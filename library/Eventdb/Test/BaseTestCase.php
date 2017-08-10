<?php

namespace Icinga\Module\Eventdb\Test;

use Icinga\Application\Icinga;
use PHPUnit_Framework_TestCase;

abstract class BaseTestCase extends PHPUnit_Framework_TestCase
{
    private static $app;

    private $db;

    public function setUp()
    {
        $this->app();
    }

    protected function app()
    {
        if (self::$app === null) {
            self::$app = Icinga::app();
        }

        return self::$app;
    }
}
