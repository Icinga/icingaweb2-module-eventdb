<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Forms\Events;

use Icinga\Data\Filter\Filter;
use Icinga\Web\Form;

class AckFilterForm extends Form
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setAttrib('class', 'inline ack-filter-form');
    }

    /**
     * {@inheritdoc}
     */
    public function addSubmitButton()
    {
        if ((bool) $this->getRequest()->getUrl()->getParams()->get('ack', true)) {
            $icon = 'ok';
            $title = $this->translate('Hide acknowledged events');
        } else {
            $icon = 'cancel';
            $title = $this->translate('Show also acknowledged events');
        }

        $this->addElements(array(
            array(
                'button',
                'btn_submit',
                array(
                    'class'         => 'link-button spinner',
                    'decorators'    => array(
                        'ViewHelper',
                        array('HtmlTag', array('tag' => 'div', 'class' => 'control-group form-controls'))
                    ),
                    'escape'        => false,
                    'ignore'        => true,
                    'label'         => $this->getView()->icon($icon) . $this->translate('Ack'),
                    'type'          => 'submit',
                    'title'         => $title,
                    'value'         => $this->translate('Ack')
                )
            )
        ));

        return $this;
    }

    public function onSuccess()
    {
        $redirect = clone $this->getRequest()->getUrl();
        $params = $redirect->getParams();
        $modifyFilter = $params->shift('modifyFilter');
        $columns = $params->shift('columns');
        if (! (bool) $this->getRequest()->getUrl()->getParams()->get('ack', true)) {
            $params->remove('ack');
        } else {
            $redirect->setQueryString(
                Filter::fromQueryString($redirect->getQueryString())
                    ->andFilter(Filter::expression('ack', '=', 0))
                    ->toQueryString()
            );
        }
        $params = $redirect->getParams();
        if ($modifyFilter) {
            $params->add('modifyFilter');
        }
        if ($columns) {
            $params->add('columns', $columns);
        }
        $this->setRedirectUrl($redirect);
        return true;
    }
}
