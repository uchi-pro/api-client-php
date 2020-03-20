<?php

use PHPUnit\Framework\TestCase;
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

    public function testLogin()
    {
        $me = $this->getApiClient()->users()->getMe();

        $this->assertNotEmpty($me->id);
        $this->assertNotEmpty($me->role->id);
    }

    public function testFindContratorByEmail()
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

        $this->assertTrue($contractorWithEmail->role->id === 'contractor');

        if (!empty($contractorWithEmail)) {
            $foundContractor = $usersApi->findContractorByEmail($contractorWithEmail->email);
            $this->assertTrue($foundContractor->id === $contractorWithEmail->id);
        }
    }

    public function testRoleAdministrator()
    {
        $this->assertTrue(Role::createAdministrator()->id === 'administrator');
    }

    public function testRoleManager()
    {
        $this->assertTrue(Role::createManager()->id === 'manager');
    }

    public function testCreateEditor()
    {
        $this->assertTrue(Role::createEditor()->id === 'editor');
    }

    public function testCreateTeacher()
    {
        $this->assertTrue(Role::createTeacher()->id === 'teacher');
    }

    public function testCreateAgent()
    {
        $this->assertTrue(Role::createAgent()->id === 'agent');
    }

    public function testCreateContractor()
    {
        $this->assertTrue(Role::createContractor()->id === 'contractor');
    }

    public function testCreateListener()
    {
        $this->assertTrue(Role::createListener()->id === 'listener');
    }

    public function testCreateGuest()
    {
        $this->assertTrue(Role::createGuest()->id === 'guest');
    }
}
