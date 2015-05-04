<?php
/* This is from the OLD version of CDLI. Not sure if it is being used anymore
 */
session_start();
$dbdata = $_SESSION['printResult'];

if (isset($_POST['allatf'])) {
    $allatf = $_POST['allatf'];
    //echo $allatf;
}
$timestamp = time();

$myFile = "cdli_result_" . $timestamp . ".txt";
$filepath = "/Library/WebServer/Documents/cdli/search/uploadtemp/";
$path = $filepath . $myFile;
$fh = fopen($path, 'w') or die("can't open file");

$displayFields = array('primary_publication', 'author', 'publication_date', 'publication_history', 'collection', 'museum_no', 'accession_no', 'provenience', 'excavation_no', 'period', 'dates_referenced', 'object_type',
    'object_remarks', 'material', 'language', 'genre', 'subgenre', 'cdli_comments', 'db_source', 'atf_source', 'translation_source', 'ark_number', 'seal_id');
$fieldDescription = array('designation' => 'Designation', 'object_id' => 'CDLI no.', 'primary_publication' => 'Primary publication', 'author' => 'Author(s)', 'publication_date' => 'Publication date', 'publication_history' => 'Secondary publication(s)', 'collection' => 'Collection',
    'museum_no' => 'Museum no.', 'accession_no' => 'Accession no.', 'provenience' => 'Provenience', 'excavation_no' => 'Excavation no.', 'period' => 'Period', 'dates_referenced' => 'Dates referenced', 'object_type' => 'Object type',
    'object_remarks' => 'Object subtype', 'material' => 'Material', 'language' => 'Language', 'genre' => 'Genre', 'subgenre' => 'Sub-genre', 'cdli_comments' => 'CDLI comments', 'db_source' => 'Catalogue source', 'atf_source' => 'ATF source',
    'translation_source' => 'Translation', 'ark_number' => 'UCLA Library ARK no.', 'seal_id' => 'Seal no.');

function chkfwrite($handle, $data)
{
    global $path;
    if (fwrite($handle, $data) === FALSE) {
        echo "Cannot write to file ($path)";
    }
}

$rownum = count($dbdata);
for ($i = 0; $i < $rownum; $i++) {
    $object_id = $dbdata[$i]['object_id'];
    if (strlen($object_id) < 6) {
        for ($j = strlen($object_id); $j < 6; $j++) {
            $object_id = "0" . $object_id;
        }
    }
    $object_id = "P" . $object_id;
    $name = $fieldDescription['object_id'];
    chkfwrite($fh, $name . ": " . $object_id . "\n");

    $primepub_invalid = array("", "unpublished", "unpublished assigned", "unpublished assigned ?", "unpublished unassigned ?", "unpublished available", "unpublished unassigned", "unpublished (joined fragment)");
    $museno_invalid = array("", "not available", "BM .", "VAT .");
    $primepub = $dbdata[$i]['primary_publication'];
    $museno = $dbdata[$i]['museum_no'];
    $accesno = $dbdata[$i]['accession_no'];
    $excavno = $dbdata[$i]['excavation_no'];
    if (!in_array($primepub, $primepub_invalid)) {
        $designation = $primepub;
    } elseif (!in_array($museno, $museno_invalid)) {
        $designation = $museno;
    } elseif ($accesno != "") {
        $designation = $accessno;
    } elseif ($excavno != "") {
        $designation = $excavno;
    }

    $name = $fieldDescription['designation'];
    chkfwrite($fh, $name . ": " . $designation . "\n");


    foreach ($displayFields as $field) {
        $data = $dbdata[$i][$field];
        $name = $fieldDescription[$field];
        chkfwrite($fh, $name . ": " . $data . "\n");
    }

    $data = $dbdata[$i]['originaltext'];
    $data = trim($data, "'");
    //$data = preg_replace('/^\'(.*)\'$/','\${1}',$data);
    //$data = str_replace("\'",'',$data);
    $name = "Transliteration";
    chkfwrite($fh, "\n" . $name . ":\n" . $data . "\n\n");

}
fclose($fh);


$timestamp = date('Ymd');
$displayfname = "cdli_result_" . $timestamp . ".txt";
// check that it exists and is readable
if (file_exists($path) && is_readable($path)) {
    // get the file's size and send the appropriate headers
    $size = filesize($path);
    header('Content-Type: application/octet-stream');
    header('Content-Length: ' . $size);
    header('Content-Disposition: attachment; filename=' . $displayfname);
    header('Content-Transfer-Encoding: binary');
    // open the file in read-only mode
    // suppress error messages if the file can't be opened
    $file = @fopen($path, 'r');
    if ($file) {
        // stream the file and exit the script when complete
        fpassthru($file);
        exit;
    } else {
        header("Location: $error");
    }
}
unlink($path);
?>
