<?php
/* Icinga Web 2 - EventDB | (c) 2018 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Forms\Config;

use Icinga\Forms\ConfigForm;

class GlobalConfigForm extends ConfigForm
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
            'global_default_filter',
            array(
                'description' => $this->translate('Filter to be used by the menu link for EventDB by default'),
                'label'       => $this->translate('Default Filter')
            )
        );
    }
}
