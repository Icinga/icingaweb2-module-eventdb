<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb;

use ArrayObject;

class Event extends ArrayObject
{
    public static $facilities = array(
        0 => 'kernel messages',
        1 => 'user-level messages',
        2 => 'mail system',
        3 => 'system daemons',
        4 => 'security/authorization messages',
        5 => 'messages generated internally by syslogd',
        6 => 'line printer subsystem',
        7 => 'network news subsystem',
        8 => 'UUCP subsystem',
        9 => 'clock daemon',
        10 => 'security/authorization messages',
        11 => 'FTP daemon',
        12 => 'NTP subsystem',
        13 => 'log audit',
        14 => 'log alert',
        15 => 'clock daemon',
        16 => 'local use 0',
        17 => 'local use 1',
        18 => 'local use 2',
        19 => 'local use 3',
        20 => 'local use 4',
        21 => 'local use 5',
        22 => 'local use 6',
        23 => 'local use 7'
    );

    public static $priorities = array(
        0 => 'emergency',
        1 => 'alert',
        2 => 'critical',
        3 => 'error',
        4 => 'warning',
        5 => 'notice',
        6 => 'info',
        7 => 'debug'
    );

    public static $types = array(
        0 => 'syslog',
        1 => 'snmp',
        2 => 'mail'
    );

    public static $typeIcons = array(
        '_default' => 'help',
        'syslog'   => 'doc-text',
        'snmp'     => 'plug',
        'mail'     => 'bell',
    );

    public function __construct($data)
    {
        parent::__construct($data, ArrayObject::ARRAY_AS_PROPS);
    }

    public function offsetGet($index)
    {
        if (! $this->offsetExists($index)) {
            return null;
        }
        $getter = 'get' . ucfirst($index);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        return parent::offsetGet($index);
    }

    public function getAck()
    {
        return (bool) parent::offsetGet('ack');
    }

    public function getFacility()
    {
        $facility = (int) parent::offsetGet('facility');
        return array_key_exists($facility, static::$facilities) ? static::$facilities[$facility] : $facility;
    }

    public function getPriority()
    {
        $priority = (int) parent::offsetGet('priority');
        return array_key_exists($priority, static::$priorities) ? static::$priorities[$priority] : $priority;
    }

    public function getType()
    {
        $type = (int) parent::offsetGet('type');
        return array_key_exists($type, static::$types) ? static::$types[$type] : $type;
    }

    public function getTypeIcon()
    {
        if (array_key_exists($type = $this->getType(), static::$typeIcons)) {
            return static::$typeIcons[$type];
        } else {
            return static::$typeIcons['_default'];
        }
    }

    public static function fromData($data)
    {
        return new static($data);
    }

    public static function getPriorityId($priorityName)
    {
        $priorities = array_flip(static::$priorities);
        return $priorities[$priorityName];
    }
}
