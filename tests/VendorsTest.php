<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use Traversable;
use UchiPro\Vendors\Bank;
use UchiPro\Vendors\Company;
use UchiPro\Vendors\Limits;
use UchiPro\Vendors\Person;

class VendorsTest extends TestCase
{
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

        $newLimits = $vendorsApi->newLimits();
        $newLimits->maxTotalFilesize = rand(1000, 10000);

        $reason = 'Тестирование API';
        $updatedLimits = $vendorsApi->updateVendorLimits($vendor, $newLimits, $reason);
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

        $criteria = $vendorsApi->newCriteria();
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

        $criteria = $vendorsApi->newCriteria();
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

        $criteria = $vendorsApi->newCriteria();
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

    public function testSaveVendorCompanyProfile()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped(
                'Вендор для теста не найден.'
            );
        }
        $vendor = $vendors[0];

        $company = new Company();
        $company->title = 'Тестовая '.rand(1000, 9999);
        $company->director = 'Иванов Иван Петрович';
        $company->email = 'test@test.ru';
        $company->inn = 1840083124;
        $company->kpp = rand(11111111, 99999999);
        $company->ogrn = 1181832021854;
        $company->locality = 'с. Шаркан';
        $vendor->profile = $company;

        $bank = new Bank();
        $bank->bik = rand(10000, 99999);
        $vendor->bank = $bank;

        $vendor = $vendorsApi->saveVendor($vendor);

        $this->assertEquals($company->kpp, $vendor->profile->kpp);
        $this->assertEquals($company->title, $vendor->profile->title);
        $this->assertEquals($bank->bik, $vendor->bank->bik);
    }

    public function testSaveVendorPersonProfile()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped(
                'Вендор для теста не найден.'
            );
        }
        $vendor = $vendors[0];

        $person = new Person();
        $person->locality = 'п. Кез';
        $vendor->profile = $person;

        $vendor = $vendorsApi->saveVendor($vendor);

        $this->assertEquals($person->locality, $vendor->profile->locality);
    }

    public function testCreateVendor()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $name = "test{$this->time()}";
        $email = "$name@test.ru";

        $newVendor = $vendorsApi->newVendor();
        $newVendor->email = $email;
        $newVendor->title = $name;
        $savedVendor = $vendorsApi->saveVendor($newVendor);

        $this->assertNotEmpty($savedVendor->id);
        $this->assertEquals($savedVendor->email, $email);
    }

    public function testSetVendorDomains()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendor = $vendorsApi->newVendor('6dabb1bc-f6bb-44ef-ac2f-af80d0e0520e');

        $domains = [];
        foreach (range(1, rand(1, 6)) as $i) {
            $domains[] = "test{$this->time()}-$i.profit.uchi.red";
        }
        $savedDomains = $vendorsApi->setVendorDomains($vendor, $domains);

        $this->assertSameSize($savedDomains, $domains);
    }

    public function testSetVendorCourses()
    {
        $vendorsApi = $this->getApiClient()->vendors();

        $vendor = $vendorsApi->newVendor('6dabb1bc-f6bb-44ef-ac2f-af80d0e0520e');

        $allCourses = [
            'c4d49bac-42b7-4b77-b4f2-8743a64e5fad',
            'ba288384-e521-410f-ac52-13c02dac58d6',
            'b6249e81-bd4b-47d3-bcec-a8d66f050eff',
            '7a20ebe9-a686-462c-8d04-59048a9df0cc',
            '6aa62a13-f36f-4dc7-bf4c-189cd3ace2d4',
        ];
        $randomKeys = array_rand($allCourses, rand(2, 4));
        $courses = array_filter($allCourses, function ($key) use ($randomKeys) {
            return in_array($key, $randomKeys);
        }, ARRAY_FILTER_USE_KEY);

        $savedCourses = $vendorsApi->setVendorCourses($vendor, $courses);

        $this->assertSameSize($savedCourses, $courses);
    }
}
