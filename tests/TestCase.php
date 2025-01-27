<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use UchiPro\ApiClient;
use UchiPro\Identity;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var Identity
     */
    protected $identity;

    public function setUp(): void
    {
        $url = getenv('UCHIPRO_URL');
        $login = getenv('UCHIPRO_LOGIN');
        $password = getenv('UCHIPRO_PASSWORD');
        $accessToken = getenv('UCHIPRO_ACCESS_TOKEN');

        $this->identity = !empty($accessToken)
            ? Identity::createByAccessToken($url, $accessToken)
            : Identity::createByLogin($url, $login, $password);
    }

    protected function getApiClient(): ApiClient
    {
        $apiClient = ApiClient::create($this->identity);
        if ($this->isDebug()) {
            $apiClient->enableDebugging();
        }
        return $apiClient;
    }

    private function isDebug(): bool
    {
        return in_array('--debug', $_SERVER['argv']);
    }

    protected function time(): int
    {
        static $time;

        if (empty($time)) {
            $time = time();
        }

        return $time;
    }
}
