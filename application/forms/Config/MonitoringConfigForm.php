<?php
/* Icinga Web 2 - EventDB | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Forms\Config;

use Icinga\Forms\ConfigForm;

/**
 * Form for managing settings for the integration into monitoring module
 */
class MonitoringConfigForm extends ConfigForm
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setSubmitLabel($this->translate('Save'));
    }

    /**
     * {@inheritdoc}
     */
    public function createElements(array $formData)
    {
        $this->addElement(
            'text',
            'monitoring_custom_var',
            array(
                'description' => $this->translate('Name of the custom variable to enable EventDB integration for (usually "edb")'),
                'label'       => $this->translate('Custom Variable')
            )
        );

        $this->addElement(
            'checkbox',
            'monitoring_always_on_host',
            array(
                'description' => $this->translate('Always enable the integration on hosts, even when the custom variable is not set'),
                'label'       => $this->translate('Always enable for hosts')
            )
        );

        $this->addElement(
            'checkbox',
            'monitoring_always_on_service',
            array(
                'description' => $this->translate('Always enable the integration on services, even when the custom variable is not set'),
                'label'       => $this->translate('Always enable for services')
            )
        );

        $this->addElement(
            'checkbox',
            'monitoring_detailview_disable',
            array(
                'description' => $this->translate('Disable the detail view inside the monitoring module'),
                'label'       => $this->translate('Disable the detail view')
            )
        );

        $this->addElement(
            'text',
            'monitoring_detailview_filter',
            array(
                'description' => $this->translate('Filter events in the detail view area inside the monitoring module'),
                'label'       => $this->translate('Filter the detail view'),
                'value'       => 'ack=0', // Also see DetailviewExtension
            )
        );
    }
}
