<?php

declare(strict_types=1);

namespace UchiPro\Vendors;

use Exception;
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
     * @param Vendor $vendor
     *
     * @return Limits
     */
    public function getVendorLimits(Vendor $vendor)
    {
        $responseData = $this->apiClient->request("/vendors/{$vendor->id}/limits");
        if (empty($responseData['limits']) && is_array($responseData['vendor'])) {
          $responseData['limits'] = $responseData['vendor']['limits'];
        }

        $limits = new Limits();
        $limits->maxTotalFilesize = isset($responseData['limits']['max_total_filesize'])
            ? (int)$responseData['limits']['max_total_filesize']
            : null;
        $limits->totalFilesize = isset($responseData['limits']['total_filesize'])
            ? (int)$responseData['limits']['total_filesize']
            : null;
        $limits->meetingsAvailable = !empty($responseData['limits']['meetings_available']);
        $limits->leadsEventsAvailable = !empty($responseData['limits']['leads_events_available']);
        $limits->groupsWritsAvailable = !empty($responseData['limits']['groups_writs_available']);
        $limits->billingDocsAvailable = empty($responseData['limits']['billing_docs_disabled']);
        $limits->infobaseAvailable = !empty($responseData['limits']['infobase_available']);

        try {
            $responseData = $this->apiClient->request("/shop/{$vendor->id}/settings");
            if (is_array($responseData)) {
                $limits->shopAvailable = true;
            }
        } catch (Exception $e) {
            // Ничего не делаем.
        }

        return $limits;
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return array|Vendor[]
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(Criteria $criteria = null)
    {
        $vendors = [];

        $uri = $this->buildUri($criteria);
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
     * @return array|Vendor[]
     */
    public function findAll()
    {
        return $this->findBy();
    }

    /**
     * @param string $id
     *
     * @return Vendor|null
     */
    public function findById(string $id)
    {
        $responseData = $this->apiClient->request("/vendors/{$id}");

        if (empty($responseData['vendor']['uuid'])) {
            return null;
        }

        $vendor = $this->parseVendor($responseData['vendor']);
        $vendor->domains = $this->fetchVendorDomains($vendor);

        return $vendor;
    }

    /**
     * @param string $domain
     *
     * @return Vendor|null
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findByDomain(string $domain)
    {
        $vendors = $this->findBy();

        foreach ($vendors as $vendor) {
            if (in_array($domain, $vendor->domains)) {
                return $vendor;
            }
        }

        return null;
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
            $vendors[] = $this->parseVendor($item);
        }

        return $vendors;
    }

    /**
     * @param array $data
     *
     * @return Vendor
     */
    private function parseVendor(array $item): Vendor
    {
        $vendor = $this->createVendor();
        $vendor->id = $item['uuid'] ?? null;
        $vendor->createdAt = $this->apiClient->parseDate($item['created_at']);
        $vendor->title = $item['title'] ?? null;
        $vendor->email = $item['email'] ?? null;
        $vendor->domains = $item['domains'] ?? [];

        $settings = new Settings();
        $settings->selfRegistrationEnabled = !empty($item['settings']['self_registration_enabled']);
        $settings->smtpHost = $item['settings']['smtp_host'] ?? null;
        $settings->smtpUsername = $item['settings']['smtp_username'] ?? null;
        $vendor->settings = $settings;

        return $vendor;
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
