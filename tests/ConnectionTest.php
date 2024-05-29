<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use UchiPro\ApiClient;

class ConnectionTest extends TestCase
{
    public function testPrepareUrl()
    {
        $apiClient = ApiClient::create($this->identity);

        $url = str_replace('https://', 'http://', $this->identity->url) . '/';

        $preparedUrl = $apiClient->prepareUrl($url);

        $this->assertTrue(strpos($preparedUrl, 'https://') === 0);
        $this->assertTrue(substr_count($preparedUrl, '/') === 2);
    }

    public function testPreparePostBody()
    {
        $params = [
            'course' => [1, 2],
            'listener' => ['001', '002', '003'],
        ];
        $query = ApiClient::httpBuildQuery($params);

        $this->assertEquals('course=1&course=2&listener=001&listener=002&listener=003', $query);
    }
}
