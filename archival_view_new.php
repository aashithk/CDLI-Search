<?php
/*this page assumes to always be called with an object ID. Bad things otherwise*/
header('Content-type: text/html; charset=UTF-8');
ini_set('display_errors', '1');
mb_internal_encoding('UTF-8');
include 'corefuncs.php';

// Open connection to MySQL server.
$mysql_connection = mysql_init();

// Build the query
$raw_obj_id = $_GET['ObjectID'];
$raw_obj_id = ltrim($raw_obj_id, "pP0");
$formattedID = "P" . str_pad($raw_obj_id, P_NUMBER_LENGTH, "0", STR_PAD_LEFT);
 $_GET['ObjectID']=$formattedID;

$obj_id = ltrim($formattedID, "P0");
$query = 'SELECT * FROM cataloguesnew WHERE id_text = ' . $obj_id . ';';
// Query the MySQL server.
$results = $mysql_connection->query($query);

// Store the results.
$data = $results->fetch(PDO::FETCH_ASSOC);


?>
<?php
require_once "bootstrap.php";

const TABULAR_DISPLAY_MODE = "Line";
const FULL_DISPLAY_MODE = 'Text';
/*
 * Start query performance profile if query parameter d is set.
 *
 */
if (isset($_REQUEST["d"]) && extension_loaded('xhprof')) {
    include_once '/usr/lib/php/xhprof_lib/utils/xhprof_lib.php';
    include_once '/usr/lib/php/xhprof_lib/utils/xhprof_runs.php';
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

header('Content-type: text/html; charset=utf-8');

ini_set('display_errors', 1);
ini_set('memory_limit', '536870912');
mb_internal_encoding('utf-8');
error_reporting(E_ALL);
//---CONSTANTS------------------------------------------------------------------------------------------------------//
// the fields and order to be used when displaying info in line mode
// and the column width
$line_fields = Array(
    'ObjectID' => "60px",
    'PrimaryPublication' => "100px",
    'MuseumNumber' => "80px",
    'Period' => "100px",
    'DatesReferenced' => "110px",
    'Provenience' => "80px",
    'Genre' => "100px"
);
define("DISPLAY_COUNT", 2000);

//generate current url without offset number
function gen_url()
{
    $url = $_SERVER['REQUEST_URI'];
    $url = preg_replace('/&offset=[0-9]+/', "", $url);
    return $url;
}



// generate html for the objects that are just being updated by the user last operation
function generate_updatedIds()
{
    $html = "";
    if ($_SESSION['updatedIDs'] != "") {
        $html .= "Updated entries : ";
        $html .= $_SESSION['updatedIDs'];
        $html .= "<br>";
    }
    if ($_SESSION['nonUpdatedIDs'] != "") {
        $html .= "Non-updated entries : ";
        $html .= $_SESSION['nonUpdatedIDs'];
        $html .= "<br>";
    }
    if ($_SESSION['lockedIDs'] != "") {
        $html .= "Entries locked by other users : ";
        $html .= $_SESSION['lockedIDs'];
        $html .= "<br>";
    }
    unset($_SESSION['updatedIDs']);
    unset($_SESSION['nonUpdatedIDs']);
    unset($_SESSION['lockedIDs']);
    return $html;
}

//---BUILD THE QUERIES--//
/* @var User $user */
$user = User::getCurrentUserOrLoginAsGuest(getEM());
$offset = isset($_GET["offset"]) ? intval($_GET["offset"]) : 0;

// LINE mode search require the text to contain a least one line that contains all the search phrases
// FULLTEXT mode only require the text to contains all the search phrases
$search_mode = isset($_GET['singleLine']) && $_GET["singleLine"] == "true" ? Search::LINE_MODE : Search::FULLTEXT_MODE;

// Transliteration search phrases
$transPhrases = TransPhrase::createTransPhrasesFromText(isset($_GET["TextSearch"]) ? $_GET["TextSearch"] : "");

// Case sensitive search or not
$caseSensitivity = isset($_GET['caseSensitive']) && $_GET['caseSensitive'] == 'true';

// Translation search phrases
$translationPhrases = Search::getRegexsForTranslationPhrases(isset($_GET["TranslationSearch"]) ? $_GET["TranslationSearch"] : "");

// Comment search phrases
$commentPhrases = Search::getRegexsForCommentPhrases(isset($_GET["CommentSearch"]) ? $_GET["CommentSearch"] : "");

//structure search phrases
$structurePhrases = Search::getRegexsForStructurePhrases(isset($_GET["StructureSearch"]) ? $_GET["StructureSearch"] : "");


// diplay the search result in Full display mode (with all the catalog data and full transliteration)
// or Tabular mode (only with selective catalog data and matched transliteration lines)
$display_mode = isset($_GET['SearchMode']) && $_GET["SearchMode"] == "Line" ? TABULAR_DISPLAY_MODE : FULL_DISPLAY_MODE;

$search = Search::createSearch(getEM(), User::getCurrentUserOrLoginAsGuest(getEM()))
    ->setCatalogue($_GET)
    ->setTransPhrases($transPhrases)
    ->setTranslationPhrases($translationPhrases)
    ->setCommentPhrases($commentPhrases)
    ->setStructurePhrases($structurePhrases)
    ->setMode($search_mode)
    ->setLimit(DISPLAY_COUNT)
    ->setOffset($offset)
    ->setUser($user)
    ->setCaseSensitive($caseSensitivity);
if (isset($_GET["order"])) {
    $search->setOrderBySearchFieldName($_GET["order"]);
}
// keep the search object in the session so other page can use it
$_SESSION["search"] = serialize($search);

// get query result
$results = $search->getResults(Doctrine\ORM\Query::HYDRATE_ARRAY);

// total number of records founds
$total_results = count($search->getAllPIds());



//---We have received the query results. Render the objects in the desired format--//

// the page this search originated from
$requestFrom = "";
if (isset($_GET['requestFrom'])) {
    $requestFrom = $_GET['requestFrom'];
}
/*
if ($total_results == 0) {
    echo 'No results found.';
}
*/
// create a object renderer for the desired search mode and display mode

$objectHTMLRenderer = null;
if ($display_mode == TABULAR_DISPLAY_MODE) { //  tabular mode
    if ($search->hasTextSearch() || $search->hasTranslationSearch() || $search->hasCommentSearch()) {
        // for tabular mode with transliteration search
        $objectHTMLRenderer = new TabularObjectRenderer(array_keys($line_fields),
            $transPhrases,
            $translationPhrases,
            $commentPhrases,
            $search_mode,
            $caseSensitivity);

    } else {
        // for tabular mode with only catalog search
        $objectHTMLRenderer = new SimpleObjectRenderer(array_keys($line_fields),
            $transPhrases,
            $translationPhrases,
            $commentPhrases,
            $search_mode,
            $caseSensitivity);
    }
} else { // full text mode
    $objectHTMLRenderer = new FullObjectRenderer(array_keys(Search::$FIELD_MAPPINGS),
        $transPhrases,
        $translationPhrases,
        $commentPhrases,
        $structurePhrases,
        $search_mode,
        $caseSensitivity, $user);
}

?>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
        <title>CDLI-Archival View</title>        
        <link rel="stylesheet" type="text/css;charset=utf-8" href="cdli.css"/>
        <link rel="stylesheet" type="text/css" href="cdlisearch.css"/>
        <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
        <?php include_once("analyticstracking.php") ?>
    </head>
    <body>
    <!-- HEADER PART -->
    <hr align="left" size="2" width="1200"/>
    <table width="1200" border="0" cellspacing="0" cellpadding="1" class="header" style="font-size: 12px;table-layout: fixed; ">
        <tr>
            <!-- left logo -->
            <td align="left">
                <a href="/">
                    <img alt="" width="81" height="51" border="0" src="cdli_logo.gif"/>
                </a>
            </td>

            <!-- title in the middle -->
            <td rowspan="2" width="700" align="center" valign="middle">
                <h1>Archival view of <?php echo $formattedID; ?> 
                </h1>
                <br/>
               

                <br/>
                
                
            </td>

            <!-- right part -->
            <td valign="middle" align="right" width="250">
                <a href="search.php">
                    <?php echo $requestFrom == 'Quicksearch' ? 'Go to Full Search' : 'Return to Search Page'; ?>
                </a>
                <br/>
                <a target="_blank" href="http://cdli.ucla.edu/?q=cdli-search-information" link="#0099FF" alink="#0099FF"
                   vlink="#0099FF">
                    Search aids
                </a>
                <br/>
                <a target="_blank" href="http://cdli.ucla.edu/?q=terms-of-use" link="#0099FF" alink="#0099FF"
                   vlink="#0099FF">
                    Terms of Use
                </a>
                <br/>

                <?php if ($user->getUsername() == "guest") { ?>
                    <a href="login.php" link="#0099FF" alink="#0099FF" vlink="#0099FF" target="_blank">
                        Internal login
                    </a>
                <?php } else { ?>
                    <a href="accountManagement.php" link="#0099FF" alink="#0099FF" vlink="#0099FF">
                        Manage your account
                    </a>
                    <br/>
                    Logged in as <font color="Gray"><?php
                        echo $user->getUsername();
                        ?></font>
                    &nbsp;&nbsp;<br/>
                    <a href="logout.php" link="#0099FF" alink="#0099FF" vlink="#0099FF">
                        Log out
                    </a>
                <?php } ?>
            </td>
        </tr>
    </table>

    <hr align="left" size="2" width="1200"/>

    <style>
        table.header {
            border-collapse: separate;
            border-spacing: 10px 0px;
        }
    </style>
    

    <table class="header" style="font-size: 12px">
        <tbody>
        <tr>
            
            <?php
            // if the user is a ATF editor, show Lock link
            if ($user->canEditTranliterations()) {
                echo '<td><a id="lock" href="#">Lock transliterations</a></td>';
            }

            // if the user has permission to download HD images
            if ($user->canDownloadHdImages()) {
                echo '<td><a href="download_archival_images.php">Download all archival tif images</a></td>';
            }
            ?>
            <?php
            echo '<td>';
           
                $other_mode_query = http_build_query(array_merge($_GET, array(
                    'SearchMode' => TABULAR_DISPLAY_MODE,
                    'offset' => 0
                )));
                echo '<a href="search_results.php?' . $other_mode_query . '">Reduce to catalogue data</a>';

            echo '</td>';
            ?>
            
        </tr>
        </tbody>
    </table>

    <?php
    //-- check if the updatedIDs are set --//
    if (isset($_SESSION['updatedIDs']) || isset($_SESSION['nonUpdatedIDs']) || isset($_SESSION['lockedIDs'])) {
        echo generate_updatedIds();
    }
    // display the search results

// use the object renderer to generate html for each matched object
$result_html = "";
/* @var Object $object */
foreach ($results as $object) {














    $result_html .= $objectHTMLRenderer->getHTMLForObject($object);
    $showTransliteration = $user->canViewPrivateTransliterations() || $object->isPublicAtf();
   
    ?>
<!-- DATA PART -->
</a>
<br>
<br>
<table>
<tr id="row1" width="600">
    <td width="450" class="row section" valign="top">
        <table style="font-size: 11px;" width="388" valign="top">
            <tr>
                <th colspan="2" align="left">Publication</th>
            </tr>
            <tr>
                <td width="200" valign="top">Primary publication:</td>
                <td><?php echo $data['primary_publication']; ?></td>
            </tr>
            <tr>
                <td valign="top">Author:</td>
                <td class="value"><?php echo $data['author']; ?></td>
            </tr>
            <tr>
                <td valign="top">Publication date:</td>
                <td class="value"><?php echo $data['publication_date']; ?></td>
            </tr>
            <tr>
                <td valign="top">Secondary publication(s):</td>
                <td class="value"><?php echo $data['publication_history']; ?></td>
            </tr>
            <tr>
                <td valign="top">Citation:</td>
                <td class="value"><?php echo $data['citation']; ?></td>
            </tr>
            <tr>
                <td valign="top">Author remarks:</td>
                <td class="value"><?php echo $data['author_remarks']; ?></td>
            </tr>
            <tr>
                <td valign="top">Published collation:</td>
                <td class="value"><?php echo $data['published_collation']; ?></td>
            </tr>
            <tr>
                <td valign="top">CDLI no.:</td>
                <td class="value"><?php echo $formattedID; ?></td>
            </tr>
            <tr>
                <td valign="top">UCLA Library ARK</td>
                <td class="ark">
                    <?php echo $data['ark_number']; ?></FONT>
                </td>
            </tr>
            <tr>
                <td valign="top">CDLI comments:</td>
                <td class="value"><?php echo $data['cdli_comments']; ?></td>
            </tr>
        </table>
    </TD>

    <td class="row section" valign="top">
        <table style="font-size: 11px;" width="400">
            <tr>
                <th colspan="2" align="left">Source of original electronic files</th>
            </tr>
            <tr>
                <td width="100" valign="top">Catalogue:</td>
                <td width="400" class="value"><?php echo $data['db_source']; ?></td>
            </tr>
            <tr>
                <td valign="top">Transliteration:</td>
                <td width="400" class="value"><?php echo $data['atf_source']; ?></td>
            </tr>
            <tr>
                <td valign="top">Translation:</td>
                <td width="400" class="value"><?php echo $data['translation_source']; ?></td>
            </tr>
            <tr>
                <td valign="top">Photo:</td>
                <td width="400" class="value">
                    If not otherwise indicated, digital images were prepared in their current form by
                    CDLI staff, in some cases with the kind assistance of collection staff. For terms of us,
                    <a target="_blank" href="http://cdli.ucla.edu/?q=terms-of-use">click here</a>.
                    <BR><BR>
                </td>
            </tr>
            <tr>
                <td valign="top">Line Art:</td>
                <td width="400" class="value">
                    If not otherwise indicated, line art drawings prepared in their digital
                    form by CDLI staff are to be credited to primary publication author(s).
                    <BR><BR>
                </td>
            </tr>
        </table>
    </td>
</tr>

<tr id="row2" width="600">
    <td width="450" class="row section" valign="top">
        <table style="font-size: 11px;" width="388">
            <tr>
                <th colspan="2" align="left">Collection Information</th>
            </tr>
            <tr>
                <td width="200" valign="top">Owner:</td>
                <td class="value"><?php echo $data['collection']; ?></td>
            </tr>
            <tr>
                <td valign="top">Museum no.:</td>
                <td class="value"><?php echo $data['museum_no']; ?></td>
            </tr>
            <tr>
                <td valign="top">Accession no.:</td>
                <td class="value"><?php echo $data['accession_no']; ?></td>
            </tr>
            <tr>
                <td valign="top">Acquisition history:</td>
                <td class="value"><?php echo $data['acquisition_history']; ?></td>
            </tr>
            <TR rowspan="2">
                <TD colspan="2"><BR></TD>
            </TR>
            <tr>
                <th colspan="2" align="left">Text Content:</th>
            </tr>
            <tr>
                <td width="200" valign="top">Genre:</td>
                <td class="value"><?php echo $data['genre']; ?></td>
            </tr>
            <tr>
                <td valign="top">Sub-genre:</td>
                <td class="value"><?php echo $data['subgenre']; ?></td>
            </tr>
            <tr>
                <td valign="top">Sub-genre remarks:</td>
                <td class="value"><?php echo $data['subgenre_remarks']; ?></td>
            </tr>
            <tr>
                <td valign="top">Composite no.:</td>
                <td class="value"><?php echo $data['composite']; ?></td>
            </tr>
            <tr>
                <td valign="top">Language:</td>
                <td class="value"><?php echo $data['language']; ?></td>
            </tr>
        </table>
    </td>

    <td class="row section" valign="top">
        <table style="font-size: 11px;" width="400">
            <tr>
                <th colspan="2" align="left">Physical Information</th>
            </tr>
            <tr>
                <td width="200" valign="top">Object type:</td>
                <td class="value"><?php echo $data['object_type']; ?></td>
            </tr>
            <tr>
                <td valign="top">Material:</td>
                <td class="value"><?php echo $data['material']; ?></td>
            </tr>
            <tr>
                <td valign="top">Object remarks:</td>
                <td class="value"><?php echo $data['object_remarks']; ?></td>
            </tr>
            <tr>
                <td valign="top">Measurements (mm):</td>
                <td class="value"><?php echo $data['height'] . ' x ' . $data['width'] . ' x ' . $data['thickness']; ?></td>
            </tr>
            <tr>
                <td valign="top">Object preservation:</td>
                <td class="value"><?php echo $data['object_preservation']; ?></td>
            </tr>
            <tr>
                <td valign="top">Surface preservation:</td>
                <td class="value"><?php echo $data['surface_preservation']; ?></td>
            </tr>
            <tr>
                <td valign="top">Condition description:</td>
                <td class="value"><?php echo $data['condition_description']; ?></td>
            </tr>
            <tr>
                <td valign="top">Join information:</td>
                <td class="value"><?php echo $data['join_information']; ?></td>
            </tr>
            <tr>
                <td valign="top">Seal no.:</td>
                <td class="value">
                    <A target="_blank"
                       HREF="search_results.php?SearchMode=Text&order=ObjectType&SealID=<?php echo $data['seal_id']; ?>&">
                        <FONT COLOR="#0099FF"><?php echo $data['seal_id']; ?></FONT></A></td>
                </td>
            </tr>
            <tr>
                <td valign="top">Seal information:</td>
                <td class="value"><?php echo $data['seal_information']; ?></td>
            </tr>
        </table>
    </td>
</tr>

<tr id="row3" width="600">
    <td width="450" class="row section" valign="top">
        <table style="font-size: 11px;" width="388">
            <tr>
                <th colspan="2" align="left">Provenience:</th>
            </tr>
            <tr>
                <td width="200" valign="top">Provenience:</td>
                <td class="value"><?php echo $data['provenience']; ?></td>
            </tr>
            <tr>
                <td valign="top">Provenience remarks:</td>
                <td class="value"><?php echo $data['provenience_remarks']; ?></td>
            </tr>
            <tr>
                <td valign="top">Excavation no.:</td>
                <td class="value"><?php echo $data['excavation_no']; ?></td>
            </tr>
            <tr>
                <td valign="top">Findspot square:</td>
                <td class="value"><?php echo $data['findspot_square']; ?></td>
            </tr>
            <tr>
                <td valign="top">Elevation:</td>
                <td class="value"><?php echo $data['elevation']; ?></td>
            </tr>
            <tr>
                <td valign="top">Stratigraphic level:</td>
                <td class="value"><?php echo $data['stratigraphic_level']; ?></td>
            </tr>
            <tr>
                <td valign="top">Findspot remarks:</td>
                <td class="value"><?php echo $data['findspot_remarks']; ?></td>
            </tr>
        </table>
    </td>

    <td class="row section" valign="top">
        <table style="font-size: 11px;" width="388">
            <tr>
                <th colspan="2" align="left">Chronology:</th>
            </tr>
            <tr>
                <td width="200"
                450="top">Period:</td>
                <td class="value"><?php echo $data['period']; ?></td>
            </tr>
            <tr>
                <td valign="top">Period remarks:</td>
                <td class="value"><?php echo $data['period_remarks']; ?></td>
            </tr>
            <tr>
                <td valign="top">Date of Origin:</td>
                <td class="value"><?php echo $data['date_of_origin']; ?></td>
            </tr>
            <tr>
                <td valign="top">Dates referenced:</td>
                <td class="value"><?php echo $data['dates_referenced']; ?></td>
            </tr>
            <tr>
                <td valign="top">Date remarks:</td>
                <td class="value"><?php echo $data['date_remarks']; ?></td>
            </tr>
            <tr>
                <td valign="top">Alternative years:</td>
                <td class="value"><?php echo $data['alternative_years']; ?></td>
            </tr>
            <tr>
                <td valign="top">Accounting period:</td>
                <td class="value"><?php echo $data['accounting_period']; ?></td>
            </tr>
        </table>
    </td>
</tr>
</table>

<BR><BR>
<a target="_blank" href="http://cdli.ox.ac.uk/wiki/abbreviations_for_assyriology">
    <FONT COLOR="#0099FF">Unclear abbreviations?</FONT></a>

Can you improve upon the content of this page? <a
    href="http://cdli.ucla.edu/?q=cdli-corrections-and-additions-help-page"><font color="#0099FF">Please contact
        us!</font></a>
<HR align="left" size="2" width="1200"/>
<!-- IMAGE PART -->
<br>
<?php

//Display Images

$showImage = $user->canViewPrivateImages() || $object->isPublicImages();


if($showImage)
{
$image_names = get_image_paths($formattedID);
if (sizeof($image_names) > 0) {
    echo '<table border="0" cellspacing="10" cellpadding="10">';
    for ($i = 0; $i < sizeof($image_names); $i++) {
        echo '<tr><td align="middle">';
        if ($image_names[$i][2]) {
            echo '<a href="/dl/' . $image_names[$i][1] . '"><img src="/dl/tn_' . $image_names[$i][1] . '"></a>';
        } else {
            echo '<a href="/dl/' . $image_names[$i][1] . '">View ' . $image_names[$i][0] . '</a>';
        }
        echo '</td></tr>';
    }
    echo '</table>';
}

}
?>

<!-- TRANSLITERATION PART -->
<?php

$showTransliteration = $user->canViewPrivateTransliterations() || $object->isPublicAtf();
if ($showTransliteration) {

$textQuery = "SELECT wholetext, object_type, object_remarks from cataloguesnew c
                left join fulltrans f on f.object_id = c.id_text where id_text=$raw_obj_id";
$textQueryResult = $mysql_connection->query($textQuery);
$row = $textQueryResult->fetch(PDO::FETCH_ASSOC);
$transliteration = htmlspecialchars($row['wholetext']);
// replace all new lines with html new lines
$transliteration = nl2br($transliteration, false);
// get rid of all the junk at the beginning of the translation
$start_index = strpos($transliteration, '@');
$transliteration = substr($transliteration, $start_index, -1);
// replace all the @somethings with the way they should be displayed
foreach ($translation_headings as $translation_heading) {
    $transliteration = preg_replace($translation_heading[0], $translation_heading[1], $transliteration);
}
// add the object type at the beginning
if (strpos($row['object_type'], 'other (see object remarks)') === 0) {
    echo '<b>' . ucfirst($row['object_remarks']) . '</b><br>' . $transliteration;
} else {
    echo '<b>' . ucfirst($row['object_type']) . '</b><br>' . $transliteration;
}
echo '<br>';

}
?>

<!-- REVISION HISTORY PART -->
<?php
$showTransliteration = $user->canViewPrivateTransliterations() || $object->isPublicAtf();
if ($showTransliteration) {
echo 'Uploads and Revision(s):<br>';
$revQuery = "SELECT * FROM `revhistories`  WHERE object_id=$raw_obj_id ORDER BY `mod_date` DESC";
if (!isset($_SESSION['authenticated'])) {
    $revQuery .= " LIMIT 5";
}
$revs = $mysql_connection->query($revQuery);
while ($row = $revs->fetch(PDO::FETCH_ASSOC)) {

    $revtime = $row['mod_date'];
    $revauthor = $row['author'];
    $revcredit = $row['credit'];
    if ($revcredit == "") {
        $revcredit = $revauthor;
    }
    $string_revtime_html = urlencode($revtime);
    echo "<br/>";
    echo "<A target=\"_blank\" HREF=\"revhistory.php/?txtversion=$string_revtime_html&txtpnumber=$raw_obj_id&\" />";
    echo "$revtime by $revauthor, credit $revcredit";
    echo "</A>";
}
}

















}

if ($total_results == 0) {
    echo '<br/>';
    echo '<br/>';
    echo 'No results found.';
}
   
    ?>
    </body>

    </html>

<?php
/*
 * Show query performance profile result if query parameter d is set.
 *
 */
if (isset($_REQUEST["d"]) && extension_loaded('xhprof')) {
    $profiler_namespace = 'myapp'; // namespace for your application
    $xhprof_data = xhprof_disable();
    $xhprof_runs = new XHProfRuns_Default();
    $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

    // url to the XHProf UI libraries (change the host name and path)
    $profiler_url = sprintf('http://cdli.ucla.edu/search/xhprof/?run=%s&source=%s', $run_id, $profiler_namespace);

    echo '<a href="' . $profiler_url . '" target="_blank">Profiler output</a>';

}
