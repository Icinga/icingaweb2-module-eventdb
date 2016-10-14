<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Module\Eventdb\EventdbController;
use Icinga\Module\Eventdb\Forms\Event\EventCommentForm;
use Icinga\Web\Url;

class CommentsController extends EventdbController
{
    public function newAction()
    {
        $this->assertPermission('eventdb/interact');

        $this->getTabs()->add('new-comment', array(
            'active'    => true,
            'title'     => $this->translate('New Comment'),
            'url'       => Url::fromRequest()
        ));

        $restrictionFilter = Filter::matchAny(array_map(
            '\Icinga\Data\Filter\Filter::fromQueryString',
            $this->getRestrictions('eventdb/events/filter', 'eventdb/events')
        ));
        $queryFilter = Filter::fromQueryString((string) $this->params);
        $filter = Filter::matchAll($restrictionFilter, $queryFilter);

        $commentForm = new EventCommentForm();
        $commentForm
            ->setDb($this->getDb())
            ->setFilter($filter)
            ->setRedirectUrl('eventdb/events')
            ->handleRequest();

        $this->view->form = $commentForm;
    }
}
