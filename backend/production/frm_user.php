<?php
include('session.php');
include('connection.php');

isset($_REQUEST['user_first_name']) ? $user_first_name = $_REQUEST['user_first_name'] : $user_first_name = '';
isset($_REQUEST['user_last_name']) ? $user_last_name = $_REQUEST['user_last_name'] : $user_last_name = '';
isset($_REQUEST['user_email']) ? $user_email = $_REQUEST['user_email'] : $user_email = '';
isset($_REQUEST['user_password']) ? $user_password = $_REQUEST['user_password'] : $user_password = '';
isset($_REQUEST['user_tel']) ? $user_tel = $_REQUEST['user_tel'] : $user_tel = '';
isset($_REQUEST['user_img_name']) ? $user_img_name = $_REQUEST['user_img_name'] : $user_img_name = '';
isset($_REQUEST['submit']) ? $submit = $_REQUEST['submit'] : $submit = '';
isset($_REQUEST['module']) ? $module = $_REQUEST['module'] : $module = '';
isset($_REQUEST['user_id']) ? $user_id = $_REQUEST['user_id'] : $user_id = '';
isset($_REQUEST['uid']) ? $uid = $_REQUEST['uid'] : $uid = '';

if ($submit) {
    if ($submit == 'Save') {
        // Insert Data 
        if ($_FILES["user_img_name"]["name"] != "") {
            if (copy($_FILES["user_img_name"]["tmp_name"], "../../images/users/" . $_FILES["user_img_name"]["name"])) {
                $salt = sha1(rand());
                $salt = substr($salt, 0, 10);
                $encrypted = base64_encode(sha1($user_password . $salt, true) . $salt);
                $hash = array("salt" => $salt, "encrypted" => $encrypted);
                $encrypted_password = $hash["encrypted"];
                $encrypted_salt = $hash["salt"];
                $strsave = "insert into user(user_first_name,user_last_name,user_email,user_password,user_salt,user_tel,user_img_name)";
                $strsave = $strsave . " values('$user_first_name','$user_last_name','$user_email','$encrypted_password','$encrypted_salt','$user_tel','" . $_FILES['user_img_name']["name"] . "')";
                $objsave = mysql_query($strsave) or die("Error Query [" . $strsave . "]");
                echo "<script language='javascript'>alert('บันทึกข้อมูลสำเร็จ !!!'); </script>";
                echo "<script language='javascript'>window.location='frm_user.php';</script>";
            }
        } else {
            $salt = sha1(rand());
            $salt = substr($salt, 0, 10);
            $encrypted = base64_encode(sha1($user_password . $salt, true) . $salt);
            $hash = array("salt" => $salt, "encrypted" => $encrypted);
            $encrypted_password = $hash["encrypted"];
            $encrypted_salt = $hash["salt"];
            $strsave = "insert into user(user_first_name,user_last_name,user_email,user_password,user_salt,user_tel)";
            $strsave = $strsave . " values('$user_first_name','$user_last_name','$user_email','$encrypted_password','$encrypted_salt','$user_tel')";
            $objsave = mysql_query($strsave) or die("Error Query [" . $strsave . "]");
            echo "<script language='javascript'>alert('บันทึกข้อมูลสำเร็จ !!!'); </script>";
            echo "<script language='javascript'>window.location='frm_user.php';</script>";
        }
    }
    if ($submit == "Edit") {
        // Edit Data
        if ($_FILES["user_img_name"]["name"] != "") {
            if (move_uploaded_file($_FILES["user_img_name"]["tmp_name"], "../../images/users/" . $_FILES["user_img_name"]["name"])) {
                $strsql = "Update user set user_first_name='$user_first_name',user_last_name='$user_last_name',user_email='$user_email',user_tel='$user_tel',user_img_name='" . $_FILES['user_img_name']["name"] . "' where user_id=$uid";
                $objsql = mysql_query($strsql) or die("Error Query [" . $strsql . "]");
                echo "<script language='javascript'>alert('แก้ไขข้อมูลสำเร็จ !!!'); </script>";
                echo "<script language='javascript'>window.location='tb_user.php';</script>";
            }
        } else {
            $strsql = "Update user set user_first_name='$user_first_name',user_last_name='$user_last_name',user_email='$user_email',user_tel='$user_tel' where user_id=$uid";
            $objsql = mysql_query($strsql) or die("Error Query [" . $strsql . "]");
            echo "<script language='javascript'>alert('แก้ไขข้อมูลสำเร็จ !!!'); </script>";
            echo "<script language='javascript'>window.location='tb_user.php';</script>";
        }
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
    <style>
        input[type=file] {
            color: transparent;
        }
    </style>
    <?php require("title.php"); ?>

    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="../build/css/custom.min.css" rel="stylesheet">
    <style>@font-face {
            font-family: yourFontName ;
            src: url(../build/css/th_baijam_bold-webfont.ttf) format("truetype");
        }

        .body {
            font-family: yourFontName, serif;
        }</style>
</head>

<body style="color:black;" class="nav-md">
<div class="container body">
    <div class="main_container">
        <div class="col-md-3 left_col">
            <div class="left_col scroll-view">
                <!-- sidebar menu -->
                <?php require("sidebar.php"); ?>
                <!-- /sidebar menu -->
            </div>
        </div>

        <?php require("top_navigation.php"); ?>

        <!-- page content -->
        <div class="right_col" role="main">
            <div class="">
                <div class="page-title">
                    <div class="title_left">
                        <h3>เพิ่มข้อมูลผู้ใช้บริการ</h3>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-8 col-sm-8 col-xs-8 col-lg-offset-2">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2  style="padding: 5px">ข้อมูลส่วนตัว</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <br/>
                                <form class="form-horizontal form-label-left" method="POST" enctype="multipart/form-data" action="frm_user.php">
                                    <?php if ($module == 'Edit') { ?>
                                        <?php
                                        $stredit = "Select * from user where user_id=$user_id";
                                        $obedit = mysql_query($stredit) or die("Error Query [" . $stredit . "]");
                                        $rowedit = mysql_fetch_array($obedit);
                                        ?>

                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="อีเมล">อีเมล
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" maxlength="50" id="user_email" name="user_email"
                                                       required="required"
                                                       value="<?= $rowedit['user_email']; ?>"
                                                    class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12"
                                                   for="user_first_name">ชื่อ

                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" maxlength="50" id="user_first_name"
                                                       name="user_first_name"
                                                       value="<?= $rowedit['user_first_name']; ?>" required="required"
                                                       class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12"
                                                   for="user_last_name">นามสกุล

                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" maxlength="50" id="user_last_name"
                                                       name="user_last_name"
                                                       value="<?= $rowedit['user_last_name']; ?>" required="required"
                                                       class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="user_tel">เบอร์โทรศัพท์

                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" maxlength="10" id="user_tel" name="user_tel"
                                                       value="<?= $rowedit['user_tel']; ?>" required="required"
                                                       class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12"
                                                   for="user_last_name">รูปโปรไฟล์

                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <img
                                                    src="../../images/users/<?php echo $rowedit["user_img_name"]; ?>"
                                                    class="img-thumbnail" style="min-height:80px;height:80px;"></div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12"
                                                   for="user_img_name">แก้ไขรูปโปรไฟล์

                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input style="padding-top: 7px" type='file' name="user_img_name"
                                                       title="Choose a video please" id="aa"
                                                       onchange="pressed();readURL(this);">
                                                <label id="fileLabel"></label>
                                                <br/>
                                                <img src="#" width="140px" id="blah" height="140px" alt="..."
                                                     class="img-thumbnail">
                                            </div>
                                        </div>
                                        <div class="ln_solid"></div>
                                        <div class="form-group" align="center">
                                            <input type="hidden" name="uid" value="<?php echo $rowedit["user_id"]; ?>"/>
                                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                                <button type="reset" name="reset" value="Cancel"
                                                        class="btn btn-info"><i
                                                        class="fa fa-refresh fa-fw"></i>ล้างข้อมูล
                                                </button>
                                                <button type="submit" name="submit" value="Edit"
                                                        class="btn btn-success"><i
                                                        class="fa fa-floppy-o fa-fw"></i>บันทึกข้อมูล
                                                </button>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="อีเมล">อีเมล
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="user_email" name="user_email" required=""
                                                       maxlength="50"
                                                       class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12"
                                                   for="user_password">รหัสผ่าน
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="password" id="user_password" name="user_password"
                                                       maxlength="16" required="required"
                                                       class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12"
                                                   for="user_first_name">ชื่อ

                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="user_first_name" name="user_first_name"
                                                       required="required" maxlength="50"
                                                       class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label  col-md-3 col-sm-3 col-xs-12"
                                                   for="user_last_name">นามสกุล

                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="user_last_name" name="user_last_name"
                                                       required="required" maxlength="50"
                                                       class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="user_tel">เบอร์โทรศัพท์

                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="user_tel" name="user_tel"
                                                       required="required" maxlength="10"
                                                       class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12"
                                                   for="user_img_name">ใส่รูปโปรไฟล์ </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12 ">
                                                <input style="padding-top: 7px;font-family: yourFontName"   type='file' name="user_img_name"
                                                       title="Choose a video please" id="aa"
                                                       onchange="pressed();readURL(this);">
                                                <label style="font-family: yourFontName" id="fileLabel"></label>
                                                <br/>
                                                <img src="#" width="140px" id="blah" height="140px" alt="..."
                                                     class="img-thumbnail">
                                            </div>
                                        </div>
                                        <div class="ln_solid"></div>
                                        <div class="form-group" align="center">
                                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                                <button type="reset" name="reset" value="Cancel"
                                                        class="btn btn-info"><i
                                                        class="fa fa-refresh fa-fw"></i>ล้างข้อมูล
                                                </button>
                                                <button type="submit" name="submit" value="Save"
                                                        class="btn btn-success"><i
                                                        class="fa fa-floppy-o fa-fw"></i>บันทึกข้อมูล
                                                </button>

                                            </div>
                                        </div>
                                    <?php } ?>
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

<script src="../vendors/jquery/dist/jquery.min.js"></script>
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../build/js/custom.min.js"></script>

<script>
    $(document).ready(function () {
        if ($('#blah').attr('src') == '#') {
            $('#blah').hide();
        }

    });</script>
<script>
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#blah')
                    .attr('src', e.target.result)
                    .width(150)
                    .height(150)
                    .show();
            };


            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<script>
    window.pressed = function () {
        var a = document.getElementById('aa');
        if (a.value == "") {
            fileLabel.innerHTML = "";
        } else {
            var theSplit = a.value.split('\\');
            fileLabel.innerHTML = theSplit[theSplit.length - 1];
        }
    };</script>
</body>
</html>