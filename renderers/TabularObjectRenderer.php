<?php
/**
 * Created by PhpStorm.
 * User: changun
 * Date: 3/2/14
 * Time: 9:45 PM
 */

class TabularObjectRenderer extends ObjectRenderer
{

    static function rome($N)
    {
        $c = 'ivxlcdm';
        for ($a = 5, $b = $s = ''; $N; $b++, $a ^= 7)
            for ($o = $N % $a, $N = $N / $a ^ 0; $o--; $s = $c[$o > 2 ? $b + $N - ($N &= -2) + $o = 1 : $b] . $s) ;
        return $s;
    }

    private function getLineMarkers(\Trans $trans)
    {
        // markers of each line
        $markers = array();
        $marker1 = "";
        $marker2 = "";
        foreach (explode("\n", $trans->getWholeText()) as $line) {
            $line = trim($line);
            //check for position indicators (obverse/reverse, colreguumn)
            if ($line != "" && $line[0] == "@") {
                //some sort of indicator!
                $line_exp = explode(" ", $line);
                $part1 = trim($line_exp[0]);
                if ($part1 == "@obverse") {
                    $marker1 = "obv.";
                } else if ($part1 == "@reverse") {
                    $marker1 = "rev.";
                } else if ($part1 == "@column") {
                    // convert @column number to the rome chars
                    $marker2 = TabularObjectRenderer::rome(intval($line_exp[1]));
                } else if ($part1 == "@surface") {
                    $part2 = $line_exp[1];
                    if ($part2 == "a") {
                        $marker1 = "obv.";
                    } else {
                        $marker1 = "rev.";
                    }
                } else {
                    //not sure what's going on here - reset the markers
                    $marker1 = "";
                    $marker2 = "";
                }
            }
            $markers[] = $marker1 . " " . $marker2;
        }
        return ($markers);
    }

    // Function to generate one or more html rows for one search result
    // this function can output more than one line per result, if multiple lines in the result's
    // transliteration contain text the user searched for
    function getHTMLForObject(\Object $object)
    {
        $html = "";
        //if(count($this->structurePhrases) <= 0 )
        {
        $markers = $this->getLineMarkers($object->getTrans());
        }
        $origLines = explode("\n", $object->getTrans()->getWholetext());
        for ($i = 0; $i < count($origLines); $i++) {
            $line = $origLines[$i];
            // check if it is a line contains text, translation, or comment
            // for line mode, we only show the lines that contain all the trans phrases
            // for full text mode, we show all the lines that contain at least one trans phrase
            if (count($this->transPhrases) > 0 && preg_match("/^[0-9]+/", $line) &&
                (($this->searchMode == SEARCH::LINE_MODE && $this->testLineContainsAllTransPhrases($line)) ||
                 ($this->searchMode == SEARCH::FULLTEXT_MODE && $this->testLineContainsAtLeastOneTransPhrase($line)))) {
                $lineFound = "transliteration";
            } else if (count($this->translationPhrases) > 0 &&
                       $this->testLineContainsAtLeastOneTranslationPhrase($line)) {
                $lineFound = "translation";
            } else if (count($this->commentPhrases) > 0 &&
                       $this->testLineContainsAtLeastOneCommentPhrase($line)) {
                $lineFound = "comment";
            } else if (count($this->structurePhrases) > 0 &&
                       $this->testLineContainsAtLeastOneStructurePhrase($line)) {
                $lineFound = "structure";
            }else {
                continue;
            }

            $html .= '<tr align="left" valign="top">';
            $co=0;
            foreach ($this->fields as $field) {
                $co=$co+1;
               if ($co==1) {
                  $html .= '<td >';
                }
                else
                {
                  $html .= '<td width="140px">';

                }
                $html .= $this->getCatalogueHTMLByField($object, $field);
                $html .= '</td>';
            }

            if (count($this->transPhrases) > 0) {
                $html .= '<td>';
                if ($lineFound == "transliteration") {
                    $html .= htmlspecialchars($markers[$i]);
                    $html .= " ";
                    $html .= preg_replace("/(\\d+)\\./", "$1:", trim($this->getLineHTMLWithHighlight($line)));
                }
                $html .= '</td>';
            }

            if (count($this->translationPhrases) > 0) {
                $html .= '<td>';
                if ($lineFound == "translation") {
                    $new_tr_line = substr($line, 4);
                    $language = htmlspecialchars(substr($new_tr_line, 0, 3));
                    $translation_string = substr($this->getLineHTMLWithHighlight($line), 7);
                    $newline = "<span id='translation' style='padding-left:0;'><i id='translation'>" . $language . "</i>" . $translation_string . "</span>";
                    $html .= trim($newline);
                }
                $html .= '</td>';
            }

            if (count($this->commentPhrases) > 0) {
                $html .= '<td>';
                if ($lineFound == "comment") {
                    $newline = trim(substr($this->getLineHTMLWithHighlight($line), 1));
                    if (startsWith($line, "# ")) {
                        $newline = "<span id='markH' style='padding-left:0;'>" . $newline . "</span>";
                    } else {
                        $newline = "<span id='markS' style='padding-left:0;'>" . $newline . "</span>";
                    }
                    $html .= $newline;
                }
                $html .= '</td>';
            }
            if (count($this->structurePhrases) > 0) {
                $html .= '<td>';
                if ($lineFound == "structure") {
                    $newline = trim(substr($this->getLineHTMLWithHighlight($line), 1));
                    if (startsWith($line, "@")) {
                        $newline = "<span id='markH' style='padding-left:0;'>" . $newline . "</span>";
                    } else {
                        $newline = "<span id='markS' style='padding-left:0;'>" . $newline . "</span>";
                    }
                    $html .= $newline;
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
        }
        return $html;
    }

}