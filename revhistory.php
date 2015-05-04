<?php
session_start();
$logged_in = false;
if (isset($_SESSION['authenticated'])) {
    $logged_in = true;
}
header('Content-type: text/html; charset=utf-8');
set_time_limit(180)?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
    <title>CDLI-Revison</title>
    <link rel="stylesheet" type="text/css;charset=UTF-8" href="/cdli.css"/>
    <?php include_once("analyticstracking.php") ?>
</head>
<body link="#0099FF" alink="#0099FF" vlink="violet">
<TABLE>
    <?php

    $DEBUG = false;
    mb_internal_encoding('UTF-8');

    require_once 'includes/auth.inc';
    require_once 'includes/error_handler.inc';


    if (isset($_GET['txtpnumber'])) {
        $pnumber = $_GET['txtpnumber'];

        $newpnumber = "$pnumber";
        $pnumber = $newpnumber;
    } else {
        $pnumber = "";
    }
    if (isset($_GET['txtversion'])) {
        $version = $_GET['txtversion'];
        $version = htmlentities($version);

        $version = "$version";
    } else {
        $version = "";
    }

    if ($version <> "" && $pnumber <> "") {

        $con = mysql_connect($dbHost, $dbUser, $dbPass) or die(sql_failure_handler($query, mysql_error()));

        mysql_select_db($dbDatabase) or die(sql_failure_handler($query, mysql_error()));
        mysql_set_charset('utf8', $con);
        mysql_query("set names 'utf8'", $con);
        mysql_query('SET character_set_client=utf8');
        mysql_query('SET character_set_connection=utf8');
        mysql_query('SET character_set_results=utf8');
        $version = mysql_real_escape_string($version);
        $pnumber = mysql_real_escape_string($pnumber);

        $string_query = "SELECT new_text, author,credit from revhistories where mod_date = '$version' and object_id = '$pnumber'";
        //echo $string_query;
        $Recordset1 = mysql_query($string_query) or die(sql_failure_handler($string_query, mysql_error()));;

        mysql_close($con);

        $num_rows = mysql_num_rows($Recordset1);
        $printResult[] = array();
        for ($iWalk = 0; $iWalk < $num_rows; ++$iWalk) {
            $row = mysql_fetch_array($Recordset1);
            $string_wholetext = trim($row['new_text'], "'");
            $string_author = $row['author'];
            $string_credit = $row['credit'];
            if ($string_credit == "") {
                $string_credit = $string_author;
            }

            echo "<TR><TD>Author: $string_author | Credit: $string_credit </TD></TR>";
            $string_wholetext = nl2br($string_wholetext);
            echo "<TR align=\"left\" valign=\"top\"><TD width=\"400\">$string_wholetext</TD></TR>";
            if ($logged_in) {
                echo "<TR align=\"left\"><TD width=\"400\">
						<form id=\"updateCreditsForm\" method = \"post\" action=\"../modifycredits.php\">
						<input name=\"send\" id=\"send\" type=\"submit\" value=\"Update Credits\">
						<input type = \"text\" name = \"newcredits\" id=\"newcredits\" ></TD></TR>
						<input type = \"hidden\" name = \"objectid\" id = \"objectid\" value=\"$pnumber\">
						<input type = \"hidden\" name = \"version\" id = \"version\" value=\"$version\">
						</form>";
                //put here the empty forms with pnumber and date to be able to fetch the entry on the next page.
            }
        }
    }
    ?>

</TABLE>
</BODY>
</HTML>
