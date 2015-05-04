<?php
function sql_failure_handler($query, $error){

$msg = htmlspecialchars("Failed Query: {$query}<br>SQL Error: {$error}");
if(defined("debug")){

return $msg;
}
return "Requested page is temporarily unavailable.";


}



?>
