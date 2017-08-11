<?php
/* Icinga Web 2 - EventDB | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\ProvidedHook\Monitoring;

use Icinga\Application\Config;
use Icinga\Authentication\Auth;
use Icinga\Data\Filter\Filter;
use Icinga\Data\Filter\FilterAnd;
use Icinga\Module\Eventdb\Eventdb;
use Icinga\Module\Monitoring\Hook\DetailviewExtensionHook;
use Icinga\Module\Monitoring\Object\MonitoredObject;
use Icinga\Web\Navigation\Navigation;
use Icinga\Web\Url;

/**
 * Available in icingaweb2 after 2.5.0
 */
class DetailviewExtension extends DetailviewExtensionHook
{
    public function getHtmlForObject(MonitoredObject $object)
    {
        if (! Auth::getInstance()->hasPermission('eventdb/events')) {
            return '';
        }

        $config = static::config();

        if ($config->get('detailview_disable') === '1') {
            return '';
        }

        $actions = clone EventdbActionHook::getActions($object);
        if (empty($actions)) {
            // no actions -> no EventDB
            return '';
        }

        $htm = '<h2>EventDB</h2>';

        $htm .= '<div class="quick-actions">';
        $actions->setLayout(Navigation::LAYOUT_TABS);
        $htm .= $actions->render();
        $htm .= '</div>';

        $url = Url::fromPath('eventdb/events', array('host_name' => $object->host_name));

        $customFilter = EventdbActionHook::getCustomFilter($object);
        if ($customFilter === null) {
            $customFilter = new FilterAnd;
        }
        $detailview_filter = $config->get('detailview_filter', 'ack=0');
        if ($detailview_filter !== null) {
            $customFilter = $customFilter->andFilter(Filter::fromQueryString($detailview_filter));
        }

        $htm .= sprintf(
            '<div class="container" data-last-update="-1" data-icinga-url="%s" data-icinga-refresh="60">',
            $url->with(array(
                'sort'  => 'priority',
                'dir'   => 'asc',
                'view'  => 'compact',
                'limit' => 5,
            ))->addFilter($customFilter)
        );
        $htm .= '<p class="progress-label">' . mt('eventdb', 'Loading') . '<span>.</span><span>.</span><span>.</span></p>';
        $htm .= '</div>';

        return $htm;
    }

    protected function eventDb()
    {
        return Eventdb::fromConfig();
    }

    protected static function config()
    {
        return Config::module('eventdb')->getSection('monitoring');
    }
}
