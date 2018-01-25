<?php
/* Icinga Web 2 | (c) 2018 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Controllers;

use Icinga\Web\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        $this->redirectNow('eventdb/events');
    }
}
