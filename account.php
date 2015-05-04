<?php
const UNCHANGED = "<--UNCHANGED-->";
require_once "bootstrap.php";
ini_set('display_errors', 1);

$listFields = Array('id', 'username',
    'createdDate',
    'admin', 'canDownloadHdImages',
    'canViewPrivateCatalogues',
    'canViewPrivateTransliterations',
    'canEditTranliterations',
    'canViewPrivateImages', 'canViewIpadWeb', 'collectionPassword',
    'filtering');
$booleanFields = Array(
    'admin', 'canDownloadHdImages',
    'canViewPrivateCatalogues',
    'canViewPrivateTransliterations',
    'canEditTranliterations',
    'canViewPrivateImages',
    'canViewIpadWeb');

function getJSONForUser(\User $user)
{
    global $listFields;
    $row = array();
    foreach ($listFields as $field) {
        $var = $user->getByFieldName($field);
        if ($var instanceof DateTime) {
            $row[$field] = $var->format("Y-m-d");
        } else {
            $row[$field] = $var;
        }
    }
    // do not output the password hash. output UNCHANGED string instead
    $row["password"] = UNCHANGED;
    return $row;
}

try {
    if (!User::getCurrentUserOrLoginAsGuest(getEM())->getAdmin()) {
        header("Location: /");
        exit();
    }
    //Getting records (listAction)
    if ($_GET["action"] == "list") {
        //Add all records to an array
        if (isset($_GET["jtSorting"])) {
            list($orderBy, $order) = explode(" ", $_GET["jtSorting"]);
        } else {
            $orderBy = "username";
            $order = "ASC";
        }
        $users = getEM()->createQueryBuilder()->select("u")
            ->from("User", "u")
            ->orderBy("u." . $orderBy, $order)
            ->getQuery()
            ->getResult();

        $rows = array();
        /* @var User $user */
        foreach ($users as $user) {
            $row = getJSONForUser($user);
            $rows[] = $row;
        }
        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['Records'] = $rows;
        echo json_encode($jTableResult);
    } //Creating a new record (createAction)
    else if ($_GET["action"] == "create") {

        if ($_POST["password"] == "" || $_POST["username"] == "") {
            throw new Exception ("The username and password cannot be empty");
        }


        $user = User::queryByUsername(getEM(), $_POST["username"]);
        if ($user != null) {
            // if the username already exist, return error
            throw new Exception ("The user " . $_POST["username"] . " already exist.");
        }

        $user = new User();
        // set boolean fields to be false since
        // they won't appear in the POST if they are set as false
        foreach ($booleanFields as $field) {
            $user->setByFieldName(false, $field);
        }
        foreach ($_POST as $field => $value) {
            if ($field != "id" && $field != "password") {
                if (in_array($field, $booleanFields)) {
                    // if it is a boolean fields, convert the str to boolean
                    $value = $value == "true" ? true : false;
                }
                $user->setByFieldName($value, $field);
            }

        }

        $user->setPlainTextPassword($_POST["password"]);
        $user->setCreatedDate(new DateTime());
        getEM()->persist($user);
        getEM()->flush($user);
        // Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['Record'] = getJSONForUser($user);
        print json_encode($jTableResult);

    } //Updating a record (updateAction)
    else if ($_GET["action"] == "update") {
        /* @var User $user */
        $user = getEM()->find("User", $_POST["id"]);

        // set boolean fields to be flase since
        // they won't appear in the POST if they are set as false
        foreach ($booleanFields as $field) {
            $user->setByFieldName(false, $field);
        }
        foreach ($_POST as $field => $value) {
            if (in_array($field, $booleanFields)) {
                // convert str to boolean for boolean fields
                $user->setByFieldName($value == "true" ? true : false, $field);
            }
        }
        getEM()->persist($user);
        getEM()->flush();
        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        $jTableResult['Record'] = getJSONForUser($user);
        print json_encode($jTableResult);
    } //Deleting a record (deleteAction)
    else if ($_GET["action"] == "delete") {
        $user = getEM()->find("User", $_POST["id"]);
        if ($user->getUsername() == "guest") {
            throw new Exception("Cannot remove the <i>guest</i> user!");
        }
        getEM()->remove($user);
        getEM()->flush();
        //Return result to jTable
        $jTableResult = array();
        $jTableResult['Result'] = "OK";
        print json_encode($jTableResult);
    }


} catch (Exception $ex) {
    //Return error message
    $jTableResult = array();
    $jTableResult['Result'] = "ERROR";
    $jTableResult['Message'] = $ex->getMessage();
    echo json_encode($jTableResult);
}

?>