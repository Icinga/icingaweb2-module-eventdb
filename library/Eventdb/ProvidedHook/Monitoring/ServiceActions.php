<?php
/* Icinga Web 2 - EventDB | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\ProvidedHook\Monitoring;

use Icinga\Module\Monitoring\Hook\ServiceActionsHook;
use Icinga\Module\Monitoring\Object\Service;

class ServiceActions extends ServiceActionsHook
{
    public function getActionsForService(Service $service)
    {
        return EventdbActionHook::getActions($service);
    }
}
