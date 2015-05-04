<?php
/**
 * This page is to find the transliterations that may contain errors. It has not been completed yet.
 */
require_once "bootstrap.php";
ini_set('display_errors', 1);
ini_set('memory_limit', '53687091200');
$user = User::getCurrentUserOrLoginAsGuest(getEM());
// check if the user has the permission to modify the text
if (!$user->canEditTranliterations()) {
    header('Location: http://www.cdli.ucla.edu/search/');
    exit;
}
$qb = getEM()->createQueryBuilder();
$qb->select('o')
    ->from('Object', 'o')
    ->innerJoin('o.trans', 'trans');


$qb->orWhere("REGEXP ( trans.wholetext, '^&P[0-9]{6} = ', 'insensitive')= 0");
$qb->orWhere("REGEXP ( trans.wholetext, '(^|\n)[0-9]+[^\\.0-9]', 'insensitive')= 1");
$qb->orWhere("REGEXP ( trans.wholetext, '(^|\n)[0-9]+\\.[^ ]', 'insensitive')= 1");
$qb->orWhere("REGEXP ( trans.wholetext, '(^|\n)[0-9]+[^\n]*[^ -~\n][^\n]*)', 'insensitive')= 1");

// get all the matched objectIDs without rearding pagination
$qb->setMaxResults(null)
    ->setFirstResult(0);
// select only objectId
$qb->select('o')->orderBy("o.objectId", "ASC");
//

?>


<html>
<head>
    <title>CDLI-Editing</title>
    <link rel="stylesheet" href="vendor/css/codemirror/codemirror.css">
    <link rel="stylesheet" href="vendor/css/codemirror/codemirror-theme.css">
    <link href="edit.css" media="screen" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        textarea {
            font-size: 12pt;
        }
    </style>
    <?php include_once("analyticstracking.php") ?>
</head>


<div id="header">
    <div id="icon">
        <a href="http://cdli.ucla.edu"><img src="cdli_logo.gif"/></a>
    </div>
</div>


</head>
<body>
<?php


$results = $qb->getQuery()->getResult();
echo '<p><b>Found total ' . count($results) . ' texts with syntax errors. Show the first 50 texts of them.</b></p>';
$editPageRenderer = new EditPageRenderer(array_keys(Search::$FIELD_MAPPINGS), $user);
$count = 0;

foreach ($results as $object) {
    echo '<div id="entire">';
    echo $editPageRenderer->getHTMLForObject($object);
    echo '</div>';
    $count++;
    if ($count > 50) {
        break;
    }
}
?>



<script src="vendor/js/jquery-1.6.4.min.js"></script>
<script src="vendor/js/codemirror/codemirror.js"></script>
<script src="vendor/js/codemirror/cdli.js"></script>
<script>

    //
    <!-- Create a simple CodeMirror instance -->

    $("textarea").each(function (index, el) {
        var editor = CodeMirror.fromTextArea(el, {
            lineNumbers: true,
            theme: "cdli",
            mode: "cdli"
        });
        editor.setSize("500", "800");
    });
</script>
</html>