<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use UchiPro\Users\Role;

class UsersTest extends TestCase
{
    public function testLogin()
    {
        $me = $this->getApiClient()->users()->getMe();

        $this->assertNotEmpty($me->id);
        $this->assertNotEmpty($me->username);
        $this->assertNotEmpty($me->role->id);
    }

    public function testFindById()
    {
        $usersApi = $this->getApiClient()->users();
        $criteria = $usersApi->newCriteria()->withRole(Role::createContractor());
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
        $criteria = $usersApi->newCriteria()->withRole(Role::createContractor());
        $contractors = $usersApi->findBy($criteria);

        foreach ($contractors as $contractor) {
            $listener = $usersApi->getContractorDefaultListener($contractor);
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
        $criteria = $usersApi->newCriteria()->withRole(Role::createContractor());
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

    public function testFindListenerByEmail()
    {
        $usersApi = $this->getApiClient()->users();

        $email = 'andrey@uchipro.ru';
        $foundListener = $usersApi->findListenerByEmail($email);

        $this->assertNotEmpty($foundListener);
        $this->assertSame($email, $foundListener->email);
    }

    public function testCreateUser()
    {
        $usersApi = $this->getApiClient()->users();
        $this->assertNull($usersApi->newUser()->role);
    }

    public function testCreateUserAdministrator()
    {
        $usersApi = $this->getApiClient()->users();
        $this->assertSame('administrator', $usersApi->newAdministrator()->role->id);
    }

    public function testCreateUserContractor()
    {
        $usersApi = $this->getApiClient()->users();
        $this->assertSame('contractor', $usersApi->newContractor()->role->id);
    }

    public function testCreateUserListener()
    {
        $usersApi = $this->getApiClient()->users();
        $this->assertSame('listener', $usersApi->newListener()->role->id);
    }

    public function testRoleAdministrator()
    {
        $this->assertSame('administrator', Role::createAdministrator()->id);
    }

    public function testRoleManager()
    {
        $this->assertSame('manager', Role::createManager()->id);
    }

    public function testCreateRoleEditor()
    {
        $this->assertSame('editor', Role::createEditor()->id);
    }

    public function testCreateRoleTeacher()
    {
        $this->assertSame('teacher', Role::createTeacher()->id);
    }

    public function testCreateRoleAgent()
    {
        $this->assertSame('agent', Role::createAgent()->id);
    }

    public function testCreateRoleContractor()
    {
        $this->assertSame('contractor', Role::createContractor()->id);
    }

    public function testCreateRoleListener()
    {
        $this->assertSame('listener', Role::createListener()->id);
    }

    public function testCreateRoleGuest()
    {
        $this->assertSame('guest', Role::createGuest()->id);
    }

    public function testSaveContractor()
    {
        $vendorsApi = $this->getApiClient()->vendors();
        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped('Нет ни одного вендора.');
        }
        $vendor = $vendors[0];

        $usersApi = $this->getApiClient()->users();
        $rand = time();

        $domain = $vendor->domains[0] ?? '';

        $user = $usersApi->newContractor();
        $user->username = "test$rand";
        $user->name = "test$rand-$domain";
        $user->email = "test$rand@test.ru";
        $user->phone = "+7$rand";
        $user->vendor = $vendor;

        $password = $user->email;
        $usersApi->saveUser($user, $password);

        $criteria = $usersApi->newCriteria()
            ->withQ($user->email)
            ->withRole(Role::createContractor());
        $foundContractors = $usersApi->findBy($criteria);

        $this->assertArrayHasKey(0, $foundContractors, 'Созданный контрагент не найден.');

        $criteria = $usersApi->newCriteria()
            ->withQ($user->email)
            ->withRole(Role::createListener());
        $foundListeners = $usersApi->findBy($criteria);

        if (count($foundListeners) > 0) {
            $this->assertEquals($foundContractors[0]->name, $foundListeners[0]->name);
        }
    }

    public function testSaveListener()
    {
        $vendorsApi = $this->getApiClient()->vendors();
        $vendors = $vendorsApi->findAll();
        if (empty($vendors[0])) {
            $this->markTestSkipped('Нет ни одного вендора.');
        }
        $vendor = $vendors[0];

        $usersApi = $this->getApiClient()->users();

        $criteria = $usersApi->newCriteria()->withRole(Role::createContractor());
        $contractors = $usersApi->findBy($criteria);
        if (empty($contractors[0])) {
            $this->markTestSkipped('Нет ни одного контрагента.');
        }
        $contractor = $contractors[0];

        $rand = time();

        $domain = $vendor->domains[0] ?? '';

        $listener = $usersApi->newListener();
        $listener->username = "test$rand";
        $listener->name = "test$rand-$domain";
        $listener->email = "test$rand@test.ru";
        $listener->phone = "+7$rand";
        $listener->vendor = $vendor;
        $listener->parent = $contractor;

        $password = $listener->email;
        $usersApi->saveUser($listener, $password);

        $criteria = $usersApi->newCriteria()
            ->withQ($listener->email)
            ->withRole(Role::createListener());
        $foundListeners = $usersApi->findBy($criteria);

        $this->assertArrayHasKey(0, $foundListeners, 'Созданный слушатель не найден.');

        $criteria = $usersApi->newCriteria()
            ->withQ($listener->email)
            ->withRole(Role::createListener());
        $foundListeners = $usersApi->findBy($criteria);

        if (count($foundListeners) > 0) {
            $this->assertEquals($foundListeners[0]->name, $foundListeners[0]->name);
        }
    }

    public function testDeleteUser()
    {
        $vendorsApi = $this->getApiClient()->vendors();
        $vendors = $vendorsApi->findAll();

        if (empty($vendors[0])) {
            $this->markTestSkipped('Нет ни одного вендора.');
        }
        $vendor = $vendors[0];

        $usersApi = $this->getApiClient()->users();
        $rand = time();

        $domain = $vendor->domains[0] ?? '';

        $newContractor = $usersApi->newContractor();
        $newContractor->name = "test$rand-$domain";
        $newContractor->email = "test$rand@test.ru";
        $newContractor->phone = "+7$rand";
        $newContractor->vendor = $vendor;

        $password = $newContractor->email;
        $usersApi->saveUser($newContractor, $password);

        $foundContractor = $usersApi->findContractorByEmail($newContractor->email);

        $deletedContractor = $usersApi->deleteUser($foundContractor);
        $this->assertTrue($deletedContractor->isDeleted);
    }

    public function testGetListenersNumber()
    {
        $listenersNumber = $this->getApiClient()->users()->getListenersNumber();
        $this->assertTrue($listenersNumber >= 0);
    }

    public function testFindAdministrators()
    {
        $usersApi = $this->getApiClient()->users();

        $criteria = $usersApi->newCriteria()->withRole(Role::createAdministrator());
        $foundAdministrators = $usersApi->findBy($criteria);

        $this->assertNotEmpty($foundAdministrators);
        $this->assertEquals('administrator', $foundAdministrators[0]->role->id);
    }
}
