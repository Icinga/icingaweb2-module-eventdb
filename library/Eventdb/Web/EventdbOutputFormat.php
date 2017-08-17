<?php

namespace Icinga\Module\Eventdb\Web;

use Icinga\Web\Widget\Tabextension\OutputFormat;

class EventdbOutputFormat extends OutputFormat
{
    /**
     * Types that are disabled by default
     *
     * @var array
     */
    protected static $disabledTypes = array(self::TYPE_PDF, self::TYPE_CSV);

    /**
     * {@inheritdoc}
     */
    public function __construct($disabled = array())
    {
        $disabled = array_merge(static::$disabledTypes, $disabled);
        parent::__construct($disabled);
    }
}
