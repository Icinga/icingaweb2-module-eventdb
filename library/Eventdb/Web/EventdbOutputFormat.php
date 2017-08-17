<?php

namespace Icinga\Module\Eventdb\Web;

use Icinga\Web\Widget\Tabextension\OutputFormat;
use Icinga\Web\Widget\Tabs;

class EventdbOutputFormat extends OutputFormat
{
    /**
     * TEXT output type
     */
    const TYPE_TEXT = 'text';

    /**
     * Types that are disabled by default
     *
     * @var array
     */
    protected static $disabledTypes = array(self::TYPE_PDF, self::TYPE_CSV);

    /**
     * Types that are enabled in addition to default
     *
     * @var array
     */
    protected $enable = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($disabled = array(), $enable = array())
    {
        $this->enable = $enable;
        $disabled = array_merge(static::$disabledTypes, $disabled);
        parent::__construct($disabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes()
    {
        $supported = parent::getSupportedTypes();

        if (in_array(self::TYPE_TEXT, $this->enable)) {
            $supported[self::TYPE_TEXT] = array(
                'name'      => 'text',
                'label'     => mt('eventdb', 'Text'),
                'icon'      => 'doc-text',
                'urlParams' => array('format' => 'text'),
            );
        }

        return $supported;
    }

    public function apply(Tabs $tabs)
    {
        parent::apply($tabs);

        if ($textTab = $tabs->get(self::TYPE_TEXT)) {
            $textTab->setTargetBlank(false);
        }
    }
}
