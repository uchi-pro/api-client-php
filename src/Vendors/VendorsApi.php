<?php

declare(strict_types=1);

namespace UchiPro\Vendors;

use UchiPro\ApiClient;
use UchiPro\Collection;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;

final class VendorsApi
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    private function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function createVendor(string $id = '', string $title = ''): Vendor
    {
        return Vendor::create($id, $title);
    }

    public function createCriteria(): Criteria
    {
        return new Criteria();
    }

    public function getVendorLimits(Vendor $vendor): Limits
    {
        $responseData = $this->apiClient->request("/vendors/$vendor->id/limits");

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
        if (isset($limitsData['session_cheat_available'])) {
            $limits->sessionCheatAvailable = filter_var($limitsData['session_cheat_available'], FILTER_VALIDATE_BOOLEAN);
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
        if (isset($limitsData['course_compiler_available'])) {
            $limits->courseCompilerAvailable = filter_var($limitsData['course_compiler_available'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($limitsData['schedules_available'])) {
            $limits->schedulesAvailable = filter_var($limitsData['schedules_available'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($limitsData['surveys_available'])) {
            $limits->surveysAvailable = filter_var($limitsData['surveys_available'], FILTER_VALIDATE_BOOLEAN);
        }

        return $limits;
    }

    public function getPlainVendorLimits(Vendor $vendor): string
    {
        $responseData = $this->apiClient->request("/vendors/$vendor->id/limits");

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
        if (!is_null($limits->sessionCheatAvailable)) {
            $params['session_cheat_available'] = (int)$limits->sessionCheatAvailable;
        }
        if (!is_null($limits->meetingsAvailable)) {
            $params['meetings_available'] = (int)$limits->meetingsAvailable;
        }
        if (!is_null($limits->leadsEventsAvailable)) {
            $params['leads_events_available'] = (int)$limits->leadsEventsAvailable;
        }
        if (!is_null($limits->groupsWritsAvailable)) {
            $params['groups_writs_available'] = $limits->groupsWritsAvailable;
        }
        if (!is_null($limits->billingDocsAvailable)) {
            $params['billing_docs_available'] = (int)$limits->billingDocsAvailable;
        }
        if (!is_null($limits->infobaseAvailable)) {
            $params['infobase_available'] = (int)$limits->infobaseAvailable;
        }
        if (!is_null($limits->shopAvailable)) {
            $params['online_shop_available'] = (int)$limits->shopAvailable;
        }
        if (!is_null($limits->courseCompilerAvailable)) {
            $params['course_compiler_available'] = (int)$limits->courseCompilerAvailable;
        }
        if (!is_null($limits->schedulesAvailable)) {
            $params['schedules_available'] = (int)$limits->schedulesAvailable;
        }
        if (!is_null($limits->surveysAvailable)) {
            $params['surveys_available'] = (int)$limits->surveysAvailable;
        }

        $responseData = $this->apiClient->request("/vendors/$vendor->id/limits", $params);

        return $this->parseLimits($responseData['limits']);
    }

    public function getVendorTotalFilesize(Vendor $vendor): ?int
    {
        $responseData = $this->apiClient->request("/vendors/$vendor->id/limits");

        if (empty($responseData['limits']) && is_array($responseData['vendor'])) {
            $responseData['limits'] = $responseData['vendor']['limits'];
        }

        return (int)$responseData['limits']['total_filesize'];
    }

    /**
     * @param Criteria|null $criteria
     *
     * @return Vendor[]|Collection
     *
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findBy(Criteria $criteria = null): iterable
    {
        $vendors = new Collection();

        $uri = $this->buildUri($criteria);
        $responseData = $this->apiClient->request($uri);

        if (!array_key_exists('vendors', $responseData)) {
            throw new BadResponseException('Не удалось получить вендоров.');
        }

        if (is_array($responseData['vendors'])) {
            $vendors = $this->parseVendors($responseData['vendors']);
        }

        if (isset($responseData['pager'])) {
            $vendors->setPager($responseData['pager']);
        }

        foreach ($vendors as $vendor) {
            $vendor->domains = $this->getVendorDomains($vendor);
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
    public function findById(string $id): ?Vendor
    {
        $responseData = $this->apiClient->request("/vendors/$id");

        if (empty($responseData['vendor']['uuid'])) {
            return null;
        }

        $vendor = $this->parseVendor($responseData['vendor']);
        $vendor->domains = $this->getVendorDomains($vendor);

        return $vendor;
    }

    /**
     * @throws RequestException
     * @throws BadResponseException
     */
    public function findByDomain(string $domain): ?Vendor
    {
        $vendors = $this->findBy();

        foreach ($vendors as $vendor) {
            if (in_array($domain, $vendor->domains)) {
                return $vendor;
            }
        }

        return null;
    }

    public function activateVendor(Vendor $vendor): Vendor
    {
        $params = ['active' => 1];
        $responseData = $this->apiClient->request("/vendors/$vendor->id", $params);

        return $this->parseVendor($responseData['vendor']);
    }

    public function blockVendor(Vendor $vendor): Vendor
    {
        $params = ['active' => 0];
        $responseData = $this->apiClient->request("/vendors/$vendor->id", $params);

        return $this->parseVendor($responseData['vendor']);
    }

    /**
     * @param array $list
     *
     * @return Vendor[]|Collection
     */
    private function parseVendors(array $list): iterable
    {
        $vendors = new Collection();

        foreach ($list as $item) {
            $vendors[] = $this->parseVendor($item);
        }

        return $vendors;
    }

    /**
     * @param array $item
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

    private function buildUri(Criteria $criteria = null): string
    {
        $uri = '/vendors';

        $uriQuery = ['is_active' => null];
        if ($criteria) {
            if (!is_null($criteria->q)) {
                $uriQuery['q'] = $criteria->q;
            }
            if (!is_null($criteria->isActive)) {
                $uriQuery['is_active'] = (int)$criteria->isActive;
            }
        }

        if (!empty($uriQuery)) {
            $uri .= '?'.$this->apiClient::httpBuildQuery($uriQuery);
        }

        return $uri;
    }

    /**
     * @return array|string[]
     */
    private function getVendorDomains(Vendor $vendor): iterable
    {
        $uri = "/vendors/$vendor->id/domains";
        $responseData = $this->apiClient->request($uri);

        return $responseData['domains'] ?? [];
    }

    /**
     * @deprecated
     * @see getVendorDomains
     */
    private function fetchVendorDomains(Vendor $vendor): iterable
    {
        return $this->getVendorDomains($vendor);
    }

    public static function create(ApiClient $apiClient): VendorsApi
    {
        return new VendorsApi($apiClient);
    }
}
