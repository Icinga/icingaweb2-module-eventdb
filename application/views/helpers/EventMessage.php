<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

use Icinga\Application\Config;

class Zend_View_Helper_EventMessage extends Zend_View_Helper_Abstract
{
    /**
     * Purifier instance
     *
     * @var HTMLPurifier
     */
    protected static $purifier;

    public function eventMessage($message)
    {
        return $this->getPurifier()->purify($message);
    }

    /**
     * Get the purifier instance
     *
     * @return  HTMLPurifier
     */
    protected function getPurifier()
    {
        if (self::$purifier === null) {
            require_once 'HTMLPurifier/Bootstrap.php';
            require_once 'HTMLPurifier.php';
            require_once 'HTMLPurifier.autoload.php';

            $config = HTMLPurifier_Config::createDefault();
            $config->set('Core.EscapeNonASCIICharacters', true);
            $config->set('HTML.Allowed', Config::module('eventdb')->get(
                'frontend',
                'allowed_html',
                'p,br,b,a[href|target],i,table,tr,td[colspan],div,*[class]'
            ));
            $config->set('Attr.AllowedFrameTargets', array('_blank'));
            $config->set('Cache.DefinitionImpl', null);
            self::$purifier = new HTMLPurifier($config);
        }
        return self::$purifier;
    }
}
