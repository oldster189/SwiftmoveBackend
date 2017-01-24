<?php
include('session.php');
include('connection.php');

isset($_REQUEST['email']) ? $email = $_REQUEST['email'] : $email = '';
isset($_REQUEST['password']) ? $password = $_REQUEST['password'] : $password = '';
isset($_REQUEST['fname']) ? $fname = $_REQUEST['fname'] : $fname = '';
isset($_REQUEST['lname']) ? $lname = $_REQUEST['lname'] : $lname = '';
isset($_REQUEST['tel']) ? $tel = $_REQUEST['tel'] : $tel = '';
isset($_REQUEST['id_card']) ? $id_card = $_REQUEST['id_card'] : $id_card = '';
isset($_REQUEST['gender']) ? $gender = $_REQUEST['gender'] : $gender = '';
isset($_REQUEST['province']) ? $province = $_REQUEST['province'] : $province = '';
isset($_REQUEST['address']) ? $address = $_REQUEST['address'] : $address = '';
isset($_REQUEST['type_car']) ? $type_car = $_REQUEST['type_car'] : $type_car = '';
isset($_REQUEST['brand_car']) ? $brand_car = $_REQUEST['brand_car'] : $brand_car = '';
isset($_REQUEST['model_car']) ? $model_car = $_REQUEST['model_car'] : $model_car = '';
isset($_REQUEST['color_car']) ? $color_car = $_REQUEST['color_car'] : $color_car = '';
isset($_REQUEST['province_plate']) ? $province_plate = $_REQUEST['province_plate'] : $province_plate = '';
isset($_REQUEST['plate_car']) ? $plate_car = $_REQUEST['plate_car'] : $plate_car = '';
isset($_REQUEST['start_km']) ? $start_km = $_REQUEST['start_km'] : $start_km = '';
isset($_REQUEST['start_price']) ? $start_price = $_REQUEST['start_price'] : $start_price = '';
isset($_REQUEST['rate_price']) ? $rate_price = $_REQUEST['rate_price'] : $rate_price = '';
isset($_REQUEST['lift_status']) ? $lift_status = $_REQUEST['lift_status'] : $lift_status = '';
isset($_REQUEST['lift_price']) ? $lift_price = $_REQUEST['lift_price'] : $lift_price = '';
isset($_REQUEST['lift_plus_status']) ? $lift_plus_status = $_REQUEST['lift_plus_status'] : $lift_plus_status = '';
isset($_REQUEST['lift_plus_price']) ? $lift_plus_price = $_REQUEST['lift_plus_price'] : $lift_plus_price = '';
isset($_REQUEST['cart_status']) ? $cart_status = $_REQUEST['cart_status'] : $cart_status = '';
isset($_REQUEST['cart_price']) ? $cart_price = $_REQUEST['cart_price'] : $cart_price = '';

isset($_REQUEST['module']) ? $module = $_REQUEST['module'] : $module = '';
isset($_REQUEST['submit']) ? $submit = $_REQUEST['submit'] : $submit = '';
isset($_REQUEST['did']) ? $did = $_REQUEST['did'] : $did = '';
isset($_REQUEST['driver_id']) ? $driver_id = $_REQUEST['driver_id'] : $driver_id = '';

if ($submit) {
    if ($submit == "Save") {
        if ($_FILES["driver_img_name"]["name"] != "") {
            if (copy($_FILES["driver_img_name"]["tmp_name"], "../../images/driver/" . $_FILES["driver_img_name"]["name"])) {
                //บันทึกข้อมูล
                $salt = sha1(rand());
                $salt = substr($salt, 0, 10);
                $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
                $hash = array("salt" => $salt, "encrypted" => $encrypted);
                $encrypted_password = $hash["encrypted"];
                $encrypted_salt = $hash["salt"];
                $strsave = "insert into driver(driver_first_name,driver_last_name,driver_email,driver_password,driver_salt,driver_tel,";
                $strsave .= "driver_id_card,driver_address,driver_sex,driver_province,driver_img_name) ";
                $strsave .= "values('" . $fname . "','" . $lname . "','" . $email . "','" . $encrypted_password . "','" . $encrypted_salt . "','" . $tel . "',";
                $strsave .= "'" . $id_card . "','" . $address . "','" . $gender . "','" . $province . "','" . $_FILES['driver_img_name']["name"] . "')";
                $objsave = mysql_query($strsave) or die("Error Query [" . $strsave . "]");
            }
        } else {
            //บันทึกข้อมูล
            $salt = sha1(rand());
            $salt = substr($salt, 0, 10);
            $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
            $hash = array("salt" => $salt, "encrypted" => $encrypted);
            $encrypted_password = $hash["encrypted"];
            $encrypted_salt = $hash["salt"];
            $strsave = "insert into driver(driver_first_name,driver_last_name,driver_email,driver_password,driver_salt,driver_tel,";
            $strsave .= "driver_id_card,driver_address,driver_sex,driver_province) ";
            $strsave .= "values('" . $fname . "','" . $lname . "','" . $email . "','" . $encrypted_password . "','" . $encrypted_salt . "','" . $tel . "',";
            $strsave .= "'" . $id_card . "','" . $address . "','" . $gender . "','" . $province . "')";
            $objsave = mysql_query($strsave) or die("Error Query [" . $strsave . "]");
        }
        $driver_id = mysql_insert_id();
        if ($lift_status == "on") {
            $lift_status = "t";
        } else {
            $lift_status = "f";
        }
        if ($lift_plus_status == "on") {
            $lift_plus_status = "t";
        } else {
            $lift_plus_status = "f";
        }
        if ($cart_status == "on") {
            $cart_status = "t";
        } else {
            $cart_status = "f";
        }
        $strsave = "";
        $strsave = "insert into driver_detail(driver_detail_type,driver_detail_brand,driver_detail_model,driver_detail_color,";
        $strsave .= "driver_detail_license_plate,driver_detail_province_license_plate,driver_detail_service_lift_status,";
        $strsave .= "driver_detail_service_lift_price,driver_detail_service_lift_plus_status,driver_detail_service_lift_plus_price,";
        $strsave .= "driver_detail_service_cart_status,driver_detail_service_cart_price,driver_detail_charge_start_price,";
        $strsave .= "driver_detail_charge_start_km,driver_detail_charge,driver_id) ";
        $strsave .= "values('" . $type_car . "','" . $brand_car . "','" . $model_car . "','" . $color_car . "','" . $plate_car . "','" . $province_plate . "',";
        $strsave .= "'" . $lift_status . "','" . $lift_price . "','" . $lift_plus_status . "','" . $lift_plus_price . "','" . $cart_status . "','" . $cart_price . "',";
        $strsave .= "'" . $start_km . "','" . $start_price . "','" . $rate_price . "','" . $driver_id . "')";
        $objsave = mysql_query($strsave) or die("Error Query [" . $strsave . "]");
        echo "<script language='javascript'>alert('บันทึกข้อมูลสำเร็จ !!!'); </script>";
        echo "<script language='javascript'>window.location='frm_driver.php';</script>";

    }
    if ($submit == "Edit") {
        // Edit Data
        if ($_FILES["driver_img_name"]["name"] != "") {
            if (move_uploaded_file($_FILES["driver_img_name"]["tmp_name"], "../../images/driver/" . $_FILES["driver_img_name"]["name"])) {
                $strsql = "Update driver set driver_first_name='$fname',driver_last_name='$lname',";
                $strsql .= "driver_email='$email',driver_tel='$tel',driver_id_card='$id_card',driver_address='$address',";
                $strsql .= "driver_sex='$gender',driver_province='$province',driver_img_name='" . $_FILES['driver_img_name']["name"] . "'";
                $strsql .= " where driver_id=$did";
                $objsql = mysql_query($strsql) or die("Error Query [" . $strsql . "]");
            }
        } else {
            $strsql = "Update driver set driver_first_name='$fname',driver_last_name='$lname',";
            $strsql .= "driver_email='$email',driver_tel='$tel',driver_id_card='$id_card',driver_address='$address',";
            $strsql .= "driver_sex='$gender',driver_province='$province'";
            $strsql .= " where driver_id=$did";
            $objsql = mysql_query($strsql) or die("Error Query [" . $strsql . "]");
        }
        if ($lift_status == "on") {
            $lift_status = "t";
        } else {
            $lift_status = "f";
        }
        if ($lift_plus_status == "on") {
            $lift_plus_status = "t";
        } else {
            $lift_plus_status = "f";
        }
        if ($cart_status == "on") {
            $cart_status = "t";
        } else {
            $cart_status = "f";
        }
        $strsql = "Update driver_detail set driver_detail_type='$type_car',driver_detail_brand='$brand_car',";
        $strsql .= "driver_detail_model='$model_car',driver_detail_color='$color_car',driver_detail_license_plate='$plate_car',";
        $strsql .= "driver_detail_province_license_plate='$province_plate',driver_detail_service_lift_status='$lift_status',";
        $strsql .= "driver_detail_service_lift_price='$lift_price',driver_detail_service_lift_plus_status='$lift_plus_status',";
        $strsql .= "driver_detail_service_lift_plus_price='$lift_plus_price',driver_detail_service_cart_status='$cart_status',";
        $strsql .= "driver_detail_service_cart_price='$cart_price',driver_detail_charge_start_price='$start_price',";
        $strsql .= "driver_detail_charge_start_km='$start_km',driver_detail_charge='$rate_price' ";
        $strsql .= " where driver_id=$did";
        $objsql = mysql_query($strsql) or die("Error Query [" . $strsql . "]");
        echo "<script language='javascript'>alert('แก้ไขข้อมูลสำเร็จ !!!'); </script>";
        echo "<script language='javascript'>window.location='tb_driver.php';</script>";

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
    <style>
        input[type=file] {
            color: transparent;
        }
    </style>
    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="../vendors/select2/dist/css/select2.min.css" rel="stylesheet">
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

        <!-- top navigation -->
        <?php require("top_navigation.php"); ?>
        <!-- /top navigation -->


        <!-- page content -->
        <div class="right_col" role="main">
            <div class="">
                <div class="page-title">
                    <div class="title_left">
                        <h3 >เพิ่มข้อมูลผู้ให้บริการ</h3>
                    </div>
                </div>
                <div class="clearfix"></div>
                <form data-parsley-validate class="form-horizontal form-label-left" method="POST"
                      enctype="multipart/form-data" action="frm_driver.php">
                    <?php
                    if ($module == 'Edit') {
                        $stredit = "select * from view_data_driver where driver_id=$driver_id";
                        $objedit = mysql_query($stredit) or die("Error Query [" . $stredit . "]");
                        $rowsedit = mysql_fetch_array($objedit);
                        //echo $rowsedit['id_fruit'];
                        ?>
                        <div class="row">
                            <div class="col-md-8 col-sm-8 col-xs-8 col-lg-offset-2">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2  style="padding: 5px">ข้อมูลส่วนตัว</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">
                                        <br/>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="panel-body">
                                                    <fieldset>
                                                        <div class="form-group">
                                                            <label>อีเมล</label>
                                                            <input class="form-control" maxlength="50" type="text"
                                                                   name="email"
                                                                   value="<?= $rowsedit['driver_email']; ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ชื่อ</label>
                                                            <input class="form-control" maxlength="50" type="text"
                                                                   name="fname"
                                                                   value="<?= $rowsedit['driver_first_name']; ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>นามสกุล</label>
                                                            <input class="form-control" maxlength="50" type="text"
                                                                   name="lname"
                                                                   value="<?= $rowsedit['driver_last_name']; ?>">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>เบอร์โทรศัพท์</label>
                                                            <input class="form-control" maxlength="10" type="text"
                                                                   name="tel"
                                                                   value="<?= $rowsedit['driver_tel']; ?>">
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                            <div class="col-md-6 ">
                                                <div class="panel-body">
                                                    <fieldset>

                                                        <div class="form-group">
                                                            <label>เลขบัตรประชาชน</label>
                                                            <input class="form-control" maxlength="13" type="text"
                                                                   name="id_card"
                                                                   value="<?= $rowsedit['driver_id_card']; ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>เพศ</label><br/>
                                                            <?php
                                                            $chk_gender = $rowsedit['driver_sex'];
                                                            if ($chk_gender == "M") {
                                                                ?>

                                                                <label class="radio-inline">
                                                                    <input type="radio" name="gender"
                                                                           checked id="inlineRadio1" value="M"> ชาย
                                                                </label>
                                                                <label class="radio-inline">
                                                                    <input type="radio" name="gender"
                                                                           id="inlineRadio2" value="F"> หญิง
                                                                </label>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <label class="radio-inline">
                                                                    <input type="radio" name="gender"
                                                                           id="inlineRadio1" value="M"> ชาย
                                                                </label>
                                                                <label class="radio-inline">
                                                                    <input type="radio" name="gender"
                                                                           checked id="inlineRadio2" value="F"> หญิง
                                                                </label>

                                                                <?php
                                                            }
                                                            ?>

                                                        </div>
                                                        <div style="padding-top: 7px" class="form-group">
                                                            <label>พื้นที่ให้บริการ</label>
                                                            <?php
                                                            $pid = $rowsedit['driver_province'];
                                                            $sql2 = "SELECT * from province WHERE province_id=$pid";
                                                            $obj2 = mysql_query($sql2) or die("Error Query [" . $sql2 . "]");
                                                            $rows2 = mysql_fetch_array($obj2);
                                                            ?>
                                                            <select class="select2_single form-control" name="province">
                                                                <option
                                                                    value="<?= $rows2['province_id']; ?>"><?= $rows2['province_name']; ?></option>
                                                                <?php
                                                                $sql = "SELECT * FROM province";
                                                                $objedit = mysql_query($sql) or die("Error Query [" . $stredit . "]");
                                                                while ($rowg = mysql_fetch_array($objedit)) {
                                                                    ?>
                                                                    <option
                                                                        value="<?= $rowg['province_id'] ?>"> <?= $rowg['province_name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ที่อยู่</label>
                                                            <textarea class="form-control" rows="3"
                                                                      name="address"> <?= $rowsedit['driver_address']; ?> </textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="as">รูปโปรไฟล์</label>
                                                            <div class="col-md-12col-sm-12 col-xs-12">
                                                                <img
                                                                    src="../../images/driver/<?= $rowsedit['driver_img_name'] ?>"
                                                                    class="img-thumbnail"
                                                                    style="min-height:80px;height:80px;"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="as">แก้ไขรูปโปรไฟล์</label>
                                                            <input id="aa" type="file" name="driver_img_name"
                                                                   onchange="pressed();readURL(this);">
                                                            <label id="fileLabel"></label>
                                                            <br/> <img src="#" width="140px" id="blah" height="140px"
                                                                       alt="..." class="img-thumbnail">

                                                        </div>

                                                    </fieldset>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 col-sm-8 col-xs-8 col-lg-offset-2">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2  style="padding: 5px">รายละเอียดรถที่ให้บริการ</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">
                                        <br/>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="panel-body">
                                                    <fieldset>
                                                        <div class="form-group">
                                                            <label>ประเภทรถ</label>
                                                            <select class="form-control" name="type_car">
                                                                <option value="Pickup">กระบะ</option>
                                                                <option value="Truck">กระบะทึบ</option>
                                                                <option value="EcoCar">รถ 5 ประตู</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ยี่ห้อรถ</label>
                                                            <input maxlength="20" class="form-control" type="text"
                                                                   name="brand_car"
                                                                   value="<?= $rowsedit['driver_detail_brand']; ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>รุ่นรถ</label>
                                                            <input maxlength="20" class="form-control" type="text"
                                                                   name="model_car"
                                                                   value="<?= $rowsedit['driver_detail_model']; ?>">
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                            <div class="col-md-6 ">
                                                <div class="panel-body">
                                                    <fieldset>
                                                        <div class="form-group">
                                                            <label>สีรถ</label>
                                                            <input maxlength="20" class="form-control" type="text"
                                                                   name="color_car"
                                                                   value="<?= $rowsedit['driver_detail_color']; ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>จังหวัดป้ายทะเบียนรถ</label>
                                                            <?php
                                                            $pid = $rowsedit['driver_detail_province_license_plate'];
                                                            $sql2 = "SELECT * from province WHERE province_id=$pid";
                                                            $obj2 = mysql_query($sql2) or die("Error Query [" . $sql2 . "]");
                                                            $rows2 = mysql_fetch_array($obj2);
                                                            ?>
                                                            <select class="select2_single form-control"
                                                                    name="province_plate">
                                                                <option
                                                                    value="<?= $rows2['province_id']; ?>"><?= $rows2['province_name']; ?></option>
                                                                <?php
                                                                $sql = "SELECT * FROM province";
                                                                $objedit = mysql_query($sql) or die("Error Query [" . $stredit . "]");
                                                                while ($rowg = mysql_fetch_array($objedit)) {
                                                                    ?>
                                                                    <option
                                                                        value="<?= $rowg['province_id'] ?>"> <?= $rowg['province_name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ป้ายทะเบียนรถ</label>
                                                            <input maxlength="20" class="form-control" type="text"
                                                                   name="plate_car"
                                                                   value="<?= $rowsedit['driver_detail_license_plate']; ?>">
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>

                                        </div>


                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 col-sm-8 col-xs-8 col-lg-offset-2">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2  style="padding: 5px">รายละเอียดค่าบริการ</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">
                                        <br/>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="panel-body">
                                                    <fieldset>
                                                        <div class="form-group">
                                                            <label>ระยะทางเริ่มต้น</label>
                                                            <input class="form-control" type="text"
                                                                   maxlength="4" placeholder="กิโลเมตร"
                                                                   name="start_km"
                                                                   value="<?= $rowsedit['driver_detail_charge_start_km']; ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ค่าบริการเริ่มต้น</label>
                                                            <input class="form-control" type="text" placeholder="บาท"
                                                                   maxlength="4" name="start_price"
                                                                   value="<?= $rowsedit['driver_detail_charge_start_price']; ?>">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ค่าบริการตามระยะทาง</label>
                                                            <input class="form-control" type="text"
                                                                   maxlength="4" placeholder="บาท/กิโลเมตร"
                                                                   name="rate_price"
                                                                   value="<?= $rowsedit['driver_detail_charge']; ?>">
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="panel-body">
                                                    <fieldset>
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <?php
                                                                    $chk_lift = $rowsedit['driver_detail_service_lift_status'];
                                                                    echo "<script>console.log( 'Debug Objects: " . $chk_lift . "' );</script>";
                                                                    if ($chk_lift == "t") {
                                                                        echo "<script>console.log( 'Debug Objects1: " . $chk_lift . "' );</script>";
                                                                        ?>

                                                                        <input name="lift_status" id="lift_status"
                                                                               checked
                                                                               type="checkbox"> คนขับช่วยยกของ
                                                                        <input
                                                                            class="form-control" placeholder="บาท"
                                                                            maxlength="4" id="lift_price" type="text"
                                                                            name="lift_price"
                                                                            value="<?= $rowsedit['driver_detail_service_lift_price']; ?>">
                                                                    <?php } else {
                                                                        echo "<script>console.log( 'Debug Objects2: " . $chk_lift . "' );</script>";
                                                                        ?>

                                                                        <input name="lift_status" id="lift_status"
                                                                               type="checkbox"> คนขับช่วยยกของ
                                                                        <input
                                                                            maxlength="4" class="form-control"
                                                                            placeholder="บาท"
                                                                            disabled id="lift_price" type="text"
                                                                            name="lift_price">
                                                                    <?php }
                                                                    ?>

                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <?php
                                                                    $chk_lift_plus = $rowsedit['driver_detail_service_lift_plus_status'];
                                                                    if ($chk_lift_plus == "t") {
                                                                        ?>
                                                                        <input name="lift_plus_status"
                                                                               id="lift_plus_status" checked
                                                                               type="checkbox"> ผู้ช่วยช่วยยกของ
                                                                        <input
                                                                            class="form-control" placeholder="บาท"
                                                                            maxlength="4" id="lift_price_price"
                                                                            type="text"
                                                                            name="lift_price_price"
                                                                            value="<?= $rowsedit['driver_detail_service_lift_plus_price']; ?>">
                                                                    <?php } else {
                                                                        ?>
                                                                        <input name="lift_plus_status"
                                                                               id="lift_plus_status"
                                                                               type="checkbox"> ผู้ช่วยช่วยยกของ
                                                                        <input
                                                                            maxlength="4" class="form-control"
                                                                            placeholder="บาท"
                                                                            disabled id="lift_price_price" type="text"
                                                                            name="lift_price_price">
                                                                    <?php }
                                                                    ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <?php
                                                                    $chk_cart = $rowsedit['driver_detail_service_cart_status'];
                                                                    if ($chk_cart == "t") {
                                                                        ?>
                                                                        <input name="cart_status" id="cart_status"
                                                                               checked
                                                                               type="checkbox"> รถเข็น
                                                                        <input
                                                                            class="form-control" placeholder="บาท"
                                                                            maxlength="4" id="cart_price" type="text"
                                                                            name="cart_price"
                                                                            value="<?= $rowsedit['driver_detail_service_cart_price']; ?>">
                                                                    <?php } else {
                                                                        ?>

                                                                        <input name="cart_status" id="cart_status"
                                                                               type="checkbox"> รถเข็น
                                                                        <input
                                                                            maxlength="4" class="form-control"
                                                                            placeholder="บาท"
                                                                            disabled id="cart_price" type="text"
                                                                            name="cart_price">
                                                                    <?php }
                                                                    ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="ln_solid"></div>
                                            <div align="center">
                                                <input type="hidden" name="did" value="<?= $rowsedit['driver_id']; ?>">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="row">
                            <div class="col-md-8 col-sm-8 col-xs-8 col-lg-offset-2">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2  style="padding: 5px">ข้อมูลส่วนตัว</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">
                                        <br/>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="panel-body">
                                                    <fieldset>
                                                        <div class="form-group">
                                                            <label>อีเมล</label>
                                                            <input class="form-control" maxlength="50" type="text"
                                                                   name="email">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>รหัสผ่าน</label>
                                                            <input class="form-control" maxlength="16" type="password"
                                                                   name="password">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ชื่อ</label>
                                                            <input class="form-control" maxlength="50" type="text"
                                                                   name="fname">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>นามสกุล</label>
                                                            <input class="form-control" maxlength="50" type="text"
                                                                   name="lname">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>เบอร์โทรศัพท์</label>
                                                            <input class="form-control" maxlength="10" type="text"
                                                                   name="tel">
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                            <div class="col-md-6 ">
                                                <div class="panel-body">
                                                    <fieldset>

                                                        <div class="form-group">
                                                            <label>เลขบัตรประชาชน</label>
                                                            <input class="form-control" maxlength="13" type="text"
                                                                   name="id_card">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>เพศ</label><br/>
                                                            <label class="radio-inline">
                                                                <input type="radio" name="gender"
                                                                       checked id="inlineRadio1" value="M"> ชาย
                                                            </label>
                                                            <label class="radio-inline">
                                                                <input type="radio" name="gender"
                                                                       id="inlineRadio2" value="F"> หญิง
                                                            </label>
                                                        </div>
                                                        <div style="padding-top: 7px" class="form-group">
                                                            <label>พื้นที่ให้บริการ</label>
                                                            <select class="select2_single form-control" name="province">
                                                                <?php
                                                                $sql = "SELECT * FROM province";
                                                                $objedit = mysql_query($sql) or die("Error Query [" . $stredit . "]");
                                                                while ($rowg = mysql_fetch_array($objedit)) {
                                                                    ?>
                                                                    <option
                                                                        value="<?= $rowg['province_id'] ?>"> <?= $rowg['province_name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ที่อยู่</label>
                                                            <textarea class="form-control" rows="3"
                                                                      name="address"></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="as">ใส่รูปโปรไฟล์</label>
                                                            <input id="aa" type="file" name="driver_img_name"
                                                                   onchange="pressed();readURL(this);">
                                                            <label id="fileLabel"></label>
                                                            <br/> <img src="#" width="140px" id="blah" height="140px"
                                                                       alt="..." class="img-thumbnail">

                                                        </div>

                                                    </fieldset>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 col-sm-8 col-xs-8 col-lg-offset-2">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2  style="padding: 5px">รายละเอียดรถที่ให้บริการ</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">
                                        <br/>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="panel-body">
                                                    <fieldset>
                                                        <div class="form-group">
                                                            <label>ประเภทรถ</label>
                                                            <select class="form-control" name="type_car">
                                                                <option value="Pickup">กระบะ</option>
                                                                <option value="Truck">กระบะทึบ</option>
                                                                <option value="EcoCar">รถ 5 ประตู</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ยี่ห้อรถ</label>
                                                            <input class="form-control" maxlength="20" type="text"
                                                                   name="brand_car">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>รุ่นรถ</label>
                                                            <input class="form-control" maxlength="20" type="text"
                                                                   name="model_car">
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                            <div class="col-md-6 ">
                                                <div class="panel-body">
                                                    <fieldset>
                                                        <div class="form-group">
                                                            <label>สีรถ</label>
                                                            <input class="form-control" maxlength="20" type="text"
                                                                   name="color_car">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>จังหวัดป้ายทะเบียนรถ</label>
                                                            <select class="select2_single form-control"
                                                                    name="province_plate">
                                                                <?php
                                                                $sql = "SELECT * FROM province";
                                                                $objedit = mysql_query($sql) or die("Error Query [" . $stredit . "]");
                                                                while ($rowg = mysql_fetch_array($objedit)) {
                                                                    ?>
                                                                    <option
                                                                        value="<?= $rowg['province_id'] ?>"> <?= $rowg['province_name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ป้ายทะเบียนรถ</label>
                                                            <input class="form-control" maxlength="20" type="text"
                                                                   name="plate_car">
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>

                                        </div>


                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 col-sm-8 col-xs-8 col-lg-offset-2">
                                <div class="x_panel">
                                    <div class="x_title">
                                        <h2  style="padding: 5px">รายละเอียดค่าบริการ</h2>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">
                                        <br/>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="panel-body">
                                                    <fieldset>
                                                        <div class="form-group">
                                                            <label>ระยะทางเริ่มต้น</label>
                                                            <input class="form-control" type="text"
                                                                   maxlength="4" placeholder="กิโลเมตร"
                                                                   name="start_km">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ค่าบริการเริ่มต้น</label>
                                                            <input maxlength="4" class="form-control" type="text"
                                                                   placeholder="บาท"
                                                                   name="start_price">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>ค่าบริการตามระยะทาง</label>
                                                            <input maxlength="4" class="form-control" type="text"
                                                                   placeholder="บาท/กิโลเมตร"
                                                                   name="rate_price">
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="panel-body">
                                                    <fieldset>
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="lift_status" id="lift_status"
                                                                           type="checkbox"> คนขับช่วยยกของ
                                                                    <input maxlength="4"
                                                                           class="form-control" placeholder="บาท"
                                                                           disabled id="lift_price" type="text"
                                                                           name="lift_price">
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="lift_plus_status" id="lift_plus_status"
                                                                           type="checkbox"> ผู้ช่วยช่วยยกของ<input
                                                                        maxlength="4"
                                                                        class="form-control" type="text"
                                                                        placeholder="บาท"
                                                                        disabled
                                                                        name="lift_plus_price" id="lift_plus_price">
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input name="cart_status" id="cart_status"
                                                                           type="checkbox"> รถเข็น<input maxlength="4"
                                                                                                         class="form-control"
                                                                                                         type="text"
                                                                                                         placeholder="บาท"
                                                                                                         disabled
                                                                                                         name="cart_price"
                                                                                                         id="cart_price">
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </fieldset>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="ln_solid"></div>
                                            <div align="center">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
        <!-- /page content -->

        <?php require("footer.php"); ?>
        <!-- /footer content -->
    </div>
</div>

<!-- jQuery -->
<script src="../vendors/jquery/dist/jquery.min.js"></script>
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../vendors/select2/dist/js/select2.full.min.js"></script>
<script src="../build/js/custom.min.js"></script>

<!-- Select2 -->
<script>
    $(document).ready(function () {
        $(".select2_single").select2({
            placeholder: "Select a state",
            allowClear: true
        });
        $(".select2_group").select2({});
        $(".select2_multiple").select2({
            maximumSelectionLength: 4,
            placeholder: "With Max Selection limit 4",
            allowClear: true
        });
    });
</script>

<script>
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#blah')
                    .attr('src', e.target.result)
                    .width(150)
                    .height(150);
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
<script>
    $(document).ready(function () {

        $("#lift_status").click(enable_cb_lift);
        $("#lift_plus_status").click(enable_cb_liftplus);
        $("#cart_status").click(enable_cb_cart);
    });

    function enable_cb_lift() {
        if (this.checked) {
            console.log('Debug Objects3: ');
            $("#lift_price").removeAttr("disabled");
        } else {
            console.log('Debug Objects4:  ');
            $("#lift_price").attr("disabled", true);
        }
    }
    function enable_cb_liftplus() {
        if (this.checked) {
            $("#lift_plus_price").removeAttr("disabled");
        } else {
            $("#lift_plus_price").attr("disabled", true);
        }
    }
    function enable_cb_cart() {
        if (this.checked) {
            $("#cart_price").removeAttr("disabled");
        } else {
            $("#cart_price").attr("disabled", true);
        }
    }
</script>
</body>
</html>