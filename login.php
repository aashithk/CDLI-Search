<?php
require_once "bootstrap.php";

// process the script only if the form has been submitted
if (array_key_exists('login', $_POST)) {
    $user = User::login(getEM(), $_POST['username'], $_POST['pwd']);
    if ($user != null) {

        // the following two line of code is only for backward-compatibility
        $_SESSION['authenticated'] = true;
        $_SESSION['name'] = $user->getUsername();


        header('Location: search.php');
    } else {
        $error = 'Invalid username or password.';
    }


}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <link href="edit.css" media="screen" rel="stylesheet" type="text/css"/>
    <title>Login</title>
    <?php include_once("analyticstracking.php") ?>
</head>

<body bgcolor="black">
<div id="header">
    <div id="icon">
        <a href="/"><img src="cdli_logo.gif"/></a>
    </div>
    <div id="cetnerTitle">
        Log in
    </div>
</div>
<?php
if (isset($error)) {
    echo "<p style=\"color: #FFFFFF\">$error</p>";
}
?>
<form id="form1" name="form1" method="post" action="">
    <p style="color: #FFFFFF; font-size: 12pt">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username"/>
    </p>

    <p style="color: #FFFFFF; font-size: 12pt">
        <label for="textfield">Password:</label>
        <input type="password" name="pwd" id="pwd"/>
    </p>


    <p>
        <input name="login" type="submit" id="login" value="Log in"/>
    </p>
</form>
</body>
</html>
