<?php

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Identity;

class UserTest extends TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = require 'config.php';
    }

    private function getListenerIdentity()
    {
        $url = $this->config['url'];

        foreach ($this->config['users'] as $user) {
            if ($user['role'] === 'listener') {
                if (!empty($user['token'])) {
                    return Identity::createByAccessToken($url, $user['token']);
                } elseif (!empty($user['login']) && !empty($user['password'])) {
                    return Identity::createByLogin($url, $user['login'], $user['password']);
                }
            }
        }

        return null;
    }

    /**
     * @return ApiClient
     */
    public function getApiClient()
    {
        return ApiClient::create($this->getListenerIdentity());
    }

    public function testLogin()
    {
        $me = $this->getApiClient()->users()->getMe();

        $this->assertNotEmpty($me->id);
    }
}
