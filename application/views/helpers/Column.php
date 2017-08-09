<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

use Icinga\Module\Eventdb\Event;

class Zend_View_Helper_Column extends Zend_View_Helper_Abstract
{
    public function column($column, Event $event, $classes = array())
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
                    $html = $this->view->qlink($event->$column, 'eventdb/event/host',
                        array('host' => $event->$column));
                    break;
                case 'service_url':
                    $html = $this->view->qlink($event->$column, 'eventdb/event/service',
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

        return '<td class="' . 'event-' . $this->view->escape($column) . ' '
            . implode(' ', $classes) . '" data-base-target="_next">'  . $html . '</td>';
    }
}
