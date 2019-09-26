<?php

namespace UchiPro;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use UchiPro\Courses\Courses;
use UchiPro\Exception\AccessDeniedException;
use UchiPro\Users\Users;

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
     * @return mixed
     *
     * @throws GuzzleException
     * @throws AccessDeniedException
     */
    public function request($url, $params = [])
    {
        $method = empty($params) ? 'get' : 'post';
        $response = $this->getHttpClient()->request($method, $url);

        $responseData = json_decode($response->getBody()->getContents(), true);

        return $responseData;
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
     * @param Identity $identity
     *
     * @return static
     */
    public static function create(Identity $identity)
    {
        return new static($identity);
    }
}
