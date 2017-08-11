<?php
/* Icinga Web 2 - EventDB | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Data;

use Icinga\Data\Filter\Filter;
use Icinga\Data\Filter\FilterAnd;
use Icinga\Data\Filter\FilterOr;
use Icinga\Exception\InvalidPropertyException;

/**
 * Class LegacyFilterParser
 *
 * Utility class to parse Icinga-web 1.x JSON filters of the EventDB module
 */
class LegacyFilterParser
{
    /**
     * @param $json        string  JSON data
     * @param $host        string  Icinga host name (for default host filter and logging)
     * @param $service     string  Icinga service name (for logging)
     *
     * @return Filter
     * @throws InvalidPropertyException When filter could not be parsed
     */
    static public function parse($json, $host, $service = null)
    {
        $json = static::fixJSONQuotes($json);

        $data = json_decode($json, false, 5);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidPropertyException(
                'Could not decode legacy filter (host=%s)%s (%s): %s',
                $host,
                ($service ? ' (service=' . $service . ')' : ''),
                json_last_error_msg(),
                $json
            );
        }

        $filter = new FilterAnd();

        if (property_exists($data, 'host')) {
            // Note: we can' support regexp right now, but we replace '.*' to a normal wildcard
            $data->host = str_replace('.*', '*', $data->host);
            if ($data->host === '*') {
                $data->host = null;
            }
        } else {
            $data->host = $host;
        }
        if ($data->host !== null) {
            $filter->andFilter(Filter::expression('host_name', '=', $data->host));
        }

        static::handleArray($filter, $data, 'programInclusion', 'program');
        static::handleArray($filter, $data, 'programExclusion', 'program', '!=');

        static::handleArray($filter, $data, 'priorityExclusion', 'priority', '!=');
        static::handleArray($filter, $data, 'sourceExclusion', 'source', '!=');
        static::handleArray($filter, $data, 'facilityExclusion', 'facility', '!=');

        // TODO: msg - when really needed
        // TODO: startTime - when really needed

        // Note: any other field or data part gets ignored...

        return $filter;
    }

    protected static function handleArray(Filter $filter, $data, $property, $filterAttr, $op = '=')
    {
        if (property_exists($data, $property) && ! empty($data->$property)) {
            if ($op === '!=') {
                $subFilter = $filter;
            } else {
                $subFilter = new FilterOr;
            }

            /*
            if (is_array($data->$property) && count($data->$property) === 1) {
                $data->$property = current($data->$property);
            }
            */
            if (! is_array($data->$property)) {
                $data->$property = array($data->$property);
            }
            foreach ($data->$property as $val) {
                $subFilter->addFilter(Filter::expression($filterAttr, $op, $val));
            }

            if ($subFilter !== $filter) {
                $filters = $subFilter->filters();
                if ($filter->isChain() && count($filters) > 1) {
                    $filter->andFilter($subFilter);
                } else {
                    $filter->andFilter(current($filters));
                }
            }
        }
    }

    /**
     * @author partially by NikiC https://stackoverflow.com/users/385378/nikic
     * @source partially from https://stackoverflow.com/a/20440596/449813
     *
     * @param $json string
     *
     * @return string
     */
    public static function fixJSONQuotes($json)
    {
        // fix unquoted identifiers
        $json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', $json);

        $regex = <<<'REGEX'
~
    "[^"\\]*(?:\\.|[^"\\]*)*"
    (*SKIP)(*F)
  | '([^'\\]*(?:\\.|[^'\\]*)*)'
~x
REGEX;

        return preg_replace_callback($regex, function ($matches) {
            return '"' . preg_replace('~\\\\.(*SKIP)(*F)|"~', '\\"', $matches[1]) . '"';
        }, $json);
    }

    /**
     * Basic check if it looks like a JSON filter
     *
     * @param $string
     *
     * @return bool
     */
    static public function isJsonFilter($string)
    {
        if (! $string || ! is_string($string)) {
            return false;
        }

        $string = trim($string);
        if (empty($string)) {
            return false;
        }

        if (preg_match('/^\{.*\}$/s', $string)) {
            // looks like JSON data
            return true;
        }
        return false;
    }
}
