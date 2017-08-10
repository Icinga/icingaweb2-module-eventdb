<?php

namespace Tests\Icinga\Module\Eventdb\CustomVariable;

use Icinga\Module\Eventdb\Data\LegacyFilterParser;
use Icinga\Module\Eventdb\Test\BaseTestCase;

class LegacyFilterParserTest extends BaseTestCase
{
    /**
     * Some filter examples that should work and match the result
     *
     * Note: This is not always clean JSON, but it should work!
     *
     * @var array
     */
    protected $validFilters = array(
        '{}'                                                         => 'host_name=testhost', // default filter
        '{ host: "test" }'                                           => 'host_name=test',
        "{ host: 'test' }"                                           => 'host_name=test',
        "{ host: 'otherhostname' }"                                  => 'host_name=otherhostname',
        "{ host: 'specialhostname', priorityExclusion: [] }"         => 'host_name=specialhostname',
        "{ host: 'specialhostname', priorityExclusion: [6,7,8] }"    => 'host_name=specialhostname&priority!=6&priority!=7&priority!=8',
        '{ "host": "*" }'                                            => '', // doesn't make much sense, but well...
        '{ "host": "*", "programInclusion": ["cloud-monitoring"] }'  => 'program=cloud-monitoring',
        '{ "host": ".*", "programInclusion": ["cloud-monitoring"] }' => 'program=cloud-monitoring',
        '{ "host": "myhost.*.example.com" }'                         => 'host_name=myhost%2A.example.com',
        "{ programInclusion: ['test1', 'test2'] }"                   => 'host_name=testhost&(program=test1|program=test2)',
        "{ programInclusion: ['test'] }"                             => 'host_name=testhost&program=test',
        "{ programExclusion: ['test'] }"                             => 'host_name=testhost&program!=test',
        "{ programExclusion: ['test1', 'test2'] }"                   => 'host_name=testhost&program!=test1&program!=test2',
    );

    public function testFiltersThatContainSomeJson()
    {
        $filters = array(
            ' { host: "test" } ',
            ' {} ',
            '{}',
            "{\n\"multiline\": 1\n}",
        );
        foreach ($filters as $filter) {
            $this->assertTrue(LegacyFilterParser::isJsonFilter($filter));
        }
    }

    public function testFiltersThatDoNotContainJson()
    {
        $filters = array(
            ' {xxxx ',
            1337,
            'sometext',
            "{\nbrokenjson\n",
        );
        foreach ($filters as $filter) {
            $this->assertFalse(LegacyFilterParser::isJsonFilter($filter), 'Filter: ' . $filter);
        }

    }

    public function testParsingFilters()
    {
        foreach ($this->validFilters as $json => $result) {
            $this->assertTrue(
                LegacyFilterParser::isJsonFilter($json),
                'Should be recognized as JSON filter by isJsonFilter'
            );

            $filter = LegacyFilterParser::parse($json, 'testhost');
            $this->assertEquals(
                $result,
                $filter->toQueryString(),
                'Resulting URL filter should match for json: ' . $json
            );
        }
    }
}
