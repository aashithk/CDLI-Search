<?php
/**
 * Created by PhpStorm.
 * User: changun
 * Date: 3/2/14
 * Time: 9:41 PM
 */

class FullObjectRenderer extends ObjectRenderer
{
    // maps the headings in the transliterated text with the html code they should be replaced with
    static public $TRANS_HEADERS = Array(
        Array('/@tablet.*?(\\n|$)/', ''),
        Array('/@fragment.*?(\\n|$)/', ''),
        Array('/@object.*?(\\n|$)/', ''),
        Array('/@bulla.*?(\\n|$)/', ''),
        Array('/@obverse/', '<br><i>obverse</i>'),
        Array('/@reverse/', '<br><i>reverse</i>'),
        Array('/@top/', '<br><i>top</i>'),
        Array('/@bottom/', '<br><i>bottom</i>'),
        Array('/@left/', '<br><i>left</i>'),
        Array('/@right/', '<br><i>right</i>'),
        Array('/@date/', '<br><i>date</i>'),
        Array('/@edge/', '<br><i>edge</i>'),
        Array('/@envelope/', '<br><i>envelope</i>'),
        Array('/@surface/', '<br><i>surface</i>'),
        Array('/@seal/', '<br><i>seal</i>'),
        Array('/@column/', 'column')
    );
    private $editable;
    private $user;

    public function __construct($fields,
                                $transPhrases = Array(),
                                $translationPhrases = Array(),
                                $commentPhrases = Array(),
                                $structurePhrases =Array(),
                                $searchMode = Search::FULLTEXT_MODE,
                                $caseSensitive = false,
                                \User $user)
    {

        parent::__construct($fields, $transPhrases, $translationPhrases, $commentPhrases,$structurePhrases, $searchMode, $caseSensitive);
        $this->editable = $user->canEditTranliterations();
        $this->user = $user;
    }

    private function getTransliterationHTML(\Trans $trans)
    {
        try {
            // use "try" to check if the trans exist
            $text = $trans->getWholetext();
        } catch (Doctrine\ORM\EntityNotFoundException $e) {
            return "";
        }
        // get rid of all the junk at the beginning of the translation
        $start_index = strpos($text, '@');
        $text = substr($text, $start_index, strlen($text) - 1);
        
        $retLines = array();
        $last_line_is_comment = false;

        // ok now start process the the text line by line
        foreach (explode("\n", $text) as $line) {
            if ($line == "") {
                $retLines[] = "";
                continue;
            }
            if (substr($line, 0, 2) == ">>")
                continue;
            // if it is part of text
            if (preg_match("/^[0-9]+/", $line)) {
                $line_is_comment = false;
                // highlight the line if it contains all the trans phrase
                // or it contains at least one trans phrase and the search mode = FULL_TEXT
                if (($this->searchMode == SEARCH::LINE_MODE and $this->testLineContainsAllTransPhrases($line))
                    or($this->searchMode == SEARCH::FULLTEXT_MODE and $this->testLineContainsAtLeastOneTransPhrase($line))
                ) {
                    $newline = $this->getLineHTMLWithHighlight($line);
                } else {
                    $newline = htmlspecialchars($line);
                }
                #detect all the "&" and create a column
                #$newline=str_replace('&amp;', '&<br>', $newline); # Formatting was not as expected since dual coulumn not possible.
                $newline="<span id='markTrans'>" . $newline . "</span>";
            } // if it is translation
            else if (startsWith($line, "#tr")) {
                $new_tr_line = substr($line, 4);
                $language = htmlspecialchars(substr($new_tr_line, 0, 3));

                if ($this->testLineContainsAtLeastOneTranslationPhrase($line)) {
                    $translation_string = substr($this->getLineHTMLWithHighlight($line), 7);
                } else {
                    $translation_string = htmlspecialchars(substr($new_tr_line, 3));
                }

                $newline = "<span id='translation' ><i id='translation1'>" . $language . "</i>" . $translation_string . "</span>";
                $line_is_comment = true;
            } // Just # is a note, $ is tablet information. both should be stripped of tag and then indented/greyed.
            else if (startsWith($line, "#")) {
                if ($this->testLineContainsAtLeastOneCommentPhrase($line)) {
                    $newline = substr($this->getLineHTMLWithHighlight($line), 1);
                } else {
                    $newline = htmlspecialchars(substr($line, 1));
                }

                $newline = "<span id='markH'>" . $newline . "</span>";
                $line_is_comment = true;
            } else if (startsWith($line, "$")) {
                if ($this->testLineContainsAtLeastOneCommentPhrase($line)) {
                    $newline = substr($this->getLineHTMLWithHighlight($line), 1);
                } else {
                    $newline = htmlspecialchars(substr($line, 1));
                }

                $newline = "<span id='markS'>" . $newline . "</span>";
                $line_is_comment = true;
            } else if (startsWith($line, "@tablet")) {

                $newline = "";
                $line_is_comment = true;

            }
            else if (startsWith($line, "@object")) {

                $newline = "";
                $line_is_comment = true;

            }
            else if (startsWith($line, "@")) {


           // if(startsWith($line, "@column")||startsWith($line, "@obverse") ||startsWith($line, "@bottom")  ||startsWith($line, "@left") ||startsWith($line, "@envelope") 
             //               ||startsWith($line, "@object") ||startsWith($line, "@reverse") ||startsWith($line, "@seal")  )
            //{   
                if ($this->testLineContainsAtLeastOneStructurePhrase($line)) {
                    $newline = substr($this->getLineHTMLWithHighlight($line), 1);
                } else {
                    $newline = htmlspecialchars(substr($line, 1));
                }

                $newline = "<br /><span style='color: grey;'>" . $newline . "</span>";
                $line_is_comment = true;
           /* }
            else
            {
               if ($this->testLineContainsAtLeastOneStructurePhrase($line)) {
                    $newline = substr($this->getLineHTMLWithHighlight($line), 1);
                } else {
                    $newline = htmlspecialchars(substr($line, 1));
                }

                $newline = "<span id='markS'>" . $newline . "</span>";
                $line_is_comment = true;  
            }
            */

            } 
            // this line is neither part of text or comment or structure
            // it is possibly a header
            else {
                // WARNING! every html special char must be escaped before output
                $newline = htmlspecialchars($line);
                foreach (FullObjectRenderer::$TRANS_HEADERS as $trans_headers) {
                    if (preg_match($trans_headers[0], $newline)) {
                        $newline = preg_replace($trans_headers[0], $trans_headers[1], $newline);
                        break;
                    }
                }
                $line_is_comment = false;
            }
            // we have finished processed the line

            // add one blank line at front if this line is the start of a new block of text
            /*if (!$line_is_comment && $last_line_is_comment) {
                $retLines[] = "";
            }*/
            $last_line_is_comment = $line_is_comment;
            $retLines[] = $newline;
        }
        if (trim($retLines[count($retLines) - 1]) != "") {
            // add a blank line to the end, if there has not had one yet
            $retLines[] = "";
        }
        return join("<br>\n", $retLines);
    }

    function getEditButtonHTML(\Object $object)
    {
        $images = $object->getImageWebAddresses();
        $edit_items = Array(
            'display' => "",
            'display_addr' => "",
            'detail image' => "",
            'line art' => ""
        );
        foreach ($edit_items as $key => $item) {
            if (isset($images[$key])) {
                $edit_items[$key] = $images[$key];
            }
        }
        $html = '<form action="edit.php" method="post" target="_blank">';
        $html .= '<input type="hidden" name="editingid" id="editingid' . $object->getObjectId() . '" value="' . $object->getObjectId() . '"/>';
        $html .= '<input type="hidden" name="fileSrc" id="fileSrc" value="' . $edit_items['display'] . '"/>';
        $html .= '<input type="hidden" name="fileLink" id="fileLink" value="' . $edit_items['display_addr'] . '"/>';
        $html .= '<input type="hidden" name="detailLink" id="detailLink" value="' . $edit_items['detail image'] . '"/>';
        $html .= '<input type="hidden" name="lineArtLink" id="lineArtLink" value="' . $edit_items['line art'] . '"/>';
        $html .= '<input type="submit" value="Edit" /></form><br/>';
        return $html;
    }

    function getRevisionHistoryHTML(\Object $object)
    {
        $html = "";
        $revs = $object->getTrans()->getRevHistories();
        if (!$revs->isEmpty()) {
            $html .= "Uploads and  Revision(s): ";
        } else {
            return "";
        }
        // count how many rev has been shown
        $shownRevs = 0;
        /* @var RevHistory $rev */
        foreach ($revs as $rev) {
            $raw_obj_id = $object->getObjectId();
            $revtime = $rev->getModDate()->format('Y-m-d H:i:s');;
            $revauthor = $rev->getAuthor();
            $revcredit = $rev->getCredit();
            if ($revcredit == "") {
                $revcredit = $revauthor;
            }
            $string_revtime_html = urlencode($revtime);
            $html .= "<br/>";
            $html .= "<a target=\"_blank\" HREF=\"revhistory.php/?txtversion=$string_revtime_html&txtpnumber=$raw_obj_id&\" />";

            $html .= "$revtime by $revauthor, credit $revcredit";
            $html .= "</a>";
            $shownRevs++;
            if (!$this->user->canViewPrivateTransliterations() && $shownRevs == 5) {
                // for public users, only show 5 most recent revisions
                break;
            }
        }
        return $html;
    }

    function getHTMLForObject(\Object $object)
    {

        // table to hold all three columns (info, image, text) for a result
        $html = '<br/><table class="full_object_table" width="1300" border="0" cellspacing="0" cellpadding="8" style="font-size: 12px;">';
        $html .= '<tr align="left" valign="top">';

        // left (info) column of each result
        $html .= '<td width="300">';

        //put the edit button in place
        if ($this->editable) {

            $html .= $this->getEditButtonHTML($object);
        }

        // display designation field separately, which is also
        // the link to the archival view for this object
        $html .= '<a target="_blank" href="archival_view_new.php?ObjectID=';
        $html .= $object->getObjectPId() . '">';
        $html .= '<b><font style = "font-size: 13px;">' . $object->getDesignation() . '</font></b>';
        $html .= '<font style = "font-size: 13px;"  size = "-3"><br/>Click for archival page</font>';
        $html .= '</a><br/><br/>';

        // display information about each field in a table
        $html .= '<table width="300" border="0" cellspacing="4" cellpadding="0" style="font-size: 11px">';

        foreach ($this->fields as $field) {
            $html .= '<tr al ign="left" valign="top">';
            // render the full field name
            $html .= '<td width="116">' . Search::$FIELD_MAPPINGS[$field][1] . '</td>';
            // render the value html
            if ($field != "ObjectID") {
                $html .= '<td>' . $this->getCatalogueHTMLByField($object, $field) . '</td>';
            } else {
                // use the raw PID text
                $html .= '<td>' . $object->getObjectPId() . "</td>";
            }

            $html .= '</tr>';

        }

        $html .= '</table>';
        $html .= '<br/><div style="font-size: 11px">Can you improve upon the content of this entry?<br/>';
        $html .= '<a href="http://cdli.ucla.edu/?q=cdli-corrections-and-additions-help-page" target="_blank">Please contact us!</a></div>';
        $html .= '</td>';

        // middle (image) column
        $html .= '<td width="300" valign="top" align="middle">';

        //New PDF link addition code: separate from image logic

        $file_base = $object->getObjectPId();
        if (file_exists("/Library/WebServer/Documents/cdli/dl/pdf/$file_base" . ".pdf")) {
            $link="/dl/pdf/$file_base" . ".pdf";
            $desc="commentary";
            $html .= '<a href="' . $link . '"  target="_blank">View ' . $desc . '</a>';
            $html .= '<br/>';
        }



        $html .= $this->getImageHTMLFor($object, $this->user);
        $html .= '</td>';

        // right column (translated text and revision history)
        $html .= '<td style="font-size: 11px">';


        // only show trans when the user has sufficient permission
        $showTransliteration = $this->user->canViewPrivateTransliterations() || $object->isPublicAtf();
        if ($showTransliteration) {
            $html .= '<b>' . $object->getFormattedObjectType() . '</b><br/>';

            try {
                // use "try" to check if the trans exist
                $object->getTrans()->getWholetext();

                $html .= $this->getTransliterationHTML($object->getTrans());
                $html .= '<br/>';
                $html .= $this->getRevisionHistoryHTML($object);
            } catch (Doctrine\ORM\EntityNotFoundException $e) {

            }

        }


        $html .= '</td>';

        $html .= '</tr>';
        $html .= '</table>';
        $html .= '<hr align="left" width="1200">';


        return $html;
    }
}