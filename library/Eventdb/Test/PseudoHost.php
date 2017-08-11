<?php

namespace Icinga\Module\Eventdb\Test;

use Icinga\Module\Monitoring\Object\Host;

class PseudoHost extends Host
{
    public function provideCustomVars($vars)
    {
        $this->properties = new \stdClass;
        $this->hostVariables = $this->customvars = $vars;
        return $this;
    }
}
