<?php
require "bootstrap.php";

header('Content-type: text/html; charset=utf8');
ini_set('display_errors', 1);
ini_set('memory_limit', '1073741274');
ini_set('max_execution_time', 300);
mb_internal_encoding('ISO-8859-1');
error_reporting(E_ALL);

// if true, return a file will all the data,
// if false, return a file with jus the transliterations
$type = TextDownloadObjectRenderer::ALL_DATA;

if (isset($_GET['data_type']) && $_GET['data_type'] == 'just_transliteration') {
    $type = TextDownloadObjectRenderer::TRANS_ONLY;
}

// send the appropriate headers
$display_file_name = 'cdli_';
if ($type == TextDownloadObjectRenderer::ALL_DATA) {
    $display_file_name .= 'result';
} else {
    $display_file_name .= 'atf';
}

// make it an attachment
$display_file_name .= '_' . date('Ymd') . '.txt';
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $display_file_name);
header('Content-Transfer-Encoding: binary');


if (isset($_SESSION['search'])) {
    // the sql query used to get the search results
    /* @var Search $search */
    $search = unserialize($_SESSION['search']);
    $user = User::getCurrentUserOrLoginAsGuest(getEM());
    $results = $search->setUser($user)
        ->setOffset(0)
        ->setLimit(null)
        ->setEM(getEM())
        ->getResults();

    $objectHTMLRenderer = new TextDownloadObjectRenderer(array_keys(Search::$FIELD_MAPPINGS),
        $user, $type);

    // write each result to file
    $output = "";
    /* @var Object $object */
    foreach ($results as $object) {
        $output .= $objectHTMLRenderer->getHTMLForObject($object);
    }
    // echo output;
    echo $output;
} else {
    die('No SQL query specified.');
}

exit;
