<?php
/* Icinga Web 2 - EventDB | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\ProvidedHook\Monitoring;

use Icinga\Application\Config;
use Icinga\Authentication\Auth;
use Icinga\Module\Monitoring\Hook\ServiceActionsHook;
use Icinga\Module\Monitoring\Object\Service;
use Icinga\Web\Navigation\Navigation;
use Icinga\Web\Url;

class ServiceActions extends ServiceActionsHook
{
    public function getActionsForService(Service $service)
    {
        if (! Auth::getInstance()->hasPermission('eventdb/events')) {
            return array();
        }

        $nav = new Navigation();

        $config = $this->config();
        $custom_var = $config->get('custom_var', null);
        $always_on_service = $config->get('always_on_host', 0);

        if ($custom_var !== null) {
            $edb_cv = $service->{'_service_' . $custom_var};
            $edb_filter = $service->{'_service_' . $custom_var . '_filter'};
        } else {
            $edb_cv = null;
            $edb_filter = null;
        }

        if ($edb_filter !== null) {
            // TODO: filter and filtered action
            // TODO: error handling
        }

        // show access to all events, if (or)
        // - custom_var is not configured
        // - always_on_service is set
        // - custom_var is configured and set on object (to any value)
        if ($custom_var === null || !empty($edb_cv) || !empty($always_on_service)) {
            $nav->addItem(
                'events',
                array(
                    'label' => mt('eventdb', 'EventDB') . ': ' . mt('eventdb', 'All events for host'),
                    'url'   => Url::fromPath(
                        'eventdb/events',
                        array(
                            'host_name' => $service->host_name,
                        )
                    ),
                    'icon'  => 'tasks',
                    'class' => 'action-link',
                )
            );
        }

        return $nav;
    }

    public function config()
    {
        return Config::module('eventdb')->getSection('monitoring');
    }
}
