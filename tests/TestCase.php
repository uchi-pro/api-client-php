<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function isDebug(): bool
    {
        return in_array('--debug', $_SERVER['argv']);
    }
}
