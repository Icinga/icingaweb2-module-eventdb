<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Exception\NotFoundError;
use Icinga\Module\Eventdb\Event;
use Icinga\Module\Eventdb\EventdbController;
use Icinga\Module\Eventdb\Forms\Event\EventCommentForm;
use Icinga\Module\Eventdb\Web\EventdbOutputFormat;
use Icinga\Web\Url;

class EventController extends EventdbController
{
    public function indexAction()
    {
        $eventId = $this->params->getRequired('id');

        $url = Url::fromRequest();

        $this->getTabs()->add('event', array(
            'active' => ! $this->isFormatRequest(),
            'title'  => $this->translate('Event'),
            'url'    => $url->without(array('format'))
        ))->extend(new EventdbOutputFormat());

        $columnConfig = $this->Config('columns');
        if (! $columnConfig->isEmpty()) {
            $additionalColumns = $columnConfig->keys();
        } else {
            $additionalColumns = array();
        }

        $event = $this->getDb()
            ->select()
            ->from('event');

        $columns = array_merge($event->getColumns(), $additionalColumns);

        $event->from('event', $columns);
        $event->where('id', $eventId);

        $event->applyFilter(Filter::matchAny(array_map(
            '\Icinga\Data\Filter\Filter::fromQueryString',
            $this->getRestrictions('eventdb/events/filter', 'eventdb/events')
        )));

        $eventData = $event->fetchRow();
        if (! $eventData) {
            throw new NotFoundError('Could not find event with id %d', $eventId);
        }

        $eventObj = Event::fromData($eventData);

        $groupedEvents = null;
        if ($this->getDb()->hasCorrelatorExtensions()) {
            $group_leader = (int) $eventObj->group_leader;
            if ($group_leader > 0) {
                // redirect to group leader
                $this->redirectNow(Url::fromPath('eventdb/event', array('id' => $group_leader)));
            }

            if ($group_leader === -1) {
                // load grouped events, if any
                $groupedEvents = $this->getDb()
                    ->select()
                    ->from('event')
                    ->where('group_leader', $eventObj->id)
                    ->order('ack', 'ASC')
                    ->order('created', 'DESC');
            }
        }

        $comments = null;
        $commentForm = null;
        if ($this->hasPermission('eventdb/comments')) {
            $comments = $this->getDb()
                ->select()
                ->from('comment', array(
                    'id',
                    'type',
                    'message',
                    'created',
                    'modified',
                    'user'
                ))
                ->where('event_id', $eventId)
                ->order('created', 'DESC');

            if ($this->hasPermission('eventdb/interact')) {
                $commentForm = new EventCommentForm();
                $commentForm
                    ->setDb($this->getDb())
                    ->setFilter(Filter::expression('id', '=', $eventId));
            }
        }

        $format = $this->params->get('format');
        if ($format === 'sql') {
            $this->sendSqlSummary(array($event, $comments, $groupedEvents));
        } elseif ($this->isApiRequest()) {
            $data = new \stdClass;
            $data->event = $eventData;
            if ($comments !== null) {
                $data->comments = $comments;
            }
            if ($groupedEvents !== null) {
                $data->groupedEvents = $groupedEvents;
            }
            $this->sendJson($data);
        } else {
            if ($commentForm !== null) {
                $commentForm->handleRequest();
            }

            $this->view->event = $eventObj;
            $this->view->columnConfig = $columnConfig;
            $this->view->additionalColumns = $additionalColumns;
            $this->view->groupedEvents = $groupedEvents;
            $this->view->comments = $comments;
            $this->view->commentForm = $commentForm;
        }
    }

    /**
     * @deprecated redirects to index view now
     */
    public function commentsAction()
    {
        $this->redirectNow(
            Url::fromPath(
                'eventdb/event',
                array('id' => $this->params->getRequired('id'))
            )
        );
    }

    /**
     * Action allowing you to be forwarded to host in Icinga monitoring
     *
     * **But** case insensitive!
     */
    public function hostAction()
    {
        $host = $this->params->getRequired('host');

        $backend = $this->monitoringBackend();

        $query = $backend->select()
            ->from('hoststatus', array('host_name'))
            ->where('host', $host);

        $realHostname = $query->fetchOne();

        if ($realHostname !== null) {
            $this->redirectNow(Url::fromPath('monitoring/host/services', array('host' => $realHostname)));
        } else {
            throw new NotFoundError('Could not find a hostname matching: %s', $host);
        }
    }

    /**
     * Action allowing you to be forwarded to host in Icinga monitoring
     *
     * **But** case insensitive!
     */
    public function serviceAction()
    {
        $host = $this->params->getRequired('host');
        $service = $this->params->getRequired('service');

        $backend = $this->monitoringBackend();

        $query = $backend->select()
            ->from('servicestatus', array('host_name', 'service'))
            ->where('host', $host)
            ->where('service', $service);

        $realService = $query->fetchRow();

        if ($realService !== null) {
            $this->redirectNow(
                Url::fromPath(
                    'monitoring/service/show',
                    array(
                        'host'    => $realService->host_name,
                        'service' => $realService->service
                    )
                )
            );
        } else {
            throw new NotFoundError('Could not find a service "%s" for host "%s"', $service, $host);
        }
    }
}
