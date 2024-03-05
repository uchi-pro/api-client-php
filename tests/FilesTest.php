<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use UchiPro\ApiClient;
use UchiPro\Identity;

class FilesTest extends TestCase
{
    /**
     * @var Identity
     */
    private $identity;

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

    public function getApiClient(): ApiClient
    {
        $apiClient = ApiClient::create($this->identity);
        if ($this->isDebug()) {
            $apiClient->enableDebugging();
        }
        return $apiClient;
    }

    public function testGetProtocolFIle()
    {
        $protocolHtmlContent = $this->getApiClient()->getFile('/protocols/248/content?_fmt=html');

        $this->assertNotEmpty($protocolHtmlContent);
        $this->assertContains('Протокол', $protocolHtmlContent);
    }
}
