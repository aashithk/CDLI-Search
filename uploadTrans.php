<?php
/*
 * Write the transliterations contained in the uploaded file ($_FILES['file']) into the database.
 * Do not update a transliteration if it is locked by another user.
 *
 *
 */
require_once "bootstrap.php";
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);
ini_set('auto_detect_line_endings', true);

$user = User::getCurrentUserOrLoginAsGuest(getEM());

// check if the user has the permission to modify the text
if (!$user->canEditTranliterations()) {
    header('Location: http://www.cdli.ucla.edu/search/');
    exit;
}

if (isset($_POST['upload'])) {

    // get uploaded content
    $contents = file_get_contents($_FILES['file']['tmp_name']);
    // get optional credit
    $optional_credit = $_POST["credit"];

    $updateIDs = "";
    $nonUpdateIDs = "";
    $lockedIDs = "";
    // the link we will redirect to after uploading
    $link = "search_results.php?SearchMode=Text&requestFrm=Search&ObjectID=P000000%2c";
    $error = null;
    foreach (explode("&P", $contents) as $trans) {
        $trans = ltrim($trans); // do not rtrim the text, some people want the spaces at the end to be preserved
        if (strlen($trans) == 0 or !ctype_digit(substr($trans, 0, 6))) {
            continue;
        }
        $objectId = intval(substr($trans, 0, 6));
        $object = Object::getByObjectId($objectId);
        if ($object == null) {
            $error = "Non-Existing Object Id: P" . substr($trans, 0, 6);
            break;
        }
        $trans = '&P' . $trans;

        $lock = $object->isLocked();
        if ($lock != false && $lock->getAuthor() != $user) {
            // if the transliteration is locked by another user
            // append it to the locked pids, and do not update it.
            $lockedIDs .= $object->getObjectPId() . ',';
            continue;
        }
        if (UpdateTrans::update($objectId, $trans, $user, $optional_credit)) {
            // the object has been updated
            $updateIDs .= $object->getObjectPId() . ',';
        } else {
            // nothing changed
            $nonUpdateIDs .= $object->getObjectPId() . ',';
        }
    }
    if ($error == null) {
        getEM()->flush();
        $_SESSION['updatedIDs'] = rtrim($updateIDs, ", ");
        $_SESSION['nonUpdatedIDs'] = rtrim($nonUpdateIDs, ", ");
        $_SESSION['lockedIDs'] = rtrim($lockedIDs, ", ");
        header("Location: $link" . rtrim($updateIDs, ", "));
    } else {
        echo $error;
    }
}