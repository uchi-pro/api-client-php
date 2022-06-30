<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use UchiPro\ApiClient;
use UchiPro\Identity;
use UchiPro\Users\Role;

class UsersTest extends TestCase
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
        return ApiClient::create($this->identity);
    }

    public function testLogin()
    {
        $me = $this->getApiClient()->users()->getMe();

        $this->assertNotEmpty($me->id);
        $this->assertNotEmpty($me->role->id);
    }

    public function testFindById()
    {
        $usersApi = $this->getApiClient()->users();
        $criteria = $usersApi->createCriteria();
        $criteria->role = Role::createContractor();
        $contractors = $usersApi->findBy($criteria);

        if (empty($contractors)) {
            $this->markTestSkipped('Контрагенты не найдены.');
        }

        $contractor = $contractors[0];

        $foundContractor = $usersApi->findById($contractor->id);

        $this->assertSame($contractor->id, $foundContractor->id);
    }

    public function testFetchUserSettings()
    {
        $usersApi = $this->getApiClient()->users();
        $criteria = $usersApi->createCriteria();
        $criteria->role = Role::createContractor();
        $contractors = $usersApi->findBy($criteria);

        foreach ($contractors as $contractor) {
            $listener = $usersApi->fetchContractorDefaultListener($contractor);
            if (!is_null($listener)) {
                $this->assertNotEmpty($listener);
                return;
            }
        }

        $this->markTestSkipped('Контрагенты со слушателями по умолчанию не найдены.');
    }

    public function testFindContractorByEmail()
    {
        $usersApi = $this->getApiClient()->users();
        $criteria = $usersApi->createCriteria();
        $criteria->role = Role::createContractor();
        $contractors = $usersApi->findBy($criteria);

        $contractorWithEmail = null;
        foreach ($contractors as $contractor) {
            if (!empty($contractor->email)) {
                $contractorWithEmail = $contractor;
                break;
            }
        }

        $this->assertSame('contractor', $contractorWithEmail->role->id);

        if (!empty($contractorWithEmail)) {
            $foundContractor = $usersApi->findContractorByEmail($contractorWithEmail->email);
            $this->assertSame($contractorWithEmail->id, $foundContractor->id);
        }
    }

    public function testRoleAdministrator()
    {
        $this->assertSame('administrator', Role::createAdministrator()->id);
    }

    public function testRoleManager()
    {
        $this->assertSame('manager', Role::createManager()->id);
    }

    public function testCreateEditor()
    {
        $this->assertSame('editor', Role::createEditor()->id);
    }

    public function testCreateTeacher()
    {
        $this->assertSame('teacher', Role::createTeacher()->id);
    }

    public function testCreateAgent()
    {
        $this->assertSame('agent', Role::createAgent()->id);
    }

    public function testCreateContractor()
    {
        $this->assertSame('contractor', Role::createContractor()->id);
    }

    public function testCreateListener()
    {
        $this->assertSame('listener', Role::createListener()->id);
    }

    public function testCreateGuest()
    {
        $this->assertSame('guest', Role::createGuest()->id);
    }

    public function testSaveUser()
    {
        $vendorsApi = $this->getApiClient()->vendors();
        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped('Нет ни одного вендора.');
        }
        $vendor = $vendors[0];

        $usersApi = $this->getApiClient()->users();
        $rand = time();

        $user = $usersApi->createUser();
        $user->id = 0;
        $user->name = "test$rand-{$vendor->domains[0]}";
        $user->email = "test$rand@test.ru";
        $user->phone = "+7$rand";
        $user->role = Role::createContractor();
        $user->vendor = $vendor;

        $password = $user->email;
        $usersApi->saveUser($user, $password);

        $criteria = $usersApi->createCriteria();
        $criteria->q = $user->email;
        $criteria->role = Role::createContractor();
        $foundContractors = $usersApi->findBy($criteria);

        $criteria = $usersApi->createCriteria();
        $criteria->q = $user->email;
        $criteria->role = Role::createListener();
        $foundListeners = $usersApi->findBy($criteria);

        $this->assertSame($foundContractors[0]->name, $foundListeners[0]->name);
    }

    public function testGetListenersNumber()
    {
        $listenersNumber = $this->getApiClient()->users()->getListenersNumber();
        $this->assertTrue(is_numeric($listenersNumber));
    }
}
