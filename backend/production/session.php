<?php

     $connection = mysql_connect("localhost", "root", "root");
    $db = mysql_select_db("swift_move", $connection);
    
    session_start();
    
    $user_check = $_SESSION['login_user'];
    $ses_sql = mysql_query("select backend_user from backend where backend_user='$user_check'", $connection);
    $row = mysql_fetch_assoc($ses_sql);
    $login_session = $row['backend_user'];
    
    if (!isset($login_session)) {
        mysql_close($connection);
        header('Location: login.php');
    }
    
?>