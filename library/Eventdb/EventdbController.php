<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb;

use Icinga\Application\Icinga;
use Icinga\Data\QueryInterface;
use Icinga\Exception\IcingaException;
use Icinga\Exception\Json\JsonEncodeException;
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
            if (! Icinga::app()->getModuleManager()->hasEnabled('monitoring')) {
                throw new IcingaException('The module "monitoring" must be enabled and configured!');
            }
            $this->monitoringBackend = MonitoringBackend::instance($name);
        }
        return $this->monitoringBackend;
    }

    protected function setViewScript($name)
    {
        $this->_helper->viewRenderer->setNoController(true);
        $this->_helper->viewRenderer->setScriptAction($name);
    }

    protected function isFormatRequest()
    {
        return $this->hasParam('format');
    }

    protected function isApiRequest()
    {
        $format = $this->getParam('format');
        $header = $this->getRequest()->getHeader('Accept');

        if ($format === 'json' || preg_match('#application/json(;.+)?#', $header)) {
            return true;
        } else {
            return false;
        }
    }

    protected function isTextRequest()
    {
        $format = $this->getParam('format');
        if ($format === 'text' || $this->isPlainTextRequest()) {
            return true;
        } else {
            return false;
        }
    }

    protected function isPlainTextRequest()
    {
        $header = $this->getRequest()->getHeader('Accept');
        if ($header !== null && preg_match('#text/plain#', $header)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Send the user a summary of SQL queries
     *
     * @param array|QueryInterface $queries
     */
    protected function sendSqlSummary($queries)
    {
        if (! is_array($queries)) {
            $queries = array($queries);
        }

        $str = '';
        foreach ($queries as $query) {
            if ($query !== null) {
                $str .= wordwrap($query) . "\n\n";
            }
        }

        $this->sendText($str);
    }

    /**
     * Output JSON data to the requester
     *
     * @param mixed $data
     * @param int   $options
     * @param int   $depth
     *
     * @throws JsonEncodeException
     */
    protected function sendJson($data, $options = 0, $depth = 100)
    {
        header('Content-Type: application/json');

        if (defined('JSON_PARTIAL_OUTPUT_ON_ERROR')) {
            $options |= JSON_PARTIAL_OUTPUT_ON_ERROR;
        }

        $output = json_encode($data, $options, $depth);
        if (! $output && json_last_error() !== null) {
            throw new JsonEncodeException('JSON error: ' . json_last_error_msg());
        }
        echo $output;
        exit;
    }

    protected function sendText($str, $script = null)
    {
        if ($this->isPlainTextRequest()) {
            if ($script !== null) {
                echo $this->view->render($this->getViewScript($script, true));
            } else {
                echo $str;
            }
            exit;
        } else {
            $this->view->text = $str;
            $this->view->partial = $script;
            $this->setViewScript('format/text');
        }
    }
}
