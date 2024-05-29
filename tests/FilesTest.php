<?php

declare(strict_types=1);

namespace UchiPro\Tests;

class FilesTest extends TestCase
{
    public function testGetProtocolFIle()
    {
        $protocolHtmlContent = $this->getApiClient()->getFile('/protocols/248/content?_fmt=html');

        $this->assertNotEmpty($protocolHtmlContent);
        $this->assertContains('Протокол', $protocolHtmlContent);
    }
}
