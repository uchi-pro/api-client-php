<?php

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Identity;

class ConnectionTest extends TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = require 'config.php';
    }

    /**
     * @return ApiClient
     */
    public function getApiClient()
    {
        return ApiClient::create(Identity::createByAccessToken('', ''));
    }

    public function testPrepareUrl()
    {
        $apiClient = $this->getApiClient();

        $url = str_replace('https://', 'http://', $this->config['url']) . '/';

        $preparedUrl = $apiClient->prepareUrl($url);

        $this->assertTrue(strpos($preparedUrl, 'https://') === 0);
        $this->assertTrue(substr_count($preparedUrl, '/') === 2);
    }
}
