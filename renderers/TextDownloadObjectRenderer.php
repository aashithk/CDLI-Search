<?php
/**
 * Created by PhpStorm.
 * User: changun
 * Date: 3/3/14
 * Time: 9:06 PM
 */

class TextDownloadObjectRenderer extends ObjectRenderer
{
    const ALL_DATA = 1;
    const TRANS_ONLY = 2;

    private $type;

    /** @var User * */
    private $user;

    public function __construct($fields,
                                \User $user,
                                $type)
    {

        parent::__construct($fields, null, null, null, Search::FULLTEXT_MODE, false);
        $this->type = $type;
        $this->user = $user;
    }

    function getHTMLForObject(\Object $object)
    {
        $text = "";
        if ($this->type == TextDownloadObjectRenderer::ALL_DATA) {
            foreach ($this->fields as $field) {
                $text .= Search::$FIELD_MAPPINGS[$field][1];
                $text .= ": ";
                if ($field == 'ObjectID')
                    $text .= $object->getObjectPId();
                else
                    $text .= $object->getBySearchFieldName($field);
                $text .= "\n";
            }
            $text .= "Transliteration:\n";
        }

        /* write out the transliteration */
        try {
            // use "try" to check if the trans exist
            $transliteration = $object->getTrans()->getWholetext();
        } catch (Doctrine\ORM\EntityNotFoundException $e) {
            $transliteration = null;
        }
        if ($transliteration != null) {
            // write out the transliteration
            $transliteration = trim($transliteration, "'");
            if ($transliteration != "") {
                if ($object->isPublicAtf() || $this->user->canViewPrivateTransliterations()) {
                    $text .= $transliteration . "\n\n";
                }
            }
        }
        return $text;
    }
}