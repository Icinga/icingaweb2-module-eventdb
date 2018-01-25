<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Controllers;

use Icinga\Module\Eventdb\Forms\Config\BackendConfigForm;
use Icinga\Module\Eventdb\Forms\Config\GlobalConfigForm;
use Icinga\Module\Eventdb\Forms\Config\MonitoringConfigForm;
use Icinga\Web\Controller;

class ConfigController extends Controller
{
    public function init()
    {
        $this->assertPermission('config/modules');
        parent::init();
    }

    public function indexAction()
    {
        $backendConfig = new BackendConfigForm();
        $backendConfig
            ->setIniConfig($this->Config())
            ->handleRequest();
        $this->view->backendConfig = $backendConfig;

        $globalConfig = new GlobalConfigForm();
        $globalConfig
            ->setIniConfig($this->Config())
            ->handleRequest();
        $this->view->globalConfig = $globalConfig;

        $this->view->tabs = $this->Module()->getConfigTabs()->activate('config');
    }

    public function monitoringAction()
    {
        $monitoringConfig = new MonitoringConfigForm();
        $monitoringConfig
            ->setIniConfig($this->Config())
            ->handleRequest();
        $this->view->form = $monitoringConfig;
        $this->view->tabs = $this->Module()->getConfigTabs()->activate('monitoring');
    }
}
