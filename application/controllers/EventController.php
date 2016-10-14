<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Module\Eventdb\EventdbController;
use Icinga\Module\Eventdb\Forms\Event\EventCommentForm;
use Icinga\Module\Eventdb\Forms\Event\MailMeForm;
use Icinga\Util\StringHelper;
use Icinga\Web\Url;

class EventController extends EventdbController
{
    public function indexAction()
    {
        $this->assertPermission('eventdb/comments');

        $eventId = $this->params->getRequired('id');

        $this->getTabs()->add('event', array(
            'active'    => true,
            'title'     => $this->translate('Event'),
            'url'       => Url::fromRequest()
        ));

        $staticColumns = array(
            'ack',
            'id',
            'priority',
            'host_name',
            'host_address'
        );

        $columnConfig = $this->Config('columns');
        if ($columnConfig->isEmpty()) {
            $displayColumns = array(
                'type',
                'message',
                'program',
                'facility',
                'created'
            );
        } else {
            $displayColumns = $columnConfig->keys();
        }

        $columns = array_merge($staticColumns, array_diff($displayColumns, $staticColumns));

        $event = $this->getDb()
            ->select()
            ->from('event', $columns)
            ->where('id', $eventId);

        $event->applyFilter(Filter::matchAny(array_map(
            '\Icinga\Data\Filter\Filter::fromQueryString',
            $this->getRestrictions('eventdb/events/filter', 'eventdb/events')
        )));

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
            ->where('event_id', $eventId);

        $this->setupPaginationControl($comments);

        $this->setupFilterControl(
            $comments,
            array(
                'type'      => $this->translate('Type'),
                'message'   => $this->translate('Comment'),
                'created'   => $this->translate('Created'),
                'user'      => $this->translate('Author')
            ),
            array('message'),
            array('id', 'format')
        );

        $this->setupLimitControl();

        $this->setupSortControl(
            array(
                'type'      => $this->translate('Type'),
                'message'   => $this->translate('Comment'),
                'created'   => $this->translate('Created'),
                'user'      => $this->translate('Author')
            ),
            $comments,
            array('created' => 'desc')
        );

        if ($this->params->get('format') === 'sql') {
            echo '<pre>'
                . htmlspecialchars(wordwrap($event))
                . '</pre>';
            echo '<pre>'
                . htmlspecialchars(wordwrap($comments))
                . '</pre>';
            exit;
        }

        if ($this->hasPermission('eventdb/interact')) {
            $commentForm = new EventCommentForm();
            $commentForm
                ->setDb($this->getDb())
                ->setFilter(Filter::expression('id', '=', $eventId))
                ->handleRequest();
            $this->view->commentForm = $commentForm;
        }

        $this->view->columnConfig = $columnConfig;
        $this->view->comments = $comments;
        $this->view->eventData = $event->fetchRow();
        $this->view->displayColumns = $columns;
    }
}
