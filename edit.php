<?php
require_once "bootstrap.php";
header('Content-type: text/html; charset=utf-8');
// user must have editing permission
$user = User::getCurrentUserOrLoginAsGuest(getEM());

// check if the user has the permission to modify the text
if (!$user->canEditTranliterations()) {
    header('Location: http://www.cdli.ucla.edu/search/');
    exit;
}
$editingid = $_POST['editingid'];
$object = Object::getByObjectId($editingid);

$editPageRenderer = new EditPageRenderer(array_keys(Search::$FIELD_MAPPINGS),
    $user);

$html = $editPageRenderer->getHTMLForObject($object);
?>

<html>
<head>
    <title>CDLI-Editing</title>
    <link href="edit.css" media="screen" rel="stylesheet" type="text/css"/>
    <style type="text/css">
        textarea {
            font-size: 12pt;
        }
    </style>
    <?php include_once("analyticstracking.php") ?>
</head>
<body>

<div class="header">
    <div class="icon">
        <a href="http://cdli.ucla.edu"><img src="cdli_logo.gif"/></a>
    </div>
</div>

<div class="entire">
    <?php echo $html; ?>
</div>

</body>
</html>	
