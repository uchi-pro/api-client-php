<?php

use PHPUnit\Framework\TestCase;
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

        if (!empty($accessToken)) {
            $this->identity = Identity::createByAccessToken($url, $accessToken);
        } else {
            $this->identity = Identity::createByLogin($url, $login, $password);
        }
    }

    /**
     * @return ApiClient
     */
    public function getApiClient()
    {
        return ApiClient::create($this->identity);
    }

    public function testGetVendors()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        $this->assertTrue(is_array($vendors));
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
}
