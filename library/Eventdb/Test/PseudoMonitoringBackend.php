<?php

namespace Icinga\Module\Eventdb\Test;

use Icinga\Data\ConfigObject;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;

class PseudoMonitoringBackend extends MonitoringBackend
{
    public static function dummy()
    {
        return new static('dummy', new ConfigObject());
    }
}
