<?php

namespace Icinga\Module\Eventdb\Test;

use Icinga\Module\Monitoring\Object\Service;

class PseudoService extends Service
{
    public function provideCustomVars($vars)
    {
        $this->properties = new \stdClass;
        $this->serviceVariables = $this->customvars = $vars;
        $this->hostVariables = array();
        return $this;
    }
}
