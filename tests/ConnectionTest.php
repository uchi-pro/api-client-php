<?php

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Identity;

class ConnectionTest extends TestCase
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

    public function testPrepareUrl()
    {
        $apiClient = ApiClient::create($this->identity);

        $url = str_replace('https://', 'http://', $this->identity->url) . '/';

        $preparedUrl = $apiClient->prepareUrl($url);

        $this->assertTrue(strpos($preparedUrl, 'https://') === 0);
        $this->assertTrue(substr_count($preparedUrl, '/') === 2);
    }
}
