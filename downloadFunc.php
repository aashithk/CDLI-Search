<?php
/* This is from the OLD version of CDLI. Not sure if it is being used anymore
 */

require_once 'includes/auth.inc';
require_once 'includes/error_handler.inc';
$con = mysql_connect($dbHost, $dbUser, $dbPass) or die(sql_failure_handler($query, mysql_error()));

mysql_select_db($dbDatabase) or die(sql_failure_handler($query, mysql_error()));


function downloadFunc()
{
    $file = 'export';
    $result = mysql_query("SELECT *
		FROM `CellCat`
		group by object_id order by object_id LIMIT 100") or die(sql_failure_handler($query, mysql_error()));;
    mysql_close($con);
    if (mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            $csv_output .= $row['Field'] . "; ";
            $i++;
        }
    }
    $csv_output .= "\n";

    $filename = $file . "_" . date("Y-m-d_H-i", time());
    header("Content-type: application/vnd.ms-excel");
    header("Content-disposition: csv" . date("Y-m-d") . ".csv");
    header("Content-disposition: filename=" . $filename . ".csv");
    print $csv_output;
    exit;

}

?>
