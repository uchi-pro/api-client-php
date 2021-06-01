<?php

namespace UchiPro;

use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use UchiPro\Courses\Courses;
use UchiPro\Exception\AccessDeniedException;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\InvalidUrlException;
use UchiPro\Exception\RequestException;
use UchiPro\Exception\UnreachableUrlException;
use UchiPro\Leads\Leads;
use UchiPro\Orders\Orders;
use UchiPro\Sessions\Sessions;
use UchiPro\Users\Users;
use UchiPro\Vendors\Vendors;

use function GuzzleHttp\Psr7\build_query;

class ApiClient
{
    const EMPTY_UUID_VALUE = '00000000-0000-0000-0000-000000000000';

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
    public static function prepareUrl(string $url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException();
        }

        try {
            $httpClient = new HttpClient();

            $response = $httpClient->request('GET', $url, ['allow_redirects' => false]);

            $isRedirect = substr($response->getStatusCode(), 0, 1) == '3';
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
    public static function httpBuildQuery(array $query)
    {
        return build_query($query, PHP_QUERY_RFC1738);
    }

    /**
     * @return HttpClient
     *
     * @throws AccessDeniedException
     */
    private function getHttpClient()
    {
        if (!empty($this->httpClient)) {
            return $this->httpClient;
        }

        if (!strpos($this->identity->url, '://')) {
            $this->identity->url = $this->identity->urlScheme . '://' . $this->identity->url;
        }

        $httpClientConfig = [
            'base_uri' => $this->identity->url,
            'headers' => ['Accept' => 'application/json'],
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
    private function authClient(HttpClient $client, Identity $identity)
    {
        $formParams = [
            'username' => $identity->login,
            'password' => $identity->password,
        ];

        $response = $client->post('/account/login', [
            'form_params' => $formParams,
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
    public function request($url, $params = []): array
    {
        try {
            if (empty($params)) {
                $response = $this->getHttpClient()->request('get', $url);
            } else {
                $response = $this->getHttpClient()->request('post', $url, [
                  'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
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
            throw new RequestException("Ошибка запроса: {$errorsOutput}", 0, $e);
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (!is_array($responseData)) {
            throw new BadResponseException('Код ответа не 200.');
        }

        return $responseData;
    }

    public function parseDate($string)
    {
        return DateTimeImmutable::createFromFormat('Y-m-d\TH:i:sP', $string, new DateTimeZone('UTC'));
    }

    public function parseId($array, $key)
    {
        $id = $array[$key] ?? null;
        if ($id === self::EMPTY_UUID_VALUE) {
            $id = null;
        }
        return $id;
    }

    /**
     * @return Users
     */
    public function users()
    {
        return Users::create($this);
    }

    /**
     * @return Courses
     */
    public function courses()
    {
        return Courses::create($this);
    }

    /**
     * @return Sessions
     */
    public function sessions()
    {
        return Sessions::create($this);
    }

    /**
     * @return Orders
     */
    public function orders()
    {
        return Orders::create($this);
    }

    /**
     * @return Vendors
     */
    public function vendors()
    {
        return Vendors::create($this);
    }

    /**
     * @return Leads
     */
    public function leads()
    {
        return Leads::create($this);
    }

    /**
     * @param Identity $identity
     *
     * @return static
     */
    public static function create(Identity $identity)
    {
        return new static($identity);
    }
}
