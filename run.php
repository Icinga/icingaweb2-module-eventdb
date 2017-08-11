<?php

use Icinga\Application\Modules\Module;

/** @var Module $this */
$this->provideHook('monitoring/HostActions');
$this->provideHook('monitoring/ServiceActions');
