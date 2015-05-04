<?php
/*
 * This is the core function library of the OLD version of CDLI.
 * It is not being used anymore except by archival_view.php.
*/
define("P_NUMBER_LENGTH", 6);

if (isset($_SESSION['authenticated'])) { //authenticated constants
    define("REVISION_COUNT", -1);
} else { //unauthenticated versions of the same constants
    define("REVISION_COUNT", 5);
}

// search_fields contains a mapping between (input parameter name, MySQL column name, display text)
// input parameter is the string used to get data out of _GET
// MySQL column is the string required to retrieve data from the MySQL server
// display text is the string used to describe the data on the result page shown to users

$search_fields = Array(
    'PrimaryPublication' => Array('primary_publication', 'Primary publication'),
    'Author' => Array('author', 'Author(s)'),
    'PublicationDate' => Array('publication_date', 'Publication date'),
    'SecondaryPublication' => Array('publication_history', 'Secondary publication(s)'),
    'Collection' => Array('collection', 'Collection'),
    'MuseumNumber' => Array('museum_no', 'Museum no.'),
    'AccessionNumber' => Array('accession_no', 'Accession no.'),
    'Provenience' => Array('provenience', 'Provenience'),
    'ExcavationNumber' => Array('excavation_no', 'Excavation no.'),
    'Period' => Array('period', 'Period'),
    'DatesReferenced' => Array('dates_referenced', 'Dates referenced'),
    'ObjectType' => Array('object_type', 'Object type'),
    'ObjectRemarks' => Array('object_remarks', 'Remarks'),
    'Material' => Array('material', 'Material'),
    'Language' => Array('language', 'Language'),
    'Genre' => Array('genre', 'Genre'),
    'SubGenre' => Array('subgenre', 'Sub-genre'),
    'CompositeNumber' => Array('composite', 'Composite number'),
    'CDLIComments' => Array('cdli_comments', 'CDLI comments'),
    'CatalogueSource' => Array('db_source', 'Catalogue source'),
    'ATFSource' => Array('atf_source', 'ATF source'),
    'TranslationSource' => Array('translation_source', 'Translation'),
    'ArcNumber' => Array('ark_number', 'UCLA Library ARK'),
    'SealID' => Array('seal_id', 'Seal no.'),
    'ObjectID' => Array('object_id', 'CDLI no.')
);

define("SQL_COLUMN", 0);
define("DISPLAY_TEXT", 1);

// maps the headings that can show up in transliterated text with the html code they should be replaced with
$translation_headings = Array(
    Array('/@tablet.*?<br>/', ''),
    Array('/@fragment.*?<br>/', ''),
    Array('/@object.*?<br>/', ''),
    Array('/@bulla.*?<br>/', ''),
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

// Function to open connection to MySQL server.
function mysql_init()
{
    $dbname = "cdlidb";
    $dbhost = "db.cdli.ucla.edu";
    $dsn = "mysql:dbname=$dbname;host=$dbhost";
    $user = "cdliuser";
    $password = "chA@efeb";
    try {
        $dbh = new PDO($dsn, $user, $password);
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    }
    //makes sure that results from mysql are returned in utf8
    $set_UTF8_query = "SET NAMES 'utf8'";
    $dbh->query($set_UTF8_query);
    return $dbh;
}


// Function to get all possible image paths for a specific object.
// $file_base is expected in the form of P000000
// Each element returned contains (displayable name of file, location of file,
// whether it should be a thumbnail or not).
function get_image_paths($file_base)
{
    $image_names = Array();

    if (file_exists("/Library/WebServer/Documents/cdli/dl/photo/$file_base" . ".jpg")) {
        $image_names[] = Array("photo", "photo/$file_base" . ".jpg", true);
    }
    if (file_exists("/Library/WebServer/Documents/cdli/dl/photo/$file_base" . "_e.jpg")) {
        $image_names[] = Array("envelope image", "photo/$file_base" . "_e.jpg", true);
    }
    if (file_exists("/Library/WebServer/Documents/cdli/dl/lineart/$file_base" . "_l.jpg")) {
        $image_names[] = Array("line art", "lineart/$file_base" . "_l.jpg", true);
    }
    if (file_exists("/Library/WebServer/Documents/cdli/dl/photo/$file_base" . "_d.jpg")) {
        $image_names[] = Array("detail image", "photo/$file_base" . "_d.jpg", true);
    }
    if (file_exists("/Library/WebServer/Documents/cdli/dl/photo/$file_base" . "_ed.jpg")) {
        $image_names[] = Array("detail envelope image", "photo/$file_base" . "_ed.jpg", true);
    }
    if (file_exists("/Library/WebServer/Documents/cdli/dl/lineart/$file_base" . "_ld.jpg")) {
        $image_names[] = Array("detail line art", "lineart/$file_base" . "_ld.jpg", true);
    }
    if (file_exists("/Library/WebServer/Documents/cdli/dl/photo/$file_base" . "_s.jpg")) {
        $image_names[] = Array("seal image", "photo/$file_base" . "_s.jpg", true);
    }
    if (file_exists("/Library/WebServer/Documents/cdli/dl/lineart/$file_base" . "_ls.jpg")) {
        $image_names[] = Array("seal line art", "lineart/$file_base" . "_ls.jpg", true);
    }
    if (file_exists("/Library/WebServer/Documents/cdli/dl/pdf/$file_base" . ".pdf")) {
        $image_names[] = Array("commentary", "pdf/$file_base" . ".pdf", false);
    }

    return $image_names;
}

function nukeMagicQuotes()
{
    if (get_magic_quotes_gpc()) {
        function stripslashes_deep($value)
        {
            $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
            return $value;
        }

        $_POST = array_map('stripslashes_deep', $_POST);
        $_GET = array_map('stripslashes_deep', $_GET);
        $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    }
}

function pc_permute($items, $perms = array())
{
    if (empty($items)) {
        $return = array($perms);
    } else {
        $return = array();
        for ($i = count($items) - 1; $i >= 0; --$i) {
            $newitems = $items;
            $newperms = $perms;
            list($foo) = array_splice($newitems, $i, 1);
            array_unshift($newperms, $foo);
            $return = array_merge($return, pc_permute($newitems, $newperms));
        }
    }
    return $return;
}

?>
