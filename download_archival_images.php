<?php
require "bootstrap.php";
mb_internal_encoding('UTF-8');

if ($_GET['download']) {
    $decoded_url = base64_decode($_GET['download']);
    $check_url = strtok($decoded_url, '?') . "WebInterface/login.html";
    $headers = @get_headers(strtok($check_url, '?'));
    if (preg_match('/^HTTP\/\d+\.\d+\s+(200|301|302)/', $headers[0])) {
        header("Location: {$decoded_url}");
    }
    exit();
}

$user = User::getCurrentUserOrLoginAsGuest(getEM());
$valid_request = ($user && $user->getUsername() &&
    $user->getCollectionPassword() && $_GET['email'] &&
    $user->canDownloadHdImages());
if ($valid_request) {
    if (isset($_SESSION['search'])) {
        // the sql query used to get the search results
        /* @var Search $search */
        $search = unserialize($_SESSION['search']);

        $results = $search->setUser($user)
            ->setOffset(0)
            ->setLimit(null)
            ->setEM(getEM())
            ->getResults();

        // Get metadata of search results
        $pids = array();
        foreach ($results as $object) {
            $pid = $object->getObjectPId();
            $pids[$pid] = "{$object->getObjectPId()}\t{$object->getMuseumNo()}\t" .
                "{$object->getAccessionNo()}\t{$object->getExcavationNo()}\t" .
                "{$object->getPeriod()}\t" . "{$object->getProvenience()}\t" .
                "{$object->getProvenience()}";
        }
    } else {
        die('No SQL query specified.');
    }

    // Create a unique identifier for this request.
    $unique_token = date('YmdHis');

    // Save the pids in a file to be used by the copy script.
    $writable = file_put_contents(
        "/Volumes/cdli_collections/collection_downloads/" .
        "{$user->getUsername()}/pids_{$unique_token}.txt",
        json_encode($pids));

    // Run script to copy the images, archive, and send notification email.
    if ($writable) {
        $clean_username = escapeshellarg($user->getUsername());
        $script_url = escapeshellarg($_SERVER['SCRIPT_URI']);
        $clean_collection_password = escapeshellarg($user->getCollectionPassword());
        shell_exec("python " .
            "/Library/WebServer/Documents/cdli/scripts/copy_image_files.py " .
            "{$_GET['email']} {$clean_username} {$clean_collection_password} " .
            "{$unique_token} {$script_url} " .
            "> /dev/null 2>/dev/null &");
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Download Archival tif Images</title>
    <link rel="stylesheet" type="text/css" href="cdli.css"/>
    <link rel="stylesheet" type="text/css" href="cdlisearch.css"/>
</head>

<body>
<!-- HEADER PART -->
<hr align="left" size="2" width="1000"/>
<table border="0" class="header">
    <tr>
        <!-- left logo -->
        <td rowspan="3">
            <a href="/">
                <img alt="" width="81" height="51" border="0" src="cdli_logo.gif"/>
            </a>
        </td>

        <!-- title in the middle -->
        <td rowspan="3" width="500" align="center" valign="middle">
            <h1>Download Archival tif Images</h1>
        </td>

        <!-- right part -->
        <td align="left" width="250">
            <a href="search.php">
                <?php
                echo $requestFrom == 'Quicksearch' ? 'Go to Full Search' : 'Return to Search Page';
                ?>
            </a>
            <br/>
            <a href="/info/systeminfo.html">
                Access System Information
            </a>

            <?php
            if (!isset($_SESSION['authenticated'])) {
                ?>
                <a target="_blank" href="login.php" link="#0099FF" alink="#0099FF" vlink="#0099FF">
                    <FONT color="#0099FF"> Internal for editors</FONT>
                </a>
            <?php
            } else {
                $username = $_SESSION['name'];
                ?>
                <br/>
                <a href="accountManagement.php" link="#0099FF" alink="#0099FF" vlink="#0099FF">
                    <FONT color="#0099FF"> Manage your account</FONT>
                </a>
                <br/>
                <a href="http://cdli.ucla.edu/?q=cdli-search-information" link="#0099FF" alink="#0099FF"
                   vlink="#0099FF">
                    <FONT color="#0099FF"> Search aids</FONT>
                </a>
                <br/>

                Logged in as <font color="Gray"><?php echo $username; ?></font>&nbsp;&nbsp;<br/>
                <a href="logout.php" link="#0099FF" alink="#0099FF" vlink="#0099FF">
                    <FONT color="#0099FF">Log out</FONT>
                </a>
            <?php
            }
            ?>
        </td>
    </tr>
</table>

<hr align="left" size="2" width="1000"/>

<p>
    <?php
    if ($user && $user->canDownloadHdImages()) {
    if ($valid_request) {
        if ($writable) {
            ?>
            Your download request has been received.<br/>
            An email will be sent to <?php echo $_GET['email']; ?> when the files are ready.<br/>
        <?php } else { ?>
            There was an error processing your request.<br/>
        <?php
        }
    } else {
    ?>

<p>
    Email download information to:

<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="text" name="email">
    <input type="submit" value="Generate Download">
</form>
</p>

<p style="width:600px;">
    Please be aware that the files offered here are subject to terms of use
    and copyright restrictions of the <?php echo $username ?>. Use of an email
    address other than that of the current account holder(s) constitutes the
    implied consent of your institution that the user of that email address
    is allowed to receive these image files. Consult the account institution's
    copyright office for further information.
</p>
<?php
}
} else {
    ?>
    You do not have permission to view this page.<br/>
<?php
}
if (isset($error)) {
    echo "<p style=\"color: #FFFFFF\">$error</p>";
}
?>
</p>
</body>
</html>
