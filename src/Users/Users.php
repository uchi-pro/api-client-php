<?php

namespace UchiPro\Users;

use Exception;
use UchiPro\ApiClient;

class Users
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @return User
     */
    public function getMe()
    {
        $responseData = $this->apiClient->request('/account/login');

        if (!isset($responseData['account'])) {
            throw new Exception('Не удалось получить данные пользователя.');
        }

        $user = new User();
        $user->id = $responseData['account']['id'];
        $user->name = $responseData['account']['title'];

        return $user;
    }

    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
