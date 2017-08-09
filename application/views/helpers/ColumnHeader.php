<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

class Zend_View_Helper_ColumnHeader extends Zend_View_Helper_Abstract
{
    public function columnHeader($columnHeader, $classes = array())
    {
        return '<th classes="' . implode(' ', $classes) . '">' . $this->view->escape(
            $this->view->columnConfig->get($columnHeader, 'label', ucwords(str_replace('_', ' ', $columnHeader)))
        ) . '</th>';
    }
}
