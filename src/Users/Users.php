<?php

namespace UchiPro\Users;

use UchiPro\ApiClient;
use UchiPro\Exception\BadResponseException;
use UchiPro\Vendors\Vendor;

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
     *
     * @throws BadResponseException
     */
    public function getMe()
    {
        $responseData = $this->apiClient->request('/account/login');

        if (!isset($responseData['account'])) {
            throw new BadResponseException('Не удалось получить данные пользователя.');
        }

        $vendor = new Vendor();
        $vendor->id = $responseData['account']['vendor_uuid'] ?? null;
        $vendor->title = $responseData['account']['vendor_title'] ?? null;

        $user = new User();
        $user->id = $responseData['account']['uuid'] ?? null;
        $user->name = $responseData['account']['title'] ?? null;
        $user->vendor = $vendor;

        return $user;
    }

    /**
     * @param ApiClient $apiClient
     *
     * @return static
     */
    public static function create(ApiClient $apiClient)
    {
        return new static($apiClient);
    }
}
