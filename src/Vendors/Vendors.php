<?php

declare(strict_types=1);

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

    public function getVendorLimits(Vendor $vendor): Limits
    {
        $responseData = $this->apiClient->request("/vendors/{$vendor->id}/limits");

        if (empty($responseData['limits']) && is_array($responseData['vendor'])) {
            $responseData['limits'] = $responseData['vendor']['limits'];
        }

        return $this->parseLimits($responseData['limits']);
    }

    private function parseLimits(array $limitsData): Limits
    {
        $limits = new Limits();
        if (isset($limitsData['max_total_filesize'])) {
            $limits->maxTotalFilesize = (int)$limitsData['max_total_filesize'];
        }
        if (isset($limitsData['total_filesize'])) {
            $limits->totalFilesize = (int)$limitsData['total_filesize'];
        }
        if (isset($limitsData['meetings_available'])) {
            $limits->meetingsAvailable = filter_var($limitsData['meetings_available'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($limitsData['leads_events_available'])) {
            $limits->leadsEventsAvailable = filter_var($limitsData['leads_events_available'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($limitsData['groups_writs_available'])) {
            $limits->groupsWritsAvailable = filter_var($limitsData['groups_writs_available'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($limitsData['billing_docs_available'])) {
            $limits->billingDocsAvailable = filter_var($limitsData['billing_docs_available'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($limitsData['infobase_available'])) {
            $limits->infobaseAvailable = filter_var($limitsData['infobase_available'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($limitsData['online_shop_available'])) {
            $limits->shopAvailable = filter_var($limitsData['online_shop_available'], FILTER_VALIDATE_BOOLEAN);
        }

        return $limits;
    }

    public function getPlainVendorLimits(Vendor $vendor): string
    {
        $responseData = $this->apiClient->request("/vendors/{$vendor->id}/limits");

        if (empty($responseData['limits']) && is_array($responseData['vendor'])) {
            $responseData['limits'] = $responseData['vendor']['limits'];
        }

        return json_encode($responseData['limits']);
    }

    public function updateVendorLimits(Vendor $vendor, Limits $limits): Limits
    {
        $params = [];
        if (!is_null($limits->maxTotalFilesize)) {
            $params['max_total_filesize'] = $limits->maxTotalFilesize;
        }
        if (!is_null($limits->meetingsAvailable)) {
            $params['meetings_available'] = $limits->meetingsAvailable;
        }
        if (!is_null($limits->leadsEventsAvailable)) {
            $params['leads_events_available'] = $limits->leadsEventsAvailable;
        }
        if (!is_null($limits->groupsWritsAvailable)) {
            $params['groups_writs_available'] = $limits->groupsWritsAvailable;
        }
        if (!is_null($limits->billingDocsAvailable)) {
            $params['billing_docs_available'] = $limits->billingDocsAvailable;
        }
        if (!is_null($limits->infobaseAvailable)) {
            $params['infobase_available'] = $limits->infobaseAvailable;
        }
        if (!is_null($limits->shopAvailable)) {
            $params['online_shop_available'] = $limits->shopAvailable;
        }

        $responseData = $this->apiClient->request("/vendors/{$vendor->id}/limits", $params);

        return $this->parseLimits($responseData['limits']);
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
        $vendor->isActive = filter_var($item['is_active'], FILTER_VALIDATE_BOOLEAN);
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
