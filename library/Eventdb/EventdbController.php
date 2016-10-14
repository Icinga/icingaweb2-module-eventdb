<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb;

use Icinga\Web\Controller;

class EventdbController extends Controller
{
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
}
