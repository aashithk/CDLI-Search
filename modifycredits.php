<?php
session_start();
ob_start();
// if session variable not set, redirect to login page
if (!isset($_SESSION['authenticated'])) {
  header('Location: http://www.cdli.ucla.edu/search/');
  exit;
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>CDLI Modify Credits</title>
    <?php include_once("analyticstracking.php") ?>
</head>

<body bgcolor="black">
<div id="header">
    <h1 style="color: #FFFFFF;">CDLI Edit</h1>
</div>
<div id="wrapper">

    <div id="maincontent">
    <span style="color:white">

<?php

    mb_internal_encoding( 'UTF-8' );

	require_once 'includes/auth.inc';
	require_once 'includes/error_handler.inc';
	$con = mysql_connect($dbHost, $dbUser, $dbPass) or die(sql_failure_handler($query, mysql_error()));

	mysql_select_db($dbDatabase) or die(sql_failure_handler($query, mysql_error()));
	mysql_set_charset('utf8',$con);
	mysql_query("set names 'utf8'", $con);
	mysql_query('SET character_set_client=utf8');
	mysql_query('SET character_set_connection=utf8');
	mysql_query('SET character_set_results=utf8');
	mysql_query('SET collation_connection=utf8_general_ci');

	$newcredits = $_POST["newcredits"];
	$objectid = $_POST["objectid"];
	$version = $_POST["version"];

	$pattern = "/(\n|\r)+/";
	$replacement = "\n";
	// Above, the escape string method was commented out, so doubling up to be a bit safer. 
	$newcredits = mysql_real_escape_string($newcredits);
	$newcredits = preg_replace($pattern,$replacement,$newcredits);
	$newcredits = "\"".$newcredits."\"";
	$string_query = "UPDATE revhistories SET credit=$newcredits WHERE object_id='$objectid' and mod_date='$version'"; 
        $result = mysql_query("UPDATE revhistories SET credit=$newcredits WHERE object_id='$objectid' and mod_date='$version'"); 

	$url = "P".str_pad($objectid, 6, "0", STR_PAD_LEFT);
	mysql_close($con);
//here maybe bring back to verhistory page.
	header("Location: http://www.cdli.ucla.edu/search/index.php?SearchMode=Text&txtID_Txt=$url");

    ?>
    </span>
    </div>

</div>
</body>
</html>
