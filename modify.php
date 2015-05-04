<?php require_once "bootstrap.php";
ini_set('display_errors', 1);
?>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
        <title>CDLI Edit</title>
        <?php include_once("analyticstracking.php") ?>
    </head>

<?php

$user = User::getCurrentUserOrLoginAsGuest(getEM());

// check if the user has the permission to modify the text
if (!$user->canEditTranliterations()) {
    header('Location: http://www.cdli.ucla.edu/search/');
    exit;
}

// get all the parameters
$new_translit = $_POST["comments"];
$editingid = $_POST["editingid"];
$optionalcredits = $_POST["credits"];

$object = Object::getByObjectId($editingid);
if ($object->canModifyBy($user)) {
    // only update trans if 1) the trans is not locked or 2) the lock owner is the user himself
    UpdateTrans::update($editingid, $new_translit, $user, $optionalcredits);
    $url = "P" . str_pad($editingid, 6, "0", STR_PAD_LEFT);
    // redirect to the page of the modified object
    header("Location: /$url");
    getEM()->flush();
} else {
    echo "Editing failed since the text is locked.";
}



?>