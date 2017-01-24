<?php
session_start(); // Starting Session
$error = ''; // Variable To Store Error Message
if (isset($_POST['submit'])) {
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error = "Username or Password is invalid";
    } else {
// Define $username and $password
        $username = $_POST['username'];
        $password = $_POST['password'];
// Establishing Connection with Server by passing server_name, user_id and password as a parameter
        $connection = mysql_connect("localhost", "root", "root");
        $username = stripslashes($username);
        $password = stripslashes($password);
        $username = mysql_real_escape_string($username);
        $password = mysql_real_escape_string($password);
// Selecting Database
        $db = mysql_select_db("swift_move", $connection);
// SQL query to fetch information of registerd users and finds user match.
        $query = mysql_query("select * from backend where backend_pass='$password' AND backend_user='$username'", $connection);
        $rows = mysql_num_rows($query);
        if ($rows == 1) {
            $_SESSION['login_user'] = $username; // Initializing Session
            header("location: index.php"); // Redirecting To Other Page
        } else {
            echo "<div class=\"alert alert-danger alert-dismissible\" role=\"alert\">
                    <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>
                   รหัสผ่านไม่ถูกต้อง</div>";
        }
        mysql_close($connection); // Closing Connection
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ระบบบริหารจัดการ Swift Move</title>
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../build/css/custom.min.css" rel="stylesheet">
</head>

<body class="login">
<div>
    <a class="hiddenanchor" id="signup"></a>
    <a class="hiddenanchor" id="signin"></a>

    <div class="login_wrapper">
        <div class="animate form login_form">
            <section class="login_content">
                <form role="form" name="form" id="form" method="post" action="login.php">
                    <h1>เข้าสู่ระบบ</h1>
                    <div class="form-group">
                        <input class="form-control" placeholder="ชื่อผู้ใช้งาน" name="username" type="text" required=""
                               autofocus>
                    </div>
                    <div class="form-group">
                        <input class="form-control" placeholder="รหัสผ่าน" name="password" type="password" required=""
                               value="">
                    </div>
                    <div>
                        <button type="submit" name="submit" value="Save" class="btn btn-md btn-success btn-block">
                            เข้าสู่ระบบ
                        </button>

                    </div>

                    <div class="clearfix"></div>

                    <div class="separator">

                        <div class="clearfix"></div>
                        <br/>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
</body>
</html>