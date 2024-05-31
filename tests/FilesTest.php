<?php

declare(strict_types=1);

namespace UchiPro\Tests;

class FilesTest extends TestCase
{
    public function testGetProtocolFIle()
    {
        $protocolHtmlContent = $this->getApiClient()->getFile('/protocols/250/content?_fmt=html');

        $this->assertNotEmpty($protocolHtmlContent);
        $this->assertStringContainsString('Протокол', $protocolHtmlContent);
    }
}
