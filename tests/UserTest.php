<?php

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Identity;

class UserTest extends TestCase
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

        $this->identity = Identity::createByLogin($url, $login, $password);
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
        $this->assertNotEmpty($me->vendor->id);
        $this->assertNotEmpty($me->role->id);
    }
}
