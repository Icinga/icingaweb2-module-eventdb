<?php
/* Icinga Web 2 | (c) 2018 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Hook;

use Icinga\Application\ClassLoader;
use Icinga\Application\Icinga;
use Icinga\Application\Modules\Module;
use Icinga\Module\Eventdb\Event;
use Icinga\Web\View;

/**
 * Base class for hooks extending the detail view of events
 *
 * Extend this class if you want to extend the detail view of events with custom HTML.
 */
abstract class DetailviewExtensionHook
{
    /**
     * The view the generated HTML will be included in
     *
     * @var View
     */
    private $view;

    /**
     * The module of the derived class
     *
     * @var Module
     */
    private $module;

    /**
     * Create a new hook
     *
     * @see init() For hook initialization.
     */
    final public function __construct()
    {
        $this->init();
    }

    /**
     * Overwrite this function for hook initialization, e.g. loading the hook's config
     */
    protected function init()
    {
    }

    /**
     * Shall return valid HTML to include in the detail view
     *
     * @param Event $event The event to generate HTML for
     *
     * @return string
     */
    abstract public function getHtmlForEvent(Event $event);

    /**
     * Shall return valid HTML to include in the multi-select view for events
     *
     * @param Event[] $events The events to generate HTML for
     *
     * @return string
     */
    public function getHtmlForEvents($events)
    {
        return '';
    }

    /**
     * Get {@link view}
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set {@link view}
     *
     * @param   View $view
     *
     * @return  $this
     */
    public function setView($view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Get the module of the derived class
     *
     * @return Module
     */
    public function getModule()
    {
        if ($this->module === null) {
            $class = get_class($this);
            if (ClassLoader::classBelongsToModule($class)) {
                $this->module = Icinga::app()->getModuleManager()->getModule(ClassLoader::extractModuleName($class));
            }
        }

        return $this->module;
    }

    /**
     * Set the module of the derived class
     *
     * @param Module $module
     *
     * @return $this
     */
    public function setModule(Module $module)
    {
        $this->module = $module;

        return $this;
    }
}
