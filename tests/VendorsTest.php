<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use Traversable;
use UchiPro\ApiClient;
use UchiPro\Identity;
use UchiPro\Vendors\Limits;

class VendorsTest extends TestCase
{
    /**
     * @var Identity
     */
    private $identity;

    public function setUp()
    {
        $url = getenv('UCHIPRO_URL');
        $login = getenv('UCHIPRO_LOGIN');
        $password = getenv('UCHIPRO_PASSWORD');
        $accessToken = getenv('UCHIPRO_ACCESS_TOKEN');

        $this->identity = !empty($accessToken)
            ? Identity::createByAccessToken($url, $accessToken)
            : Identity::createByLogin($url, $login, $password);
    }

    public function getApiClient(): ApiClient
    {
        $apiClient = ApiClient::create($this->identity);
        if ($this->isDebug()) {
            $apiClient->enableDebugging();
        }
        return $apiClient;
    }

    public function testGetVendors()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        $this->assertInstanceOf(Traversable::class, $vendors);
    }

    public function testFindVendorById()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped(
                'Вендор для теста не найден.'
            );
        }

        $vendor = $vendors[0];
        $foundVendor = $vendorsApi->findById($vendor->id);
        $this->assertEquals($vendor->id, $foundVendor->id);

        $notExistsVendor = $vendorsApi->findById("{$vendor->id}1");
        $this->assertEmpty($notExistsVendor);
    }

    public function testGetVendorLimits()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped('Вендор для теста не найден.');
        }

        $vendor = $vendors[0];
        $limits = $vendorsApi->getVendorLimits($vendor);

        $this->assertInstanceOf(Limits::class, $limits);
    }

    public function testGetPlainVendorLimits()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped('Вендор для теста не найден.');
        }

        $vendor = $vendors[0];
        $plainLimits = $vendorsApi->getPlainVendorLimits($vendor);

        $this->assertJson($plainLimits);
    }

    public function testUpdateVendorLimits()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped('Вендор для теста не найден.');
        }

        $vendor = $vendors[0];

        $newLimits = new Limits();
        $newLimits->maxTotalFilesize = rand(1000, 10000);

        $updatedLimits = $vendorsApi->updateVendorLimits($vendor, $newLimits);
        $this->assertEquals($newLimits->maxTotalFilesize, $updatedLimits->maxTotalFilesize);
    }

    public function testGetVendorTotalFilesize()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped('Вендор для теста не найден.');
        }

        $vendor = $vendors[0];
        $totalFilesize = $vendorsApi->getVendorTotalFilesize($vendor);

        $this->assertGreaterThan(0, $totalFilesize, "У вендора $vendor->title ($vendor->id) не загружены файлы.");
    }

    public function testFindVendorByDomain()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        $vendorWithDomain = null;
        foreach ($vendors as $vendor) {
            if (!empty($vendor->domains)) {
                $vendorWithDomain = $vendor;
                break;
            }
        }

        if (empty($vendorWithDomain)) {
            $this->markTestSkipped(
                'Вендор для теста не найден.'
            );
        }

        $domain = array_reverse($vendorWithDomain->domains)[0];

        $foundVendor = $vendorsApi->findByDomain($domain);

        $this->assertEquals($vendorWithDomain->id, $foundVendor->id);
    }

    public function testFindVendorByTitle()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped(
                'Вендор для теста не найден.'
            );
        }
        $vendor = $vendors[0];

        $criteria = $vendorsApi->createCriteria();
        $criteria->q = $vendor->title;
        $foundVendors = $vendorsApi->findBy($criteria);

        $this->assertEquals($vendor->id, $foundVendors[0]->id);
    }

    public function testFindActiveVendors()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $activeVendorsCount = 0;
        $vendors = $vendorsApi->findAll();
        foreach ($vendors as $vendor) {
            if ($vendor->isActive) {
                $activeVendorsCount++;
            }
        }

        $criteria = $vendorsApi->createCriteria();
        $criteria->isActive = true;
        $activeVendors = $vendorsApi->findBy($criteria);

        $this->assertCount($activeVendorsCount, $activeVendors);
    }

    public function testFindBlockedVendors()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $blockedVendorsCount = 0;
        $vendors = $vendorsApi->findAll();
        foreach ($vendors as $vendor) {
            if (!$vendor->isActive) {
                $blockedVendorsCount++;
            }
        }

        $criteria = $vendorsApi->createCriteria();
        $criteria->isActive = false;
        $activeVendors = $vendorsApi->findBy($criteria);

        $this->assertCount($blockedVendorsCount, $activeVendors);
    }

    public function testActivateVendor()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped(
                'Вендор для теста не найден.'
            );
        }
        $vendor = $vendors[0];

        $vendorsApi->activateVendor($vendor);

        $activatedVendor = $vendorsApi->findById($vendor->id);
        $this->assertTrue($activatedVendor->isActive);
    }

    public function testBlockVendor()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped(
                'Вендор для теста не найден.'
            );
        }
        $vendor = $vendors[0];

        $vendorsApi->blockVendor($vendor);

        $blockedVendor = $vendorsApi->findById($vendor->id);
        $this->assertFalse($blockedVendor->isActive);
    }
}
