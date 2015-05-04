<?php
require_once "bootstrap.php";

// if the use has not login yet. redirect to login page
if (!User::isLogin(getEM())) {
    header('Location: login.php');
    exit;
}
/* @var User $user */
$user = User::getCurrentUserOrLoginAsGuest(getEM());

// if it is a update password request
if (array_key_exists('updatePW', $_POST)) {
    $oldpwd = $_POST['oldPW'];
    $login = User::login(getEM(), $user->getUsername(), $oldpwd);

    if ($login == null) {
        $updatePWMsg = 'Current password is incorrect';
    } else {
        $user->setPlainTextPassword($_POST['newPW']);
        getEM()->persist($user);
        getEM()->flush($user);
        $updatePWMsg = 'Password updated!';
    }
}
// if it is a unlock all request
if (array_key_exists('unlockMany', $_POST)) {
    if ($user->getAdmin() && $_POST["submit"] == "Unlock All") {
        // unlock everything!
        $locks = Lock::getActiveLocks();
    } else if ($user->getAdmin() && $_POST["submit"] == "Unlock" && $_POST["unlockusername"] != "") {
        // only admin has permission to unlock locks obtained other than himself
        $locks = Lock::getActiveLocksByUser(User::queryByUsername(getEm(), $_POST["unlockusername"]));
    } else {
        // unlock this user's own records
        $locks = Lock::getActiveLocksByUser($user);
    }
    /* @var Lock $lock */
    foreach ($locks as $lock) {
        Lock::unlock($lock);
    }
    getEM()->flush();
}
// if it is a request for unlocking a single lock
if (array_key_exists('unlockId', $_GET)) {
    $lock = getEM()->find('Lock', $_GET['unlockId']);
    if ($lock != null && ($lock->getAuthor() == $user || $user->getAdmin())) {
        // a user can only unlock his own locks except for that he is an admin
        Lock::unlock($lock);
        getEM()->flush();
    }
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>User Account Management</title>
    <link rel="stylesheet" type="text/css" href="/cdli.css"/>
    <link rel="stylesheet" type="text/css" href="cdlisearch.css">
    <link href="sign_style.css" media="screen" rel="stylesheet" type="text/css">
    <?php include_once("analyticstracking.php") ?>
</head>

<html>
<body>

<!-- HEADER PART -->
<hr align="left" size="2" width="1000"/>
<table border="0" class="header">
    <tr>
        <!-- left logo -->
        <td rowspan="3">
            <a href="/"><img alt="" width="81"
                             height="51" border="0"
                             src="cdli_logo.gif"/></a>
        </td>
        <!-- title in the middle -->

        <td rowspan="3" width="500" align="center" valign="middle">

            <h2>Account Management</h2>

        </td>
        <!-- right part -->
        <td align="left" width="200">
            <a href=search.php>
                Return to Search Page </a><BR/>
            <a href="/info/systeminfo.html">
                Access System Information
            </a>

            <?php
            echo "<a href=\"accountManagement.php\" link=\"#0099FF\" alink=\"#0099FF\" vlink=\"#0099FF\">
            <FONT color=\"#0099FF\">  Manage your account
            </FONT></a><br/>";

            echo "Logged in as <font color=\"Gray\">" . $user->getUsername() . "</font>&nbsp;&nbsp;";
            echo "<a href=\"logout.php\" link=\"#0099FF\" alink=\"#0099FF\" vlink=\"#0099FF\">
            <FONT color=\"#0099FF\">Log out
            </FONT></a>";
            ?>


        </td>
    </tr>
</table>

<?php if ($user->canEditTranliterations() || $user->getAdmin()) {
    //  only show lock related content for ATF editor or admins
    ?>

    <hr align="left" size="2" width="1000"/>
    <br/><br/>
    <b>Locked Record</b><br>

    <form action="" method="post">
        <input type="hidden" name="unlockMany">
        <?php
        if ($user->getAdmin()) {
            echo '<BR><input type="submit" name="submit" value="Unlock">';
            echo ' records of user: <input type ="text" name="unlockusername"  value="' . $user->getUsername() . '" >';
            echo '<BR><input type="submit" name="submit" value="Unlock All">';
        } else {
            echo '<input type="submit" name="submit" value="Unlock all of my records">';
        }
        ?>
    </form>
    <br>

    <table style="text-align: center;border: 1px solid white;">
        <tr style="border: 1px solid white;">
            <th>Tablet ID</th>
            <th style="width: 200px">Author</th>
            <th style="width: 200px">Date Locked</th>
            <th></th>
        </tr>

        <?php

        if ($user->getAdmin()) {
            $locks = Lock::getActiveLocks();
        } else {
            // for non-admin users, only show the locks for him or herself.
            $locks = Lock::getActiveLocksByUser($user);
        }
        /* @var Lock $lock */
        foreach ($locks as $lock) {
            $object = Object::getByObjectId($lock->getTrans()->getObjectId());
            printf("<tr>\r\n");
            printf("<td>%s</td>\r\n", $object->getObjectPId());
            printf("<td>%s</td>\r\n", $lock->getAuthor()->getUsername());
            printf("<td>%s</td>\r\n", $lock->getStartDate()->format('Y-m-d'));
            printf('<td><a href="#">', $lock->getId());
            printf('<img src="lock.png" class="lock-link" width="16" height="16" lockId="%d" pid="%s">',
                $lock->getId(), $object->getObjectPId()
            );
            printf("</a></td>\r\n");
            printf("</tr>\r\n");
        }
        ?>
    </table>

<?php } ?>

<br><br><br><br><br><br>
<b>Update Password</b><br>
<?php
if (isset($updatePWMsg)) {
    echo "<p style=\"color: #FFFFFF\">$updatePWMsg</p>";
    unset($updatePWMsg);
}
?>
<form id="updatePW" name="updatePW" method="post" action="">
    <p><label for="oldPW">Current Password:</label><input type="password" name="oldPW" id="oldPW"/></p>

    <p><label for="newPW">New password:</label><input type="password" name="newPW" id="newPW"/></p>

    <p><input name="updatePW" type="submit" id="updatePW" value="Update"/></p></form>
<br><br><br><br>


<?php
if ($user->getAdmin()) {
    // load account management table (implemented based on jTable.js)
    readfile("accountManagementTable.html");
}
?>

<script>
    // show confirmation dialog for unlock
    $(".lock-link").click(function (e) {
        if (confirm("Do you really want to unlock " + $(this).attr("pid") + "?")) {
            window.location.href = "?unlockId=" + $(this).attr("lockId");
        }
    })
</script>
</body>
</html>
