<?php

namespace Icinga\Module\Eventdb\Test;

use Icinga\Application\EmbeddedWeb;
use Icinga\Authentication\Auth;
use Icinga\User;

class Bootstrap
{
    public static function web($basedir = null)
    {
        error_reporting(E_ALL | E_STRICT);
        if ($basedir === null) {
            $basedir = dirname(dirname(dirname(__DIR__)));
        }
        $testsDir = $basedir . '/test';
        require_once 'Icinga/Application/EmbeddedWeb.php';

        if (array_key_exists('ICINGAWEB_CONFIGDIR', $_SERVER)) {
            $configDir = $_SERVER['ICINGAWEB_CONFIGDIR'];
        } else {
            $configDir = $testsDir . '/config';
        }

        EmbeddedWeb::start($testsDir, $configDir)
            ->getModuleManager()
            ->loadModule('eventdb', $basedir)
            ->loadModule('monitoring', $basedir . '/vendor/icingaweb2/modules/monitoring');

        $user = new User('icingaadmin');
        $user->setPermissions(array('*'));
        Auth::getInstance()->setAuthenticated($user);
    }
}
