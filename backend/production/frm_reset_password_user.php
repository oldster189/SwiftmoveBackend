<?php
include('session.php');
include('connection.php');


isset($_REQUEST['user_password']) ? $user_password = $_REQUEST['user_password'] : $user_password = '';
isset($_REQUEST['user_id']) ? $user_id = $_REQUEST['user_id'] : $user_id = '';
isset($_REQUEST['uid']) ? $uid = $_REQUEST['uid'] : $uid = '';
isset($_REQUEST['submit']) ? $submit = $_REQUEST['submit'] : $submit = '';


if ($submit) {
    if ($submit == 'Save') {
        // Insert Data
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($user_password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        $encrypted_password = $hash["encrypted"];
        $encrypted_salt = $hash["salt"];
        $strsql = "update user set user_password ='$encrypted_password',user_salt='$encrypted_salt' where user_id = '$uid'";
        $objsql = mysql_query($strsql) or die("Error Query [" . $strsql . "]");
        echo"<script language='javascript'>alert('บันทึกข้อมูลเรียบร้อยแล้ว !!!'); </script>";
        echo"<script language='javascript'>window.location='tb_user.php';</script>";
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

    <?php require("title.php"); ?>

    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="../build/css/custom.min.css" rel="stylesheet">
</head>

<body class="nav-md">
<div class="container body">
    <div class="main_container">
        <div class="col-md-3 left_col">
            <div class="left_col scroll-view">
                <!-- sidebar menu -->
                <?php require("sidebar.php"); ?>
                <!-- /sidebar menu -->
            </div>
        </div>

        <!-- top navigation -->
        <?php require("top_navigation.php"); ?>
        <!-- /top navigation -->


        <!-- page content -->
        <div class="right_col" role="main">
            <div class="">
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-8 col-sm-8 col-xs-8 col-lg-offset-2">
                        <div class="x_panel">
                            <div class="x_title">
                                <h3>เปลี่ยนรหัสผ่านสำหรับผู้ใช้บริการ</h3>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <br/>
                                <form action="frm_reset_password_user.php" name="frm_group" method="POST" class="form-horizontal form-label-left">
                                    <?php
                                    $streditsql = "Select * from user where user_id=$user_id";
                                    $obeditjsql = mysql_query($streditsql) or die("Error Query [" . $streditsql . "]");
                                    $row_edit = mysql_fetch_array($obeditjsql);
                                    ?>
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="user_name">ชื่อผู้ใช้บริการ
                                        </label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <input type="text" id="user_name" name="user_name"
                                                   class="form-control col-md-7 col-xs-12" disabled="disabled"
                                                   value="<?= $row_edit['user_first_name']; ?> <?= $row_edit['user_last_name']; ?>">
                                            <input type="hidden" name="uid"
                                                   value="<?= $row_edit['user_id'] ?>">

                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="user_password">รหัสผ่านใหม่

                                        </label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <input type="password" id="user_password" name="user_password"
                                                   maxlength="16"  required="required" class="form-control col-md-7 col-xs-12">
                                        </div>
                                    </div>
                                    <div class="ln_solid"></div>
                                    <div class="form-group">
                                        <div align="center">
                                            <button type="reset" name="reset" value="Cancel"
                                                    class="btn btn-info"><i
                                                    class="fa fa-refresh fa-fw"></i>ล้างข้อมูล
                                            </button>
                                            <button OnClick="return chkdel();" type="submit" name="submit" value="Save"
                                                    class="btn btn-success"><i
                                                    class="fa fa-floppy-o fa-fw"></i>บันทึกข้อมูล
                                            </button>

                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require("footer.php"); ?>

    </div>
</div>

<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../build/js/custom.min.js"></script>

<script language="JavaScript">
    function chkdel(){if(confirm('  กรุณายืนยันการบันทึกอีกครั้ง !!!  ')){
        return true;
    }else{
        return false;
    }
    }
</script>
</body>
</html>