<?php

namespace UchiPro\Vendors;

use UchiPro\ApiClient;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;

class Vendors
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
     * @param Query|null $query
     *
     * @return array|Vendor[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(Query $query = null)
    {
        $vendors = [];

        $uri = $this->buildUri($query);
        $responseData = $this->apiClient->request($uri);

        if (!array_key_exists('vendors', $responseData)) {
            throw new BadResponseException('Не удалось получить вендоров.');
        }

        if (is_array($responseData['vendors'])) {
            $vendors = $this->parseVendors($responseData['vendors']);
        }

        return $vendors;
    }

    private function parseVendors(array $list)
    {
        $vendors = [];

        foreach ($list as $item) {
            $vendor = new Vendor();
            $vendor->id = $item['uuid'] ?? null;
            $vendor->title = $item['title'] ?? null;

            $settings = new Settings();
            $settings->selfRegistrationEnabled = !empty($item['settings']['self_registration_enabled']);
            $settings->smtpHost = $item['settings']['smtp_host'] ?? null;
            $settings->smtpUsername = $item['settings']['smtp_username'] ?? null;
            $vendor->settings = $settings;

            $vendors[] = $vendor;
        }

        return $vendors;
    }

    private function buildUri(Query $searchQuery = null)
    {
        $uri = '/vendors';
        return $uri;
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
