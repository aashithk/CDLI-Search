<?php
/**
 * Created by PhpStorm.
 * User: changun
 * Date: 3/2/14
 * Time: 9:47 PM
 */

class SimpleObjectRenderer extends ObjectRenderer
{
    function getHTMLForObject(\Object $object)
    {
        $html = '<tr align="left" valign="top">';
        foreach ($this->fields as $field) {
            $html .= '<td>';
            $html .= $this->getCatalogueHTMLByField($object, $field);
            $html .= '</td>';
        }
        $html .= '</tr>';
        return $html;
    }
} 