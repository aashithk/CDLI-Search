<?php
/**
 * Created by PhpStorm.
 * User: changun
 * Date: 5/26/14
 * Time: 2:17 PM
 */

class EditPageRenderer extends ObjectRenderer
{
    public function __construct($fields, \User $user)
    {
        parent::__construct($fields, null, null, null, Search::FULLTEXT_MODE, false);
        $this->user = $user;
    }

    function getHTMLForObject(\Object $object)
    {
        $html = '<table ><tr style="vertical-align: top;">';
        $html .= '<td class="desc"><table>';

        foreach ($this->fields as $field) {
            $html .= '<tr class="row">';
            // render the full field name
            $html .= '<td class="cat">' . Search::$FIELD_MAPPINGS[$field][1] . '</td>';
            // render the value html
            if ($field != "ObjectID") {
                $html .= '<td class="val">' . $this->getCatalogueHTMLByField($object, $field) . '</td>';
            } else {
                // use the raw PID text
                $html .= '<td class="val">' . $object->getObjectPId() . "</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</table></td>';

        $html .= '<td class="image">';
        $html .= $this->getImageHTMLFor($object, $this->user);
        $html .= '</td>';

        $displayedText = "";
        if ($object->hasTrans()) {
            $displayedText = htmlspecialchars(trim($object->getTrans()->getWholetext(), "'"));
        }

        if ($object->canModifyBy($this->user)) {
            $html .= '<td class="editingWindow">';
            $html .= "<form id=\"editForm\" method=\"post\" action=\"modify.php\">";
            $html .= "<textarea name=\"comments\" id=\"comments\" cols=\"50\" rows=\"40\">";
            $html .= $displayedText;
            $html .= "</textarea>";
            $html .= "<p>Optional Credits : ";
            $html .= "<input type=\"text\" name=\"credits\" id=\"credits\" value=\"\">";
            $html .= "<input type=\"hidden\" name=\"originaltext\" id=\"originaltext\" value=\"$displayedText\">";
            $html .= '<input type="hidden" name="editingid" id="editingid" value="' . $object->getObjectId() . '">';
            $html .= "<input name=\"send\" id=\"send\" type=\"submit\" value=\"Send editing\" />";
            $html .= "</p>";
            $html .= "</form> </td>";
        } else {
            $html .= "<td class=\"trans\">";
            $html .= nl2br($displayedText);
            $html .= '<p>This item is locked by other user</p>';
            $html .= "</td>";
        }
        $html .= "</tr></table>";
        return $html;
    }
}