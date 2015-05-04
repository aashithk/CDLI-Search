<?php
/**
 * Created by PhpStorm.
 * User: changun
 * Date: 2/9/14
 * Time: 2:31 PM
 */

// helper function
function startsWith($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

abstract class ObjectRenderer
{


    /** @var TransPhrases * */
    protected $transPhrases;
    /** @var array of translation phrase regex * */
    protected $translationPhrases;
    /** @var array of comment phrase regex * */
    protected $commentPhrases;
    /** @var array of structure phrase regex * */
    protected $structurePhrases;
    /* whether make it case sensitive when highlight the text*/
    protected $caseSensitive = false;
    /* what search mode it is. For Line Mode, we only highlight the lines that contain every search trans */
    protected $searchMode;
    /* what catalogue fields to display */
    protected $fields;

    public function __construct($fields,
                                $transPhrases = Array(),
                                $translationPhrases = Array(),
                                $commentPhrases = Array(),
                                $structurePhrases=Array(),
                                $searchMode = Search::FULLTEXT_MODE,
                                $caseSensitive = false)
    {
        $this->transPhrases = $transPhrases;
        $this->translationPhrases = $translationPhrases;
        $this->commentPhrases = $commentPhrases;
        $this->structurePhrases = $structurePhrases;
        $this->searchMode = $searchMode;
        $this->caseSensitive = $caseSensitive;
        $this->fields = $fields;
    }

    // Force Extending class to define this method
    abstract protected function getHTMLForObject(\Object $object);

    protected function testLineContainsAllTransPhrases($line)
    {
        // we treat the \n or the end of text as one space
        $line = $line . " ";
        $valid_line = true;
        foreach ($this->transPhrases as $phrase) {
            $regex = $phrase->getRegex();
            $modifier = ($this->caseSensitive ? "" : "i");
            if (!preg_match("/$regex/$modifier", $line)) {
                // this line fails to contain one of the phrases
                $valid_line = false;
                break;
            }
        }
        return $valid_line;
    }

    protected function testLineContainsAtLeastOneTransPhrase($line)
    {
        // we treat the \n or the end of text as one space
        $line = $line . " ";
        foreach ($this->transPhrases as $phrase) {
            $regex = $phrase->getRegex();
            $modifier = ($this->caseSensitive ? "" : "i");
            if (preg_match("/$regex/$modifier", $line)) {
                // this line fails to contain one of the phrases
                return true;
            }
        }
        return false;
    }

    protected function testLineContainsAtLeastOneTranslationPhrase($line)
    {
        if (startsWith($line, "#tr")) {
            foreach ($this->translationPhrases as $regex) {
                // trim the prefix and parentheses
                $regex = substr($regex, strpos($regex, "+") + 1, -1);
                if (preg_match("/$regex/i", $line)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function testLineContainsAtLeastOneCommentPhrase($line)
    {
        if (startsWith($line, "# ") || startsWith($line, "$ ")) {
            foreach ($this->commentPhrases as $regex) {
                // trim the prefix and parentheses
                $regex = substr($regex, strpos($regex, "+") + 1, -1);
                if (preg_match("/$regex/i", $line)) {
                    return true;
                }
            }
        }
        return false;
    }
    protected function testLineContainsAtLeastOneStructurePhrase($line)
    {
        if (startsWith($line, "@")) {
            foreach ($this->structurePhrases as $regex) {
                // trim the prefix and parentheses
                $regex = substr($regex, strpos($regex, "+") + 1, -1);
                if (preg_match("/$regex/i", $line)) {
                    return true;
                }
            }
        }
        return false;
    }

    // This function splits a multibyte string into an array of characters. Comparable to str_split().
    function mb_str_split($string)
    {
        # Split at all position not after the start: ^
        # and not before the end: $
        return preg_split('/(?<!^)(?!$)/u', $string);
    }

    /**
     * get image HTML for the given object
     * @param \Object $object
     * @param \User $user
     * @return string image html
     */
    protected function getImageHTMLFor($object, $user)
    {
        $html = "";
        // only display images if they are not private, or the user has permission to view it
        $showImage = $user->canViewPrivateImages() || $object->isPublicImages();
       
        if ($showImage) {
             $images = $object->getImageWebAddresses();
        //add extra pdf links
            foreach ($images as $desc => $link) {
                if (!($desc == 'display' or $desc == 'display_addr')) {
                    $html .= '<a href="' . $link . '"  target="_blank">View ' . $desc . '</a>';
                    $html .= '<br/>';
                }
            }
            //display main image
            if (isset($images['display'])) {
                $html .= '<br/><a href="' . $images['display_addr'] . '" target="_blank"><img src="' . $images['display'] . '"></a>';
                $html .= '<br/> <p style="color:grey;font-size:10px">(click on image to enlarge)</p>';
            } else {
                $html .= 'No Image Available';
            }
        } else {
            $html .= 'No Public Image Available';
        }
        return $html;
    }

    protected function getLineHTMLWithHighlight($line)
    {
        // extract line numbers (i.e. "1. " or "2. ") and don't highlight them
        $prefix = "";
        if (preg_match("/^[0-9]+\\'?\\.?[A-Za-z]?[0-9]*\\.? */", $line, $matches)) {
            $prefix = $matches[0];
            $line = substr($line, strlen($prefix));
            $lineType = "transliteration";
        } else if (preg_match("/^#tr\\.[A-Za-z]{2}\\: +/", $line, $matches)) {
            $prefix = $matches[0];
            $line = substr($line, strlen($prefix));
            $lineType = "translation";
        } else if (preg_match("/^(#|\\$) +/", $line, $matches)) {
            $prefix = $matches[0];
            $line = substr($line, strlen($prefix));
            $lineType = "comment";
        } else if (preg_match("/^(@)+/", $line, $matches)) {
            $prefix = $matches[0];
            $line = substr($line, strlen($prefix));
            $lineType = "structure";
        } else {
            return $line;
        }

        // we treat the \n or the end of text as one space
        $line = " " . $line . " ";
        // where in the line to insert the start/end tags
        $startTagIndexes = array();
        $endTagIndexes = array();

        if ($lineType == "transliteration") {
            /* @var TransPhrase $phrase */
            foreach ($this->transPhrases as $phrase) {
                $regex = $phrase->getRegex();
                $phpRegex = "/$regex/" . ($this->caseSensitive ? "" : "i");
                $offset = 0;
                if (preg_match($phpRegex, $line, $matches, PREG_OFFSET_CAPTURE, $offset) == 1) {
                    // a match found
                    $match = $matches[3]; // the match of the searched phrase
                    $startTagIndexes[] = $match[1]; // the indexes of the start/end of the searched phrase in the line
                    $endIndex = $match[1] + mb_strlen($match[0]);
                    $endTagIndexes[] = $endIndex;
                    $offset = $endIndex;
                }
            }
        } else if ($lineType == "translation") {
            foreach ($this->translationPhrases as $regex) {
                // trim the prefix and parentheses
                $regex = substr($regex, strpos($regex, "+") + 1, -1);
                // skip catch all patterns in the beginning
                if (startsWith($regex, ".*")) {
                    $regex = substr($regex, 2);
                }
                $phpRegex = "/$regex/i";
                $offset = 0;
                if (preg_match($phpRegex, $line, $matches, PREG_OFFSET_CAPTURE, $offset) == 1) {
                    // a match found
                    $match = $matches[0]; // the match of the searched phrase
                    // the indexes of the start/end of the searched phrase in the line
                    $startIndex = mb_strpos($line, $match[0], $offset);
                    $startTagIndexes[] = $startIndex;
                    $endIndex = $startIndex + mb_strlen($match[0]);
                    $endTagIndexes[] = $endIndex;
                    $offset = $endIndex;
                }
            }
        } else if ($lineType == "comment") {
            foreach ($this->commentPhrases as $regex) {
                // trim the prefix and parentheses
                $regex = substr($regex, strpos($regex, "+") + 1, -1);
                // skip catch all patterns in the beginning
                if (startsWith($regex, ".*")) {
                    $regex = substr($regex, 2);
                }
                $phpRegex = "/$regex/i";
                $offset = 0;
                if (preg_match($phpRegex, $line, $matches, PREG_OFFSET_CAPTURE, $offset) == 1) {
                    // a match found
                    $match = $matches[0]; // the match of the searched phrase
                    // the indexes of the start/end of the searched phrase in the line
                    $startIndex = mb_strpos($line, $match[0], $offset);
                    $startTagIndexes[] = $startIndex;
                    $endIndex = $startIndex + mb_strlen($match[0]);
                    $endTagIndexes[] = $endIndex;
                    $offset = $endIndex;
                }
            }
        } else if ($lineType == "structure") {
            foreach ($this->structurePhrases as $regex) {
                // trim the prefix and parentheses
                $regex = substr($regex, strpos($regex, "+") + 1, -1);
                // skip catch all patterns in the beginning
                if (startsWith($regex, ".*")) {
                    $regex = substr($regex, 2);
                }
                $phpRegex = "/$regex/i";
                $offset = 0;
                if (preg_match($phpRegex, $line, $matches, PREG_OFFSET_CAPTURE, $offset) == 1) {
                    // a match found
                    $match = $matches[0]; // the match of the searched phrase
                    // the indexes of the start/end of the searched phrase in the line
                    $startIndex = mb_strpos($line, $match[0], $offset);
                    $startTagIndexes[] = $startIndex;
                    $endIndex = $startIndex + mb_strlen($match[0]);
                    $endTagIndexes[] = $endIndex;
                    $offset = $endIndex;
                }
            }
        }

        $highlightedLine = "";
        $index = 0;
        foreach ($this->mb_str_split($line) as $char) {
            // escape special chars for html
            $char = htmlspecialchars($char);
            // insert highlight span tags
            if (in_array($index, $startTagIndexes)) {
                $char = '<span class="found">' . $char;
            }
            if (in_array($index, $endTagIndexes)) {
                $char = '</span>' . $char;
            }
            $highlightedLine .= $char;
            $index++;
        }
        if (in_array(strlen($line), $endTagIndexes)) {
            $highlightedLine .= '</span>';
        }
        return trim($prefix . $highlightedLine);
    }

    protected function getCatalogueHTMLByField(\Object $object, $field)
    {
        $html = "";
        $value = $object->getBySearchFieldName($field);
        if ($value == null)
            return "";
        // deal with special cases
        if ($field == "ObjectID") {
            $html .= '<a target="_blank" href="search_results.php?SearchMode=Text&ObjectID=' . $object->getObjectPId() . '">';
            $html .= $object->getObjectPId();
            $html .= '</a>';
        } else if ($field == "SealID") {
            // add seals link
            // the seal field could contain multiple seal id separated by commas
            $seal_ids = explode(",", $value);
            foreach ($seal_ids as $seal_id) {
                $seal_id = trim($seal_id);
                $html .= '<a target="_blank" href = "search_results.php?order=MuseumNumber&SealID=' . $seal_id . '">' . $seal_id . '</a> ';
            }
        } else if ($field == "CompositeNumber") {
            // add composite link
            // the composite field could contain multiple seal id separated by commas
            $composite_nos = explode(",", $value);
            foreach ($composite_nos as $composite_no) {
                $composite_no = trim($composite_no);
                $html .= '<a target="_blank" href = "search_results.php?order=MuseumNumber&CompositeNumber=' . $composite_no . '">' . $composite_no . '</a> ';
                $score_url = "http://cdli.ucla.edu/tools/scores/" . $composite_no . ".html";
                if(file_exists("/Library/WebServer/Documents/cdli/tools/scores/{$composite_no}.html")) {
                    $html .= '(<a target="_blank" href = "' . $score_url . '">score</a>) ';
                }
            }
        } else {
            $html .= htmlspecialchars($value);
        }
        return $html;
    }
}
