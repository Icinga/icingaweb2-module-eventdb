<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

use Icinga\Module\Eventdb\Event;

class Zend_View_Helper_Column extends Zend_View_Helper_Abstract
{
    public function column($column, Event $event)
    {
        switch ($column) {
            case 'host_name':
                $default = 'host_url';
                break;
            case 'message':
                $default = 'message';
                break;
            default:
                $default = null;
                break;
        }

        if ($column === 'ack') {
            $html = $event->$column ? $this->view->icon('ok', $this->view->translate('Acknowledged')) : '-';
        } else {
            $renderer = $this->view->columnConfig->get($column, 'renderer', $default);

            switch ($renderer) {
                case 'host_url':
                    $html = $this->view->qlink($event->$column, 'monitoring/host/show',
                        array('host' => $event->$column));
                    break;
                case 'service_url':
                    $html = $this->view->qlink($event->$column, 'monitoring/service/show',
                        array('service' => $event->$column, 'host' => $event->host_name));
                    break;
                case 'url':
                    $html = $this->view->qlink($event->$column, $event->$column);
                    break;
                case 'message':
                    $html = $this->view->eventMessage($event->$column);
                    break;
                default:
                    $html = $this->view->escape($event->$column);
                    break;
            }
        }

        return '<td class="' . 'event-' . $this->view->escape($column) . '">'  . $html . '</td>';
    }
}
