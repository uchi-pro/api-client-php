<?php

namespace UchiPro;

use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use UchiPro\Courses\Courses;
use UchiPro\Exception\AccessDeniedException;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\InvalidUrlException;
use UchiPro\Exception\RequestException;
use UchiPro\Exception\UnreachableUrlException;
use UchiPro\Orders\Orders;
use UchiPro\Sessions\Sessions;
use UchiPro\Users\Users;
use UchiPro\Vendors\Vendors;

class ApiClient
{
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
        $queryString = [];

        foreach($query as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subValue) {
                    $queryString[] = urlencode($key).'='.urlencode($subValue);
                }
            } else {
                $queryString[] = urlencode($key).'='.urlencode($value);
            }
        }

        return implode('&', $queryString);
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
     * @param $url
     * @param array $params
     *
     * @return array
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function request($url, $params = [])
    {
        $method = empty($params) ? 'get' : 'post';
        try {
            $response = $this->getHttpClient()->request($method, $url);
        } catch (GuzzleException $e) {
            throw new RequestException('Ошибка запроса.', 0, $e);
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (!is_array($responseData)) {
            throw new BadResponseException('Код ответа не 200.');
        }

        return $responseData;
    }

    public function parseDate($string)
    {
        return DateTimeImmutable::createFromFormat(DateTimeImmutable::RFC3339, $string, new DateTimeZone('UTC'));
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
     * @param Identity $identity
     *
     * @return static
     */
    public static function create(Identity $identity)
    {
        return new static($identity);
    }
}
