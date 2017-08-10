<?php
/* Icinga Web 2 - EventDB | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\ProvidedHook\Monitoring;

use Icinga\Module\Monitoring\Hook\HostActionsHook;
use Icinga\Module\Monitoring\Object\Host;

class HostActions extends HostActionsHook
{
    public function getActionsForHost(Host $host)
    {
        return EventdbActionHook::getActions($host);
    }
}
