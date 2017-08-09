<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Controllers;

use Icinga\Module\Eventdb\EventdbController;

class CommentsController extends EventdbController
{
    /**
     * @deprecated Moved to eventdb/events/details
     */
    public function newAction()
    {
        $this->redirectNow($this->getRequest()->getUrl()->setPath('eventdb/events/details'));
    }
}
