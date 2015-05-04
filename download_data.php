<?php
/**
 * THIS FILE IS NO LONGER USED! See download_data_new.php for the latest version.
 * The file is kept here as it is referred by the search_results_sms.php and search_results_tinker.php
 * Created by JetBrains PhpStorm.
 * User: Ryan
 * Date: 6/7/13
 * Time: 5:24 PM
 * To change this template use File | Settings | File Templates.
 */
session_start();
//header('Content-type: text/html; charset=ISO-8859-1');
header('Content-type: text/html; charset=utf-8');
ini_set('display_errors', '1');
// increase the allowable php memory usage
ini_set("memory_limit", "1G");
mb_internal_encoding('ISO-8859-1');
include 'corefuncs.php';

// if true, return a file will all the data,
// if false, return a file with jus the transliterations
$all_data = true;

if (isset($_GET['data_type']) && $_GET['data_type'] == 'just_transliteration') {
    $all_data = false;
}

// send the appropriate headers
$display_file_name = 'cdli_';
if ($all_data) {
    $display_file_name .= 'result';
} else {
    $display_file_name .= 'atf';
}
$display_file_name .= '_' . date('Ymd') . '.txt';
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $display_file_name);
header('Content-Transfer-Encoding: binary');

if (isset($_SESSION['query'])) {
    // the sql query used to get the search results
    $query = $_SESSION['query'];

    // Open connection to MySQL server.
    $mysql_connection = mysql_init();

    // Query the MySQL server.
    $results = $mysql_connection->query($query);
    if ($results == false) {
        die('Invalid query.');
    }

    // write each result to file
    while ($search_result = $results->fetch(PDO::FETCH_ASSOC)) {
        $objectID = $search_result['object_id'];
        if ($all_data) {
            // write all the data that's not the transliteration
            //edit the pnumber first to standard P###### formate
            $objectIdField = $search_fields['ObjectID'][SQL_COLUMN];
            $search_result[$objectIdField] = "P" . str_pad($search_result[$objectIdField], P_NUMBER_LENGTH, "0", STR_PAD_LEFT);
            foreach ($search_fields as $key => $value) {
                echo $value[DISPLAY_TEXT] . ': ' . $search_result[$value[SQL_COLUMN]] . "\n";
            }
            echo "Transliteration:\n";
        }
        $transliteration = $search_result['translit'];

        // write out the transliteration
        $length = strlen($transliteration);
        // if the transliteration is quoted, remove the quotes
        if (substr($transliteration, $length - 2, 1)) {
            $transliteration = substr($transliteration, 0, $length - 1);
        }
        if (substr($transliteration, 0, 1) == "'") {
            $transliteration = substr($transliteration, 1);
        }
        if ($transliteration != "") {
            if ($search_result['public_atf'] == 'yes' || isset($_SESSION['authenticated'])) {
                echo $transliteration . "\n\n";
            }
        }
    }
} else {
    die('No SQL query specified.');
}

exit;
