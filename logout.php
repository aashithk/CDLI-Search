<?php
// run this script only if the logout button has been clicked
session_start();

session_destroy();

ob_start();
header("location: search.php"); // Back to login form
ob_end_flush();


?>
