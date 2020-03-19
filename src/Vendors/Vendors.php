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
     * @param string $id
     * @param string $title
     *
     * @return Vendor
     */
    public function createVendor($id = '', $title = '')
    {
        return Vendor::create($id, $title);
    }

    /**
     * @param Criteria|null $query
     *
     * @return array|Vendor[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(Criteria $query = null)
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

        foreach ($vendors as $vendor) {
            $vendor->domains = $this->fetchVendorDomains($vendor);
        }

        return $vendors;
    }

    /**
     * @param array $list
     *
     * @return Vendor[]
     */
    private function parseVendors(array $list)
    {
        $vendors = [];

        foreach ($list as $item) {
            $vendor = new Vendor();
            $vendor->id = $item['uuid'] ?? null;
            $vendor->title = $item['title'] ?? null;
            $vendor->domains = $item['domains'] ?? [];

            $settings = new Settings();
            $settings->selfRegistrationEnabled = !empty($item['settings']['self_registration_enabled']);
            $settings->smtpHost = $item['settings']['smtp_host'] ?? null;
            $settings->smtpUsername = $item['settings']['smtp_username'] ?? null;
            $vendor->settings = $settings;

            $vendors[] = $vendor;
        }

        return $vendors;
    }

    private function buildUri(Criteria $searchQuery = null)
    {
        $uri = '/vendors';
        return $uri;
    }

    /**
     * @param Vendor $vendor
     *
     * @return array|string[]
     */
    private function fetchVendorDomains(Vendor $vendor)
    {
        $uri = "/vendors/{$vendor->id}/domains";
        $responseData = $this->apiClient->request($uri);

        return $responseData['domains'] ?? [];
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
