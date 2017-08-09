<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Module\Eventdb\EventdbController;
use Icinga\Module\Eventdb\Forms\Event\EventCommentForm;
use Icinga\Web\Url;

class EventController extends EventdbController
{
    public function indexAction()
    {
        $eventId = $this->params->getRequired('id');

        $this->getTabs()->add('event', array(
            'active'    => true,
            'title'     => $this->translate('Event'),
            'url'       => Url::fromRequest()
        ));

        $staticColumns = array(
            'id',
            'created',
            'type',
            'ack',
            'priority',
            'host_name',
            'host_address'
        );

        $columnConfig = $this->Config('columns');
        if ($columnConfig->isEmpty()) {
            $displayColumns = array(
                'message',
                'program',
                'facility'
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
                ->where('event_id', $eventId);

            if ($this->hasPermission('eventdb/interact')) {
                $commentForm = new EventCommentForm();
                $commentForm
                    ->setDb($this->getDb())
                    ->setFilter(Filter::expression('id', '=', $eventId));
                $this->view->commentForm = $commentForm;
            }

            $this->view->comments = $comments;
        }

        $format = $this->params->get('format');
        if ($format === 'sql') {
            echo '<pre>'
                . htmlspecialchars(wordwrap($event))
                . '</pre>';

            if ($comments !== null) {
                echo '<pre>'
                    . htmlspecialchars(wordwrap($comments))
                    . '</pre>';
            }

            exit;
        }

        if ($commentForm !== null) {
            $commentForm->handleRequest();
        }

        $this->view->columnConfig = $columnConfig;
        $this->view->eventData = $event->fetchRow();
        $this->view->displayColumns = $displayColumns;
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
}
