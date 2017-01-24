<?php
$db_host="localhost";
$db_user="root";
$db_passwd="root";
$db_name="swift_move";


mysql_connect( $db_host,$db_user,$db_passwd) or die ("");
mysql_query("SET NAMES UTF8");
mysql_select_db($db_name) or die("Unable to connect to MySQL"); 
?>

