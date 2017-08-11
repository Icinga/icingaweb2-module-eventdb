<?php

namespace Tests\Icinga\Module\Eventdb\ProvidedHook\Monitoring;

use Icinga\Application\Config;
use Icinga\Module\Eventdb\ProvidedHook\Monitoring\EventdbActionHook;
use Icinga\Module\Eventdb\Test\BaseTestCase;
use Icinga\Module\Eventdb\Test\PseudoHost;
use Icinga\Module\Eventdb\Test\PseudoMonitoringBackend;
use Icinga\Module\Eventdb\Test\PseudoService;
use Icinga\Web\Navigation\NavigationItem;

class EventdbActionHookTest extends BaseTestCase
{
    public function testHostWithoutVarsAndNoConfig()
    {
        $this->setupConfiguration(null, null, null);

        $nav = EventdbActionHook::getActions($this->buildHost(null, null));

        $items = $nav->getItems();
        $this->assertCount(1, $items);

        /** @var NavigationItem $navObj */
        $navObj = current($items);

        $this->assertEquals('host_name=testhost', $navObj->getUrl()->getQueryString());
    }

    public function testHostWithoutVarsAndNormalConfig()
    {
        $this->setupConfiguration();

        $nav = EventdbActionHook::getActions($this->buildHost(null, null));

        $this->assertCount(0, $nav->getItems());
    }

    public function testHostWithVars()
    {
        $this->setupConfiguration();

        $nav = EventdbActionHook::getActions($this->buildHost());

        $items = $nav->getItems();
        $this->assertCount(1, $items);

        /** @var NavigationItem $navObj */
        $navObj = current($items);

        $this->assertEquals('host_name=testhost', $navObj->getUrl()->getQueryString());
    }

    public function testHostWithVarsAlwaysOn()
    {
        $this->setupConfiguration('edb', '1');

        $nav = EventdbActionHook::getActions($this->buildHost(null, 'othervar'));

        $this->assertCount(1, $nav->getItems());
    }

    public function testServiceWithVarsAlwaysOn()
    {
        $this->setupConfiguration('edb', null, '1');

        $nav = EventdbActionHook::getActions($this->buildService(null, 'othervar'));

        $this->assertCount(1, $nav->getItems());
    }

    public function testHostWithLegacyFilter()
    {
        $this->setupConfiguration();

        $nav = EventdbActionHook::getActions($this->buildHost("{ host: 'test2', programInclusion: 'test2' }"));

        $items = $nav->getItems();
        $this->assertCount(2, $items);

        /** @var NavigationItem $navObj */
        $navObj = current($items);

        $this->assertEquals('host_name=test2&program=test2', $navObj->getUrl()->getQueryString());
    }

    public function testHostWithFilter()
    {
        $this->setupConfiguration();

        $nav = EventdbActionHook::getActions($this->buildHost("program=test3"));

        $items = $nav->getItems();
        $this->assertCount(2, $items);

        /** @var NavigationItem $navObj */
        $navObj = current($items);

        $this->assertEquals("program=test3&host_name=testhost", $navObj->getUrl()->getQueryString());
    }

    public function testHostWithFilterThatFiltersHost()
    {
        $this->setupConfiguration();

        $nav = EventdbActionHook::getActions($this->buildHost("host_name=test3&program=test3"));

        $items = $nav->getItems();
        $this->assertCount(2, $items);

        /** @var NavigationItem $navObj */
        $navObj = current($items);

        $this->assertEquals("host_name=test3&program=test3", $navObj->getUrl()->getQueryString());
    }

    protected function configure($settings = array())
    {
        $config = Config::module('eventdb');
        $section = $config->getSection('monitoring');
        foreach ($settings as $key => $val) {
            $section->$key = $val;
        }
        $config->setSection('monitoring', $section);

        // NOTE: we need to save here, because Config::module always load config from disk
        $config->saveIni();

        return $this;
    }

    protected function setupConfiguration($custom_var = 'edb', $always_host = null, $always_service = null)
    {
        $this->configure(array(
            'custom_var'        => $custom_var,
            'always_on_host'    => $always_host,
            'always_on_service' => $always_service,
        ));
    }

    protected function monitoringBackend()
    {
        return PseudoMonitoringBackend::dummy();
    }

    protected function buildHost($plainFilter = null, $custom_var = 'edb')
    {
        $host = new PseudoHost($this->monitoringBackend(), 'testhost');
        $host->host_name = 'testhost';

        $vars = array();
        if ($custom_var !== null) {
            $vars[$custom_var] = '1';
        }
        if ($plainFilter !== null) {
            $vars[$custom_var . '_filter'] = $plainFilter;
        }
        $host->provideCustomVars($vars);

        return $host;
    }

    protected function buildService($plainFilter = null, $custom_var = 'edb')
    {
        $service = new PseudoService($this->monitoringBackend(), 'testhost', 'test');
        $service->host_name = 'testhost';
        $service->service_description = 'testhost';

        $vars = array();
        if ($custom_var !== null) {
            $vars[$custom_var] = '1';
        }
        if ($plainFilter !== null) {
            $vars[$custom_var . '_filter'] = $plainFilter;
        }
        $service->provideCustomVars($vars);

        return $service;
    }
}
