<?php
/** @var Icinga\Application\Modules\Module $this */

$config = $this->getConfig();

$url = 'eventdb/events';
if (($default_filter = $config->get('global', 'default_filter')) !== null) {
    $url .= '?' . $default_filter;
}

$section = $this->menuSection('EventDB', array(
    'icon'      => 'tasks',
    'priority'  => 200,
    'url'       => $url,
));

$this->provideConfigTab('config', array(
    'title' => $this->translate('Configure EventDB'),
    'label' => $this->translate('Config'),
    'url' => 'config'
));
$this->provideConfigTab('monitoring', array(
    'title' => $this->translate('Configure integration into the monitoring module'),
    'label' => $this->translate('Monitoring'),
    'url' => 'config/monitoring'
));

$this->providePermission(
    'eventdb/events',
    $this->translate('Allow to view events')
);

$this->providePermission(
    'eventdb/comments',
    $this->translate('Allow to view comments')
);

$this->providePermission(
    'eventdb/interact',
    $this->translate('Allow to acknowledge and comment events')
);

$this->provideRestriction(
    'eventdb/events/filter',
    $this->translate('Restrict views to the events that match the filter')
);
