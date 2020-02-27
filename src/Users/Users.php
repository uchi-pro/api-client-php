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

        $role = new Role();
        $role->id = $responseData['account']['role']['code'] ?? null;
        $role->title = $responseData['account']['role']['title'] ?? null;

        $vendor = new Vendor();
        $isVendorNotEmpty = isset($responseData['account']['vendor_uuid']) && ($responseData['account']['vendor_uuid'] !== $this->apiClient::EMPTY_UUID_VALUE);
        if ($isVendorNotEmpty) {
            $vendor->id = $responseData['account']['vendor_uuid'] ?? null;
            $vendor->title = $responseData['account']['vendor_title'] ?? null;
        }
        if (empty($vendor->id)) {
            $isDomainVendorNotEmpty = isset($responseData['vendor']['uuid']) && ($responseData['vendor']['uuid'] !== $this->apiClient::EMPTY_UUID_VALUE);
            if ($isDomainVendorNotEmpty) {
                $vendor->id = $responseData['vendor']['uuid'] ?? null;
                $vendor->title = $responseData['vendor']['title'] ?? null;
            }
        }

        $user = new User();
        $user->id = $responseData['account']['uuid'] ?? null;
        $user->name = $responseData['account']['title'] ?? null;
        $user->role = $role;
        $user->vendor = $vendor;

        return $user;
    }

    /**
     * @return int
     */
    public function getListenersNumber()
    {
        $responseData = $this->apiClient->request('/users?role=listener&_items_per_page=1');

        if (empty($responseData['pager'])) {
            throw new BadResponseException('Не удалось получить список слушателей.');
        }

        return (int)$responseData['pager']['total_items'];
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
