<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb;

use Icinga\Application\Icinga;
use Icinga\Exception\IcingaException;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Web\Controller;

class EventdbController extends Controller
{
    /** @var  MonitoringBackend */
    protected $monitoringBackend;

    /**
     * Get the EventDB repository
     *
     * @return  Eventdb
     */
    protected function getDb()
    {
        return Eventdb::fromConfig();
    }

    /**
     * {@inheritdoc}
     */
    public function getRestrictions($name, $permission = null)
    {
        $restrictions = array();
        if ($this->Auth()->isAuthenticated()) {
            foreach ($this->Auth()->getUser()->getRoles() as $role) {
                if ($permission !== null && ! in_array($permission, $role->getPermissions())) {
                    continue;
                }
                $restrictionsFromRole = $role->getRestrictions($name);
                if (empty($restrictionsFromRole)) {
                    $restrictions = array();
                    break;
                } else {
                    if (! is_array($restrictionsFromRole)) {
                        $restrictionsFromRole = array($restrictionsFromRole);
                    }
                    $restrictions = array_merge($restrictions, array_values($restrictionsFromRole));
                }
            }
        }
        return $restrictions;
    }

    /**
     * Retrieves the Icinga MonitoringBackend
     *
     * @param string|null $name
     *
     * @return MonitoringBackend
     * @throws IcingaException When monitoring is not enabled
     */
    protected function monitoringBackend($name = null)
    {
        if ($this->monitoringBackend === null) {
            if (!Icinga::app()->getModuleManager()->hasEnabled('monitoring')) {
                throw new IcingaException('The module "monitoring" must be enabled and configured!');
            }
            $this->monitoringBackend = MonitoringBackend::instance($name);
        }
        return $this->monitoringBackend;
    }

}
