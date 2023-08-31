<?php

declare(strict_types=1);

namespace UchiPro;

use DateTimeImmutable;
use DateTimeInterface;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use UchiPro\Courses\CoursesApi;
use UchiPro\Exception\AccessDeniedException;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\InvalidUrlException;
use UchiPro\Exception\RequestException;
use UchiPro\Exception\UnreachableUrlException;
use UchiPro\Leads\LeadsApi;
use UchiPro\Orders\OrdersApi;
use UchiPro\Sessions\SessionsApi;
use UchiPro\Users\UsersApi;
use UchiPro\Vendors\VendorsApi;

use function GuzzleHttp\Psr7\build_query;

class ApiClient
{
    const EMPTY_UUID_VALUE = '00000000-0000-0000-0000-000000000000';
    const EMPTY_DATE_VALUE = '0001-01-01T00:00:00Z';

    /**
     * @var bool
     */
    private $isDebug = false;

    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var HttpClient
     */
    private $httpClient;

    private function __construct(Identity $identity)
    {
        $this->identity = $identity;
    }

    /**
     * @param string $url
     *
     * @return string
     *
     * @throws UnreachableUrlException
     */
    public static function prepareUrl(string $url): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException();
        }

        try {
            $httpClient = new HttpClient();

            $response = $httpClient->request('GET', $url, ['allow_redirects' => false]);

            $isRedirect = substr((string)$response->getStatusCode(), 0, 1) == '3';
            if ($isRedirect && $response->hasHeader('Location')) {
                $location = $response->getHeaderLine('Location');
                if (filter_var($location, FILTER_VALIDATE_URL)) {
                    $url = $location;
                }
            }
        } catch (GuzzleException $e) {
            throw new UnreachableUrlException('', 0, $e);
        }

        $components = parse_url($url);

        return "{$components['scheme']}://{$components['host']}";
    }

    /**
     * @param array $query
     *
     * @return string
     */
    public static function httpBuildQuery(array $query): string
    {
        return build_query($query, PHP_QUERY_RFC1738);
    }

    /**
     * @return HttpClient
     *
     * @throws AccessDeniedException
     */
    private function getHttpClient(): HttpClient
    {
        if (!empty($this->httpClient)) {
            return $this->httpClient;
        }

        if (!strpos($this->identity->url, '://')) {
            $this->identity->url = $this->identity->urlScheme . '://' . $this->identity->url;
        }

        $httpClientConfig = [
            'base_uri' => $this->identity->url,
        ];

        if ($this->identity->accessToken) {
            $httpClientConfig['headers']['X-Auth-Token'] = $this->identity->accessToken;
        } else {
            $httpClientConfig['cookies'] = true;
        }

        $this->httpClient = new HttpClient($httpClientConfig);

        if (empty($httpClientConfig['headers']['X-Auth-Token'])) {
            $this->authClient($this->httpClient, $this->identity);
        }

        return $this->httpClient;
    }

    /**
     * @param HttpClient $client
     * @param Identity $identity
     *
     * @throws AccessDeniedException
     */
    private function authClient(HttpClient $client, Identity $identity): void
    {
        $formParams = [
            'username' => $identity->login,
            'password' => $identity->password,
        ];

        $response = $client->post('/account/login', [
            'form_params' => $formParams,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (empty($responseData['account']['id'])) {
            throw new AccessDeniedException('Идентификатор авторизованного пользователя не найден.');
        }
    }

    /**
     * @param string $url
     * @param array $params
     *
     * @return array
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function request(string $url, array $params = []): array
    {
        try {
            if (empty($params)) {
                $response = $this->getHttpClient()->request('get', $url, [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]);
            } else {
                $response = $this->getHttpClient()->request('post', $url, [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                    ],
                    'body' => $this::httpBuildQuery($params),
                ]);
            }
        } catch (GuzzleException $e) {
            if ($e instanceof ClientException && $e->getCode() == '404') {
                return json_decode($e->getResponse()->getBody()->getContents(), true);
            }

            $errors = [];
            if ($e instanceof ServerException) {
                $responseData = json_decode($e->getResponse()->getBody()->getContents(), true);
                if (!empty($responseData['errors'])) {
                    $errors = $responseData['errors'];
                }
            }
            $errorsOutput = implode(' ', $errors);
            throw new RequestException("Ошибка запроса: $errorsOutput", 0, $e);
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        if ($this->isDebug()) {
            $this->dump(['url' => $url, 'params' => $params, 'response' => $responseData]);
        }

        if (!is_array($responseData)) {
            throw new BadResponseException('Код ответа не 200.');
        }

        return $responseData;
    }

    public function getFile(string $url): string
    {
        $response = $this->getHttpClient()->request('get', $url);
        return $response->getBody()->getContents();
    }

    public function parseDate($string): ?DateTimeImmutable
    {
        if ($string === self::EMPTY_DATE_VALUE) {
            return null;
        }
        return DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $string);
    }

    public function parseId($array, $key): ?string
    {
        $id = $array[$key] ?? null;
        if ($id === self::EMPTY_UUID_VALUE) {
            $id = null;
        }
        return $id;
    }

    public function users(): UsersApi
    {
        return UsersApi::create($this);
    }

    public function courses(): CoursesApi
    {
        return CoursesApi::create($this);
    }

    public function sessions(): SessionsApi
    {
        return SessionsApi::create($this);
    }

    public function orders(): OrdersApi
    {
        return OrdersApi::create($this);
    }

    public function vendors(): VendorsApi
    {
        return VendorsApi::create($this);
    }

    public function leads(): LeadsApi
    {
        return LeadsApi::create($this);
    }

    public static function create(Identity $identity): ApiClient
    {
        return new static($identity);
    }

    public function isDebug(): bool
    {
        return $this->isDebug;
    }

    public function enableDebugging(): void
    {
        $this->isDebug = true;
    }

    public function dump(): void
    {
        if (function_exists('dump')) {
            dump(func_get_args());
        } else {
            print_r(func_get_args());
            print PHP_EOL;
        }
    }
}
