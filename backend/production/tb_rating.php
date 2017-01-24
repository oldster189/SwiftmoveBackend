<?php
include('session.php');
include('connection.php');
$sort_options = array('asc', 'desc');
if (!isset($_GET['field'])) {
    $_GET['field'] = 'desc';
}

$full_query_sort = $_GET['field'];
if (!in_array($full_query_sort, $sort_options)) {
    die('invalid selection');
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

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css"
          rel="stylesheet">
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="http://cdnjs.cloudflare.com/ajax/libs/x-editable/1.4.4/bootstrap-editable/css/bootstrap-editable.css"
          rel="stylesheet">
    <link href="libs/bootstrap-filterable.css" rel="stylesheet" type="text/css">
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
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>รายงานข้อมูล คะแนนแยกตามรายบุคคล</h2>
                                <div class="nav navbar-right panel_toolbox  "style="padding-top: 10px">
                                    <span style="font-size: medium;color: #000000">เรียงตามคะแนนเฉลี่ยจาก :</span>
                                    <select id='field_select'  style="font-family: yourFontName;width:130px"
                                            name='field'
                                            onchange="window.location = '?field=' + this.value">
                                        <option
                                            value='desc' <?php if (!isset($_GET['field']) || $_GET['field'] == 'desc') {
                                            echo "selected";
                                        } ?>>มาก -> น้อย
                                        </option>
                                        <option
                                            value='asc' <?php if (isset($_GET['field']) && $_GET['field'] == 'asc') {
                                            echo "selected";
                                        } ?>>น้อย -> มาก
                                        </option>
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                            </div>

                            <div class="x_content" >
                                <table id="datatable" class="table table-striped  table-bordered">
                                    <p class="text-muted font-13 m-b-30">
                                        <code style="font-family: yourFontName">ค้นหาข้อมูลที่ต้องการกดรูป <i class="fa fa-filter"></i>
                                            ที่หัวข้อตาราง</code>
                                    </p>
                                    <thead>
                                    <tr>
                                        <th style="vertical-align: middle;text-align: center;">#</th>
                                        <th style="vertical-align: middle;text-align: center;">รูปประจำตัว</th>
                                        <th style="vertical-align: middle;text-align: center;">ผู้ให้บริการ</th>
                                        <th style="vertical-align: middle;text-align: center;">คะแนนเฉลี่ย</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $sql = "SELECT * ,AVG(job_rating) as result, COUNT(*) as count_rate FROM view_data_job  WHERE job_rating IS NOT NULL GROUP BY driver_id ORDER BY result " . $full_query_sort;

                                    $objedit = mysql_query($sql) or die("Error Query [" . $stredit . "]");
                                    $i = 1;
                                    while ($rowg = mysql_fetch_array($objedit)) {
                                        ?>
                                        <tr>
                                            <td width="5%"
                                                style="vertical-align: middle;text-align: center;"><?= /* str_pad($rowg['comment_id'], 6, '0', STR_PAD_LEFT); */
                                                $i;
                                                $i++; ?></td>
                                            <td width="17%" style="vertical-align: middle;text-align: center;"><?php
                                                if ($rowg['driver_img_name'] != "") {
                                                    ?><img src="../../images/driver/<?= $rowg['driver_img_name'] ?>"
                                                           class="img-thumbnail"
                                                           style="min-height:80px;height:80px;"><?php } else {
                                                    ?>ไม่มีรูปประจำตัว<?php } ?></td>
                                            <td width="20%"
                                                style="vertical-align: middle;text-align: center;"><?= $rowg['driver_first_name'] ?> <?= $rowg['driver_last_name'] ?></td>
                                            <td width="20%"
                                                style="vertical-align: middle;text-align: center;"><?= number_format($rowg['result'], 2, '.', ''); ?> (<?=$rowg['count_rate'];?>)</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                                <button style="font-family: yourFontName" class='btn btn-default start'>หน้าแรก</button>
                                <button style="font-family: yourFontName" class='btn btn-default prev'>ก่อนหน้า</button>
                                <button style="font-family: yourFontName" class='btn btn-default next'>ถัดไป</button>
                                <button style="font-family: yourFontName" class='btn btn-default end'>หน้าสุดท้าย</button>
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
<script src="../src/paginate.js"></script>
<script src="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
<script src="libs/bootstrap-editable.min.js"></script>
<script src="../src/jquery.filterable.js"></script>
<script src="../build/js/custom.min.js"></script>
<!-- /Datatables -->
<script>
    $(document).ready(function () {
        var btns = $("#datatable").siblings('button');
        $('#datatable').paginate({
            'buttons': {
                'next': btns.filter('.next'),
                'start': btns.filter('.start'),
                'end': btns.filter('.end'),
                'prev': btns.filter('.prev')
            }
        });
    });
</script>
<script>
    $('#datatable').filterable();
</script>
</body>
</html>