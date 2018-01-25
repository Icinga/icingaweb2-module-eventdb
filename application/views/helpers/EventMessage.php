<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

use Icinga\Application\Config;

class Zend_View_Helper_EventMessage extends Zend_View_Helper_Abstract
{
    /**
     * The RegExp for locating URLs.
     *
     * Modifications:
     *  - Don't allow ; in
     *
     * @source https://mathiasbynens.be/demo/url-regex
     */
    const URL_REGEX = '@(https?)://(-\.)?([^\s/?\.#-]+\.?)+(/[^\s;]*)?@i';

    /**
     * Purifier instance
     *
     * @var HTMLPurifier
     */
    protected static $purifier;

    public function eventMessage($message)
    {
        $htm = $this->getPurifier()->purify($message);

        // search for URLs and make them a link
        $htm = preg_replace_callback(
            static::URL_REGEX,
            function ($match) {
                return sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    htmlspecialchars($match[0]),
                    htmlspecialchars($match[0])
                );
            },
            $htm
        );

        return $htm;
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
