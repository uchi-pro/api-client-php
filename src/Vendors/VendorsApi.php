<?php

declare(strict_types=1);

namespace UchiPro\Vendors;

use UchiPro\ApiClient;
use UchiPro\Collection;
use UchiPro\Courses\Course;
use UchiPro\Exception\BadResponseException;
use UchiPro\Exception\RequestException;

final readonly class VendorsApi
{
    private function __construct(private ApiClient $apiClient) {}

    public function newVendor(?string $id = null, ?string $title = null): Vendor
    {
        return Vendor::create($id, $title);
    }

    public function newCriteria(): Criteria
    {
        return new Criteria();
    }

    public function newLimits(): Limits
    {
        return Limits::create();
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
        if (isset($limitsData['mobile_app_enabled'])) {
            $limits->mobileAppEnabled = filter_var($limitsData['mobile_app_enabled'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($limitsData['news_available'])) {
            $limits->newsAvailable = filter_var($limitsData['news_available'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($limitsData['sync_enabled'])) {
            $limits->syncEnabled = filter_var($limitsData['sync_enabled'], FILTER_VALIDATE_BOOLEAN);
        }
        if (isset($limitsData['protocols_available'])) {
            $limits->protocolsAvailable = filter_var($limitsData['protocols_available'], FILTER_VALIDATE_BOOLEAN);
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

    public function updateVendorLimits(Vendor $vendor, Limits $limits, string $reason): Limits
    {
        $params = [];

        $params['reason'] = $reason;

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
        if (!is_null($limits->mobileAppEnabled)) {
            $params['mobile_app_enabled'] = (int)$limits->mobileAppEnabled;
        }
        if (!is_null($limits->newsAvailable)) {
            $params['news_available'] = (int)$limits->newsAvailable;
        }
        if (!is_null($limits->syncEnabled)) {
            $params['sync_enabled'] = (int)$limits->syncEnabled;
        }
        if (!is_null($limits->protocolsAvailable)) {
            $params['protocols_available'] = (int)$limits->protocolsAvailable;
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
    public function findBy(Criteria $criteria = null): iterable|Collection
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
     * @return Vendor[]|Collection
     */
    public function findAll(): iterable|Collection
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
    private function parseVendors(array $list): iterable|Collection
    {
        $vendors = new Collection();

        foreach ($list as $item) {
            $vendors[] = $this->parseVendor($item);
        }

        return $vendors;
    }

    private function parseVendor(array $item): Vendor
    {
        $vendor = $this->newVendor();
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

        $vendor->profile = $this->parseVendorProfile($item);
        $vendor->bank = $this->parseVendorBank($item);

        return $vendor;
    }

    private function parseVendorProfile(array $item): Company|Person|null
    {
        $profile = null;
        $profileType = $item['profile']['type'] ?? '';
        if ($profileType === 'company') {
            $profile = new Company();
            $profile->title = $item['profile']['title'] ?? null;
            $profile->email = $item['profile']['email'] ?? null;
            $profile->phone = $item['profile']['phone'] ?? null;
            $profile->locality = $item['profile']['locality'] ?? null;
            $profile->address = $item['profile']['address'] ?? null;
            $profile->physicalAddress = $item['profile']['physical_address'] ?? null;
            $profile->postalAddress = $item['profile']['postal_address'] ?? null;
            $profile->website = $item['profile']['website'] ?? null;
            $profile->director = $item['profile']['director'] ?? null;
            $profile->inn = $item['profile']['inn'] ?? null;
            $profile->kpp = $item['profile']['kpp'] ?? null;
            $profile->ogrn = $item['profile']['ogrn'] ?? null;
            $profile->okpo = $item['profile']['okpo'] ?? null;
            $profile->oktmo = $item['profile']['oktmo'] ?? null;
            $profile->okved = $item['profile']['okved'] ?? null;
            $profile->kbk = $item['profile']['kbk'] ?? null;
        }
        if ($profileType === 'person') {
            $profile = new Person();
            $profile->inn = $item['profile']['inn'] ?? null;
            $profile->snils = $item['profile']['snils'] ?? null;
            $profile->ogrn = $item['profile']['ogrn'] ?? null;
            $profile->okpo = $item['profile']['okpo'] ?? null;
            $profile->oktmo = $item['profile']['oktmo'] ?? null;
            $profile->okved = $item['profile']['okved'] ?? null;
            $profile->dob = $item['profile']['dob'] ?? null;
            $profile->citizenship = $item['profile']['citizenship'] ?? null;
            $profile->passport = $item['profile']['passport'] ?? null;
            $profile->gender = $item['profile']['gender'] ?? null;
            $profile->contactPerson = $item['profile']['contact_person'] ?? null;
            $profile->email = $item['profile']['email'] ?? null;
            $profile->phone = $item['profile']['phone'] ?? null;
            $profile->locality = $item['profile']['locality'] ?? null;
            $profile->address = $item['profile']['address'] ?? null;
            $profile->physicalAddress = $item['profile']['physical_address'] ?? null;
            $profile->postalAddress = $item['profile']['postal_address'] ?? null;
            $profile->employer = $item['profile']['employer'] ?? null;
            $profile->education = $item['profile']['education'] ?? null;
        }
        return $profile;
    }

    private function parseVendorBank(array $item): Bank
    {
        $bank = new Bank();
        $bank->name = $item['profile']['bank_name'] ?? null;
        $bank->address = $item['profile']['bank_address'] ?? null;
        $bank->bik = $item['profile']['bik'] ?? null;
        $bank->rs = $item['profile']['rs'] ?? null;
        $bank->ks = $item['profile']['ks'] ?? null;
        $bank->ls = $item['profile']['ls'] ?? null;
        return $bank;
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

    public function setVendorDomains(Vendor $vendor, array $domains): array
    {
        $uri = "/vendors/$vendor->id/domains";
        $responseData = $this->apiClient->request($uri, ['domain' => $domains, '_replace' => true]);

        return $responseData['domains'] ?? [];
    }

    public function setVendorCourses(Vendor $vendor, array $courses): array
    {
        $uri = "/vendors/$vendor->id/courses";
        foreach ($courses as $key => $course) {
            if ($course instanceof Course) {
                $courses[$key] = $course->id;
            }
        }
        $responseData = $this->apiClient->request($uri, ['course' => $courses, '_replace' => true]);

        return $responseData['courses'] ?? [];
    }

    public function saveVendor(Vendor $vendor, array $additionalParams = []): Vendor
    {
        $formParams = [
            'vendor' => $vendor->id,
        ];

        if (!empty($vendor->title)) {
            $formParams['title'] = $vendor->title;
        }

        if (!empty($vendor->email)) {
            $formParams['email'] = $vendor->email;
        }

        if (!empty($vendor->profile)) {
            if ($vendor->profile instanceof Company) {
                $formParams['profile[type]'] = 'company';
                $profile = $this->fillCompanyProfile($vendor->profile);
                foreach ($profile as $key => $value) {
                    $formParams["profile[company][$key]"] = $value;
                }
            }
            if ($vendor->profile instanceof Person) {
                $formParams['profile[type]'] = 'person';
                $profile = $this->fillPersonProfile($vendor->profile);
                foreach ($profile as $key => $value) {
                    $formParams["profile[person][$key]"] = $value;
                }
            }
        }

        if (!empty($vendor->bank)) {
            $profile = $this->fillBankProfile($vendor->bank);
            foreach ($profile as $key => $value) {
                $formParams["profile[bank][$key]"] = $value;
            }
        }

        foreach ($additionalParams as $key => $value) {
            $formParams[$key] = $value;
        }

        $vendorId = !empty($vendor->id) ? $vendor->id : 0;
        $uri = "/vendors/$vendorId";
        $responseData = $this->apiClient->request($uri, $formParams);

        return $this->parseVendor($responseData['vendor']);
    }

    private function fillCompanyProfile(Company $company): array
    {
        $profile = [];
        if (!is_null($company->title)) {
            $profile['title'] = $company->title;
        }
        if (!is_null($company->email)) {
            $profile['email'] = $company->email;
        }
        if (!is_null($company->phone)) {
            $profile['phone'] = $company->phone;
        }
        if (!is_null($company->locality)) {
            $profile['locality'] = $company->locality;
        }
        if (!is_null($company->physicalAddress)) {
            $profile['physical_address'] = $company->physicalAddress;
        }
        if (!is_null($company->postalAddress)) {
            $profile['postal_address'] = $company->postalAddress;
        }
        if (!is_null($company->website)) {
            $profile['website'] = $company->website;
        }
        if (!is_null($company->director)) {
            $profile['director'] = $company->director;
        }
        if (!is_null($company->inn)) {
            $profile['inn'] = $company->inn;
        }
        if (!is_null($company->kpp)) {
            $profile['kpp'] = $company->kpp;
        }
        if (!is_null($company->ogrn)) {
            $profile['ogrn'] = $company->ogrn;
        }
        if (!is_null($company->okpo)) {
            $profile['okpo'] = $company->okpo;
        }
        if (!is_null($company->oktmo)) {
            $profile['oktmo'] = $company->oktmo;
        }
        if (!is_null($company->okved)) {
            $profile['okved'] = $company->okved;
        }
        if (!is_null($company->kbk)) {
            $profile['kbk'] = $company->kbk;
        }
        return $profile;
    }

    private function fillPersonProfile(Person $person): array
    {
        $profile = [];
        if (!is_null($person->inn)) {
            $profile['inn'] = $person->inn;
        }
        if (!is_null($person->snils)) {
            $profile['snils'] = $person->snils;
        }
        if (!is_null($person->ogrn)) {
            $profile['ogrn'] = $person->ogrn;
        }
        if (!is_null($person->okpo)) {
            $profile['okpo'] = $person->okpo;
        }
        if (!is_null($person->oktmo)) {
            $profile['oktmo'] = $person->oktmo;
        }
        if (!is_null($person->okved)) {
            $profile['okved'] = $person->okved;
        }
        if (!is_null($person->dob)) {
            $profile['dob'] = $person->dob;
        }
        if (!is_null($person->citizenship)) {
            $profile['citizenship'] = $person->citizenship;
        }
        if (!is_null($person->passport)) {
            $profile['passport'] = $person->passport;
        }
        if (!is_null($person->gender)) {
            $profile['gender'] = $person->gender;
        }
        if (!is_null($person->contactPerson)) {
            $profile['contact_person'] = $person->contactPerson;
        }
        if (!is_null($person->email)) {
            $profile['email'] = $person->email;
        }
        if (!is_null($person->phone)) {
            $profile['phone'] = $person->phone;
        }
        if (!is_null($person->locality)) {
            $profile['locality'] = $person->locality;
        }
        if (!is_null($person->address)) {
            $profile['address'] = $person->address;
        }
        if (!is_null($person->physicalAddress)) {
            $profile['physical_address'] = $person->physicalAddress;
        }
        if (!is_null($person->postalAddress)) {
            $profile['postal_address'] = $person->postalAddress;
        }
        if (!is_null($person->employer)) {
            $profile['employer'] = $person->employer;
        }
        if (!is_null($person->education)) {
            $profile['education'] = $person->education;
        }
        return $profile;
    }

    private function fillBankProfile(Bank $bank): array
    {
        $profile = [];
        if (!empty($bank->name)) {
            $profile['name'] = $bank->name;
        }
        if (!empty($bank->address)) {
            $profile['address'] = $bank->address;
        }
        if (!empty($bank->bik)) {
            $profile['bik'] = $bank->bik;
        }
        if (!empty($bank->ks)) {
            $profile['ks'] = $bank->ks;
        }
        if (!empty($bank->rs)) {
            $profile['rs'] = $bank->rs;
        }
        if (!empty($bank->ls)) {
            $profile['ls'] = $bank->ls;
        }
        return $profile;
    }

    public static function create(ApiClient $apiClient): VendorsApi
    {
        return new VendorsApi($apiClient);
    }
}
