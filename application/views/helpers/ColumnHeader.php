<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

class Zend_View_Helper_ColumnHeader extends Zend_View_Helper_Abstract
{
    public function columnHeader($columnHeader, $classes = array(), $plain = false)
    {
        $header = $this->view->columnConfig->get($columnHeader, 'label', ucwords(str_replace('_', ' ', $columnHeader)));
        if ($plain) {
            return $header;
        }
        $htm = '<th classes="' . implode(' ', $classes) . '">';
        $htm .= $this->view->escape($header);
        $htm .= '</th>';
        return $htm;
    }
}
