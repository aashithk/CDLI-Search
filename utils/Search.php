<?php
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Tools\Pagination\Paginator;

class Search
{
    const LINE_MODE = "LineMode";
    const FULLTEXT_MODE = "FullTextMode";


    /** @var array(TransPhrase) */
    private $transPhrases = array();
    private $translationPhrases = array();
    private $commentPhrases = array();
    private $structurePhrases = array();
    private $catalogue = array();
    private $limit = 1000;
    private $offset = 0;
    private $orderFiled = "designation";
    private $asc = true;
    private $caseSensitive = false;
    // the pid of all the object that match the query with regarding the pagination
    private $allPids = null;
    /** @var User * */
    private $user;

    /** @var \Doctrine\ORM\EntityManager * */
    private $em;

    private $mode;


    public function __sleep()
    {
        return array('transPhrases', 'translationPhrases', 'commentPhrases','structurePhrases',
                     'catalogue', 'limit', 'offset', 'orderFiled', 'asc',
                     'caseSensitive', 'mode');
    }

    public function setTransPhrases($trans)
    {
        $this->transPhrases = $trans;
        return $this;
    }

    public function setTranslationPhrases($translationPhrases)
    {
        $this->translationPhrases = $translationPhrases;
        return $this;
    }

    public function setCommentPhrases($commentPhrases)
    {
        $this->commentPhrases = $commentPhrases;
        return $this;
    }

    public function setStructurePhrases($structurePhrases)
    {
        $this->structurePhrases = $structurePhrases;
        return $this;
    }

    public function setCatalogue($catalog)
    {
        $nonEmptyCatalogue = array();
        foreach ($catalog as $key => $value) {
            if (array_key_exists($key, Search::$FIELD_MAPPINGS) && trim($value) != "") {
                $nonEmptyCatalogue[$key] = $value;
            }
        }
        $this->catalogue = $nonEmptyCatalogue;
        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    public function hasTextSearch()
    {
        return count($this->transPhrases) > 0;
    }

    public function hasTranslationSearch()
    {
        return count($this->translationPhrases) > 0;
    }

    public function hasCommentSearch()
    {
        return count($this->commentPhrases) > 0;
    }
    public function hasStructureSearch()
    {
        return count($this->structurePhrases) > 0;
    }
    public function getOffset()
    {

        return $this->offset;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getTransPhrases()
    {
        return $this->transPhrases;
    }

    public function setOrderBySearchFieldName($fieldName)
    {
        $this->orderFiled = Search::$FIELD_MAPPINGS[$fieldName][0];
        return $this;
    }

    public function setASC($asc)
    {
        $this->asc = $asc;
        return $this;
    }

    public function setCaseSensitive($sensitive)
    {
        $this->caseSensitive = $sensitive;
        return $this;
    }

    public function setEM($em)
    {
        $this->em = $em;
        return $this;
    }

    static public function createSearch($em, $user)
    {
        $search = new Search();
        $search->user = $user;
        $search->em = $em;
        $search->setMode(Search::FULLTEXT_MODE);
        return $search;
    }

    // $FIELD_MAPPINGS contains a mapping between (input parameter name, MySQL column name, display text)
    // input parameter is the string used to get data out of _GET
    // MySQL column is the string required to retrieve data from the MySQL server
    // display text is the string used to describe the data on the result page shown to users

    static public $FIELD_MAPPINGS = Array(
        'PrimaryPublication' => Array('primaryPublication', 'Primary publication'),
        'Author' => Array('author', 'Author(s)'),
        'PublicationDate' => Array('publicationDate', 'Publication date'),
        'SecondaryPublication' => Array('publicationHistory', 'Secondary publication(s)'),
        'Collection' => Array('collection', 'Collection'),
        'MuseumNumber' => Array('museumNo', 'Museum no.'),
        'AccessionNumber' => Array('accessionNo', 'Accession no.'),
        'Provenience' => Array('provenience', 'Provenience'),
        'ExcavationNumber' => Array('excavationNo', 'Excavation no.'),
        'Period' => Array('period', 'Period'),
        'DatesReferenced' => Array('datesReferenced', 'Dates referenced'),
        'ObjectType' => Array('objectType', 'Object type'),
        'ObjectRemarks' => Array('objectRemarks', 'Remarks'),
        'Material' => Array('material', 'Material'),
        'Language' => Array('language', 'Language'),
        'Genre' => Array('genre', 'Genre'),
        'SubGenre' => Array('subgenre', 'Sub-genre'),
        'CDLIComments' => Array('cdliComments', 'CDLI comments'),
        'CatalogueSource' => Array('dbSource', 'Catalogue source'),
        'ATFSource' => Array('atfSource', 'ATF source'),
        'TranslationSource' => Array('translationSource', 'Translation'),
        'ArcNumber' => Array('arkNumber', 'UCLA Library ARK'),
        'CompositeNumber' => Array('composite', 'Composite no.'),
        'SealID' => Array('sealId', 'Seal no.'),
        'ObjectID' => Array('objectId', 'CDLI no.')
    );

    // if it is a LINE MODE search,
    // we need to make sure there is at least one line that contains every search phrases
    public function performLineModeQuery(\Doctrine\ORM\QueryBuilder $qb)
    {
        $qb->select('partial o.{id, objectId}, partial trans.{objectId, text}');
        // get REGEX for each search phrase
        $regexs = $this->getRegexsForSearchPhrases();
        // a valid object must have a lest a line that contains all the search phrases
        $valid_objects = array();

        /** @var Object $object * */
        // for each object
        foreach ($qb->getQuery()->getResult() as $object) {
            getEM()->detach($object);
            if ($object->getTrans() == null)
                continue;
            getEM()->detach($object->getTrans());
            // for each line
            foreach (explode("\n", $object->getTrans()->getText()) as $line) {
                $valid_line = true;
                // check if the line contains all the search phrases
                foreach ($regexs as $seach_phrase_regex) {
                    $phpRegex = "/$seach_phrase_regex/" . ($this->caseSensitive ? "" : "i");
                    if (!preg_match($phpRegex, $line)) {
                        // this line fails to contain one of the phrases
                        $valid_line = false;
                        break;
                    }
                }
                // we've found a line that contains all the phrases
                if ($valid_line) {
                    // this object is then valid
                    $valid_objects[] = $object->getObjectId();
                    break;
                }
            }
        }
        if (count($valid_objects) == 0)
            return null;
        // only fetch those objects that are valid
        $qb = $this->em->createQueryBuilder();
        $qb->select('o', 'trans')
            ->from('Object', 'o')
            ->leftJoin('o.trans', 'trans')
            ->andWhere($qb->expr()->in("o.objectId", $valid_objects));

        return ($qb);
    }

    public function getResults()
    {
        $user = $this->user;
        // get a query builder
        $qb = $this->em->createQueryBuilder();
        $qb->select('o')
            ->from('Object', 'o')
            ->leftJoin('o.trans', 'trans');
        $parameters = array();
        // search catalogue content
        foreach ($this->catalogue as $field => $value) {
            // check if the given field is valid
            if (!array_key_exists($field, Search::$FIELD_MAPPINGS))
                continue;
            // get the corresponding field name
            $fieldName = Search::$FIELD_MAPPINGS[$field][0];
            if (preg_match('/^\/.*\/$/', $value)) {
                // if it is a regex search
                $regex = substr($value, 1, -1);
                $qb->andWhere("REGEXP ( o.$fieldName, ?" . count($parameters) . ", 'insensitive')= 1");
                $parameters[] = $regex;
            } else if ($field == 'ObjectID') {
                // special case for ObjectID
                $orX = $qb->expr()->orX();
                foreach (explode(',', $value) as $id_range) {
                    // strip the 'P' and spaces
                    $stripp=array("P", "p");
                    $id_range = trim(str_replace($stripp, "", trim($id_range)));
                    if (preg_match("/[^-\\d]/", $id_range)) {
                        // if it contains illegal search phrase
                        continue;
                    }
                    if (preg_match("/^(\\d{6})-(\\d{6})$/", $id_range, $match)) {
                        // this matches something of the form "P123- p123456"
                        $num1 = intval($match[1]);
                        $num2 = intval($match[2]);
                        $orX->add("o.objectId >= $num1 AND o.objectId<=$num2");
                    } else if (preg_match("/^\\d{6}$/", $id_range, $match)) {
                        // if the search phrase contains 6 digits
                        // search that number directly
                        $num = intval($match[0]);
                        $orX->add("o.objectId = $num");
                    } else if (preg_match("/^\\d{1,5}$/", $id_range, $match)) {
                        // if the search phrase contains less-than 6-digits number
                        // add 0's to the end the number and make it 6-digits
                        $digits = strlen($match[0]);
                        $base = pow(10, (6 - $digits));
                        $num1 = intval($match[0]) * $base;
                        $num2 = ((intval($match[0]) + 1) * $base) - 1;
                        $orX->add("o.objectId >= $num1 AND o.objectId<=$num2");
                    }
                }
                if ($orX->count() > 0) {
                    $qb->andWhere($orX);
                } else {
                    // if no valid search phrase is found, return 0 record
                    $qb->andWhere("1=0");
                }

            } // special case for "CompositeNumber" and "SealID"
            else if ($field == "CompositeNumber" || $field == "SealID") {
                $orX = $qb->expr()->orX();
                foreach (explode(',', $value) as $search_term) {
                    $search_term = trim($search_term);
                    $orX->add("o.$fieldName LIKE ?" . count($parameters));
                    $parameters[] = "%$search_term%";
                }

                $qb->andWhere($orX);
            } // special case for Provenience
            else {
                $qb->andWhere("o.$fieldName LIKE ?" . count($parameters));
                // perform LIKE
                $parameters[] = "%$value%";
            }
        }
        // filter the objects based on the user's permissions
        if (!$user->canViewPrivateCatalogues()) {
            $qb->andWhere("o.public = 'yes'");
        }

        // search transliterations
        $transPhrases = $this->transPhrases;
        if (count($transPhrases) > 0) {
            if (!$user->canViewPrivateTransliterations()) {
                $qb->andWhere("o.publicAtf = 'yes'");
            }
            foreach ($this->getLikePatternsForSearchPhrases() as $likePattern) {
                $qb->andWhere("trans.cleantext Like ?" . count($parameters));
                $parameters[] = $likePattern;
            }
            foreach ($this->getRegexsForSearchPhrases() as $regex) {
                $caseSensitivity = $this->caseSensitive ? "'sensitive'" : "'insensitive'";
                $qb->andWhere("REGEXP ( trans.text, ?" . count($parameters) . ", $caseSensitivity )= 1");
                $parameters[] = $regex;
            }
        }

        // search translations
        $translationPhrases = $this->translationPhrases;
        if (count($translationPhrases) > 0) {
            // transliteration privacy settings apply for translation search
            if (!$user->canViewPrivateTransliterations()) {
                $qb->andWhere("o.publicAtf = 'yes'");
            }
            foreach ($translationPhrases as $regex) {
                $qb->andWhere("REGEXP ( trans.wholetext, ?" . count($parameters) . ", 'insensitive' )= 1");
                $parameters[] = $regex;
            }
        }

        // search comments
        $commentPhrases = $this->commentPhrases;
        if (count($commentPhrases) > 0) {
            // transliteration privacy settings apply for comment search
            if (!$user->canViewPrivateTransliterations()) {
                $qb->andWhere("o.publicAtf = 'yes'");
            }

            foreach ($commentPhrases as $regex) {
                $qb->andWhere("REGEXP ( trans.wholetext, ?" . count($parameters) . ", 'insensitive' )= 1");
                $parameters[] = $regex;
            }
        }
        // search structures
        $structurePhrases = $this->structurePhrases;
        if (count($structurePhrases) > 0) {
            // transliteration privacy settings apply for comment search
            if (!$user->canViewPrivateTransliterations()) {
                $qb->andWhere("o.publicAtf = 'yes'");
            }

            foreach ($structurePhrases as $regex) {
                $qb->andWhere("REGEXP ( trans.wholetext, ?" . count($parameters) . ", 'insensitive' )= 1");
                $parameters[] = $regex;
            }
        }

        // add user specific filters
        foreach ($user->getAdditionalFilters() as $clause) {
            $op = $clause["operator"];
            $value = $clause["value"];
            $field = $clause["field"];
            $qb->andWhere("o.$field  $op ?" . count($parameters));
            $parameters[] = $value;
        }
        // populate the search parameters
        $qb = $qb->setParameters($parameters);

        // we need to perform another query for LINE mode search
        if ($this->mode == Search::LINE_MODE) {
            $qb = $this->performLineModeQuery($qb);
            // if $qb is null it means it found no match
            if ($qb == null) {
                // return empty array and 0 results
                return array();
            }
        }

        // get all the matched objectIDs regardless pagination pagination
        $qb->setMaxResults(null)
            ->setFirstResult(0);
        // select only objectId
        $qb->select('o.objectId')->orderBy("o." . $this->orderFiled, $this->asc ? "ASC" : "DESC");
        // get all the matched object ids
        $this->allPids = $qb->getQuery()->getScalarResult();
        $this->allPids = array_map('current', $this->allPids);
        if (isset($_GET["d"])) {
            // echo query if it is in debug mode
            echo $qb->getDQL();
            var_dump($parameters);
        }
        if (count($this->allPids) == 0) {
            return array();
        }
        // only get a subset of pids that is in the given range
        if ($this->limit) {
            $pagedPids = array_slice($this->allPids, $this->offset, $this->limit);
        } else {
            $pagedPids = array_slice($this->allPids, $this->offset);
        }
        // query db again for just the pids in the range
        $qb = getEM()->createQueryBuilder();
        $qb->select('o')
            ->from('Object', 'o')
            ->where($qb->expr()->in("o.objectId", $pagedPids)) // setup order
            ->orderBy("o." . $this->orderFiled, $this->asc ? "ASC" : "DESC");
        return $qb->getQuery()->getResult();
    }

    public function getAllPIds()
    {
        return $this->allPids;
    }

    public function getRegexsForSearchPhrases()
    {
        // get the corresponding regexes for each search phrase
        $regexs = array();
        /* @var TransPhrase $phrase */
        foreach ($this->transPhrases as $phrase) {
            $regex = $phrase->getRegex();
            // only match the phrases in the text (not in the comments)
            $regexs[] = $regex;
        }
        return $regexs;
    }

    public function getLikePatternsForSearchPhrases()
    {
        // get the corresponding regexes for each search phrase
        $patterns = array();
        /* @var TransPhrase $phrase */
        foreach ($this->transPhrases as $phrase) {
            $pattern = $phrase->getLikePattern();
            if ($pattern) {
                $patterns[] = $pattern;
            }
        }
        return $patterns;
    }

    static function getRegexsForTranslationPhrases($translationPhrases)
    {
        // process phrases
        if (trim(trim($translationPhrases), ",") == "") {
         $translationPhrases = array();
        } else {
         $translationPhrases = explode(
            ",", trim($translationPhrases, ","));
        }

        // get the corresponding regexes for each search phrase
        $regexs = array();

        // translation lines start with #tr.xx:
        $translationPrefixPattern = "[[.newline.]][[.#.]]tr\\.[[:alpha:]]{2}[[.colon.]] +";
        $translationPattern = "$translationPrefixPattern.*%s";
        foreach ($translationPhrases as $phrase) {
            // if the given search term is a regular expression,
            // use the given regex directly.
            if (preg_match('/^\/.*\/$/', $phrase)) {
                $regex = substr($phrase, 1, -1);
                // if regex checks starting pattern,
                // check the pattern right after prefix
                if ($regex[0] === '^') {
                    $regex = $translationPrefixPattern . substr($regex, 1);
                // else check the pattern anywhere after the prefix
                } else {
                    $regex = sprintf($translationPattern, $regex);
                }
            } else {
                $safe_phrase = preg_quote($phrase, '/');
                $regex = sprintf($translationPattern, $safe_phrase);
            }
            $regexs[] = "($regex)";
        }
        return $regexs;
    }

    static function getRegexsForCommentPhrases($commentPhrases)
    {
        // process phrases
        if (trim(trim($commentPhrases), ",") == "") {
         $commentPhrases = array();
        } else {
         $commentPhrases = explode(
            ",", trim($commentPhrases, ","));
        }

        // get the corresponding regexes for each search phrase
        $regexs = array();

        // comment lines start with # or $ and a space
       $commentPrefixPattern = "[[.newline.]]([[.#.]]|[[.$.]]) +";
        //regex updated to handle either $ or #. Previous one was resulting in searching random characters.
       // $commentPrefixPattern = "[.newline.][#$] +";
        $commentPattern = "$commentPrefixPattern.*%s";
        foreach ($commentPhrases as $phrase) {
            // if the given search term is a regular expression,
            // use the given regex directly.
            if (preg_match('/^\/.*\/$/', $phrase)) {
                $regex = substr($phrase, 1, -1);
                // if regex checks starting pattern,
                // check the pattern right after prefix
                if ($regex[0] === '^') {
                    $regex = $commentPrefixPattern . substr($regex, 1);
                // else check the pattern anywhere after the prefix
                } else {
                    $regex = sprintf($commentPattern, $regex);
                }
            } else {
                $safe_phrase = preg_quote($phrase, '/');
                $regex = sprintf($commentPattern, $safe_phrase);
            }
            $regexs[] = "($regex)";
        }
        return $regexs;
    }
    static function getRegexsForStructurePhrases($structurePhrases)
    {
        // process phrases
        if (trim(trim($structurePhrases), ",") == "") {
         $structurePhrases = array();
        } else {
         $structurePhrases = explode(
            ",", trim($structurePhrases, ","));
        }

        // get the corresponding regexes for each search phrase
        $regexs = array();

        // comment lines start with # or $ and a space
       $structurePrefixPattern = "[[.newline.]]([[.#.]]|[[.$.]]) +";
        //regex updated to handle either $ or #. Previous one was resulting in searching random characters.
       // $commentPrefixPattern = "[.newline.][#$] +";
        $structurePattern = "$structurePrefixPattern.*%s";

        if(is_array($structurePhrases))
        {


        foreach ($structurePhrases as $phrase) {
            // if the given search term is a regular expression,
            // use the given regex directly.
            if (preg_match('/^\/.*\/$/', $phrase)) {
                $regex = substr($phrase, 1, -1);
                // if regex checks starting pattern,
                // check the pattern right after prefix
                if ($regex[0] === '^') {
                    $regex = $structurePrefixPattern . substr($regex, 1);
                // else check the pattern anywhere after the prefix
                } else {
                    $regex = sprintf($structurePattern, $regex);
                }
            } else {
                $safe_phrase = preg_quote($phrase, '/');
                $regex = sprintf($structurePattern, $safe_phrase);
            }
            $regexs[] = "($regex)";
        }

    }
    else
    {
            $phrase=$structurePhrases;
         // if the given search term is a regular expression,
            // use the given regex directly.
            if (preg_match('/^\/.*\/$/', $phrase)) {
                $regex = substr($phrase, 1, -1);
                // if regex checks starting pattern,
                // check the pattern right after prefix
                if ($regex[0] === '^') {
                    $regex = $structurePrefixPattern . substr($regex, 1);
                // else check the pattern anywhere after the prefix
                } else {
                    $regex = sprintf($structurePattern, $regex);
                }
            } else {
                $safe_phrase = preg_quote($phrase, '/');
                $regex = sprintf($structurePattern, $safe_phrase);
            }
            $regexs[] = "($regex)";
    }

        return $regexs;
    }
}