<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class ServerTestCase extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        RunServer::start();
    }

    public function __destruct() {
        RunServer::stop();
    }
}
