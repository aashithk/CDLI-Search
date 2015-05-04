<?php
/**
 * Created by PhpStorm.
 * User: changun
 * Date: 2/9/14
 * Time: 2:44 PM
 */

class TransPhrase
{
    private $phrase;

    function __construct($phrase)
    {
        $this->phrase = $phrase;
    }

    static function createTransPhrasesFromText($text)
    {
        $phrases = array();
        if (trim(trim($text), ",") == "")
            return $phrases;
        foreach (explode(",", trim($text, ",")) as $phrase) {
            $phrases[] = new TransPhrase($phrase);
        }
        return $phrases;
    }

    private $regexCache = null;

    public function getRegex()
    {
        if ($this->regexCache != null) {
            return $this->regexCache;
        }
        $search_phrase = $this->phrase;


        // if the given search term is a regular expression, use the given regex directly.
        if (preg_match('/^\/.*\/$/', $search_phrase)) {
            // if the given search term is a regular expression, use the given regex directly.
            $regex = substr($search_phrase, 1, -1);
            $regex = "()()($regex)";
            return ($regex);
        }
        /* Generate a regular expression for this search phrase.
         * The search phrase might be 'ama-su', but we also want to highlight variations, like 'ama#?-su',
         * that allows additional symbols to appear between the tokens: "ama" and "-"
         * To achieve this using regular expression, we adopts the following step to generate regex patterns
         * 1. Find all the tokens in a trans phrase, where a token is either a series of non-symbol characters (i.e. a word)
         * (e.g. "ama") or a symbol character (e.g. "-")
         * 2. Then insert regex pattern ([^[:alnum:]\n'\\.]) between any two tokens to allow symbols to appear between two token.
         * 3. if the first token is a word, it must be at the beginning of the line or prepended by a symbol
         * 4. if the last token is a word, it must be at the end of the line or followed by a symbol
         * 5. match any instance of "-" in search phrase with "-" or ":"
         */

        // 1. get every token
        preg_match_all("/(_?\\p{L}+\\d*)|([^\\p{L}])/", $search_phrase, $tokens, PREG_PATTERN_ORDER);
        $tokens = $tokens[0];
        foreach ($tokens as $index => $token) {
            // for each token, create the quoted regex
            $tokens[$index] = preg_quote($tokens[$index], '/');
        }

        // 2. insert non_word_match between each token
        $non_word_match = "(([^[:alnum:]\n'\\.]))";
        $regex = "(" . implode($non_word_match . "*", $tokens) . ")";

        // 3. if the first token is a word, it must be at the beginning of the line or prepended by a symbol
        if (preg_match("/(_?\\p{L}+\\d*)/", $tokens[0])) {
            $regex = $non_word_match . $regex;
        } else {
            // add 2 parenthesis so that the matched phrase will in the same regex group
            $regex = "(())" . $regex;
        }
        // 4. if the last token is a word, it must be at the end of the line or followed by a symbol
        if (preg_match("/(_?\\p{L}+\\d*)/", end($tokens))) {
            $regex = $regex . "(" . $non_word_match . "|\n|$)";
        } else {
            // add 3 parenthesis so that the matched phrase will in the same regex group
            $regex = $regex . "((()))";
        }

        // 5. match any instance of "-" in search phrase with "-" or ":"
        $regex = str_replace("\\-", "(\\-|\\:)", $regex);


        $this->regexCache = $regex;
        return $regex;
    }


    public function getLikePattern()
    {
        $search_phrase = $this->phrase;

        // if search phrase is regex, do not use like pattern
        if (preg_match('/^\/.*\/$/', $search_phrase)) {
            return "";
        }

        // replace one or multiple consecutive symbol to be one space, so that it comply with the format of clean_text
        $pattern = "/[^\\p{L}\\d]+/";
        $replacement = " ";
        $clean_search_phrase = preg_replace($pattern, $replacement, $search_phrase);

        $letterOrNumber = "/[[:alnum:]]/";
        if (preg_match($letterOrNumber, substr($clean_search_phrase, 0, 1))) {
            $clean_search_phrase = " " . $clean_search_phrase;

        }
        if (preg_match($letterOrNumber, substr($clean_search_phrase, -1))) {
            $clean_search_phrase = $clean_search_phrase . " ";
        }
        return "%$clean_search_phrase%";
    }

}