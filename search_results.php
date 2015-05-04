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
    'PrimaryPublication' => "115px",
    'MuseumNumber' => "80px",
    'Period' => "110px",
    'DatesReferenced' => "110px",
    'Provenience' => "110px",
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

//generate next page link
function gen_next_page_link($offset, $page_size, $total_result)
{
    if ($offset + $page_size < $total_result) {
        $output = gen_url();
        $new_offset = $offset + $page_size;
        $return = "<a href=$output&offset=$new_offset>NEXT</a>";
        return $return;
    } else {
        return "";
    }
}

//generate previous page link
function gen_prev_page_link($offset, $page_size)
{
    if ($offset != 0) {
        $new_offset = $offset - $page_size < 0 ? 0 : $offset - $page_size;
        $output = gen_url();
        $return = "<a href=$output&Page=$new_offset>PREV</a>";
    } else {
        $return = "";
    }
    return $return;
}

// generate range of results found
function paginate_range($offset, $page_size, $total)
{
    $end = min($offset + $page_size, $total);
    $start = min($offset + 1, $total);
    return "$start - $end";
}

// generate header for tabular display mode
function generate_header_for_tabular_mode($search)
{
    // the fields to be display
    global $line_fields;
    $html = "";
    // render table header
    $html .= '<table width="1200" border="0" cellspacing="10" cellpadding="2" style="font-size: 12px;table-layout: fixed;word-break: break-all;">';
    $html .= '<tr align="left" valign="top">';
    // for each field, render the full field name as the table header
    foreach ($line_fields as $key => $width) {
        $reorder_query = http_build_query(array_merge($_GET, array(
            'order' => $key
        )));
        // get full name of the field
        $field_echo = Search::$FIELD_MAPPINGS[$key][1];
        $html .= '<td width="' . $width . '"><a href="search_results.php?' . $reorder_query . '">' . $field_echo . '</a></td>';
    }
    // only if the user searched for text (instead of fields) do we display transliteration field
    if ($search->hasTextSearch()) {
        $html .= '<td>Transliteration</td>';
    }

    // only if the user searched for translation (instead of fields) do we display translation field
    if ($search->hasTranslationSearch()) {
        $html .= '<td>Translation</td>';
    }

    // only if the user searched for comment (instead of fields) do we display comment field
    if ($search->hasCommentSearch()) {
        $html .= '<td>Comment</td>';
    }
     // only if the user searched for structure (instead of fields) do we display comment field
    if ($search->hasStructureSearch()) {
        $html .= '<td>Structure</td>';
    }
    $html .= '</tr>';
    return $html;
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

if ($total_results == 0) {
    echo 'No results found.';
}
// create a object renderer for the desired search mode and display mode

$objectHTMLRenderer = null;
if ($display_mode == TABULAR_DISPLAY_MODE) { //  tabular mode
    if ($search->hasTextSearch() || $search->hasTranslationSearch() || $search->hasCommentSearch() || $search->hasStructureSearch()) {
        // for tabular mode with transliteration search
        $objectHTMLRenderer = new TabularObjectRenderer(array_keys($line_fields),
            $transPhrases,
            $translationPhrases,
            $commentPhrases,
            $structurePhrases,
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

// use the object renderer to generate html for each matched object
$result_html = "";
/* @var Object $object */
foreach ($results as $object) {
    $result_html .= $objectHTMLRenderer->getHTMLForObject($object);
}

//
if ($display_mode == TABULAR_DISPLAY_MODE) {
    $result_html = generate_header_for_tabular_mode($search)
        . $result_html
        . '</table>';
}
?>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
        <title>CDLI-Found Texts</title>
        <!-- Latest compiled and minified CSS -->

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
                <h1>Search results
                    <?php
                    if ($search->hasTextSearch())
                        echo "for '" . $_GET["TextSearch"] . "'";
                    ?>
                </h1>
                <br/>
                <?php echo gen_prev_page_link($offset, DISPLAY_COUNT) . "&nbsp;&nbsp;"; ?>
                Page: <?php echo $search->getOffset() / $search->getLimit() + 1; ?>
                <?php echo "&nbsp;&nbsp;" . gen_next_page_link($offset, DISPLAY_COUNT, $total_results); ?>

                <br/>
                
                <?php
                $results_found = paginate_range($offset, DISPLAY_COUNT, $total_results);
                //echo "$results_found displayed of $total_results found";
                
                
                $fcount=0;
                 if ($search->hasTextSearch())
                {   
					#$pieces = explode(",", $_GET["TextSearch"]);
					$ps=preg_split ( '/,/', $_GET["TextSearch"]); 
					if(count($ps)>1)
					{
                    echo "$total_results text(s) found";
					}
                    else
                    {
                    //$fcount=(substr_count(  $result_html , $ps[0]));
                    $testem='class="found"';
                    $fcount=(substr_count(  $result_html , $testem));
                    if($fcount<$total_results)
                    {
                        $fcount=round(($total_results)*($fcount/2000));

                    }
                    echo "".$fcount." instance(s) found in $total_results text(s)"; 
                    }

                }  elseif ($search->hasCommentSearch()) 
                {
                	$ps=preg_split ( '/,/', $_GET["CommentSearch"]); 
					if(count($ps)>1)
					{
                    echo "$total_results text(s) found";
					}
                    else
                	{
                    $fcount=(substr_count(  $result_html , $ps[0]));
                    //$testem='class="found"';
                    //$fcount=(substr_count(  $result_html , $testem));
                    if($fcount<$total_results)
                    {
                        $fcount=round(($total_results)*($fcount/2000));

                    }
                    echo "".$fcount." instance(s) found in $total_results text(s)";
                    }
                }
                elseif ($search->hasTranslationSearch()) 
                {
                	$ps=preg_split ( '/,/', $_GET["TranslationSearch"]); 
					if(count($ps)>1)
					{
                    echo "$total_results text(s) found";
					}
                    else
                    {
                    //$fcount=(substr_count(  $result_html , $ps[0]));
                    $testem='class="found"';
                    $fcount=(substr_count(  $result_html , $testem));
                    if($fcount<$total_results)
                    {
                        $fcount=round(($total_results)*($fcount/2000));

                    }
                    echo "".$fcount." instance(s) found in $total_results text(s)";
                    }
                }
                else
                {
                    echo "$total_results text(s) found";
                }
                
                ?>
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
    <table class="header" width="1000" style="font-size: 12px">
        <tbody>
        <tr>
            <td><a class="download-link" href="download_data_new.php?data_type=all">Download all text</a></td>
            <td><a class="download-link" href="download_data_new.php?data_type=just_transliteration">Download
                    transliterations</a></td>
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
            if ($display_mode == TABULAR_DISPLAY_MODE) {
                $other_mode_query = http_build_query(array_merge($_GET, array(
                    'SearchMode' => FULL_DISPLAY_MODE,
                    'offset' => 0
                )));
                echo '<a href="search_results.php?' . $other_mode_query . '">Expand to full transliterations</a>';
            } else {
                $other_mode_query = http_build_query(array_merge($_GET, array(
                    'SearchMode' => TABULAR_DISPLAY_MODE,
                    'offset' => 0
                )));
                echo '<a href="search_results.php?' . $other_mode_query . '">Reduce to catalogue data</a>';
            }
            echo '</td>';
            ?>
            <td><a href="http://cdli.ox.ac.uk/wiki/abbreviations_for_assyriology" target="_blank">Unclear
                    abbreviations?</a>
            </td>
        </tr>
        </tbody>
    </table>

    <hr align="left" size="2" width="1200"/>

    <?php
    //-- check if the updatedIDs are set --//
    if (isset($_SESSION['updatedIDs']) || isset($_SESSION['nonUpdatedIDs']) || isset($_SESSION['lockedIDs'])) {
        echo generate_updatedIds();
    }
    // display the search results
    echo $result_html;

    // display pagination
    echo gen_prev_page_link($offset, DISPLAY_COUNT) . "&nbsp;&nbsp;";
    echo "Page: ";
    echo $offset / DISPLAY_COUNT + 1;
    echo "&nbsp;&nbsp;" . gen_next_page_link($offset, DISPLAY_COUNT, $total_results);
    ?>
    </body>
<script src="http://cdli.ucla.edu/js/startup_script.js"></script>

    <script>
        var allPids = <?php echo json_encode($search->getAllPIds())?>;
        // When user click the Lock link,
        // show a confirmation dialog for user to confirm if they are going to lock the matched objects
        $("#lock").click(function (e) {
            var res = confirm("You are going to lock " + allPids.length + " texts. Are you sure?");
            if (res == true) {
                jQuery.post("lock.php", {pids: allPids.join()},
                    function (res) {
                        if (res.result == "success") {
                            alert("Successfully locked " + res.msg + " texts");
                        }
                        else {
                            alert("Error:" + res.msg);
                        }
                    }, "json");
            }
        })
    </script>
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
