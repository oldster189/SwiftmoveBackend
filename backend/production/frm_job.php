<?php
include('session.php');
include('connection.php');

isset($_REQUEST['job_id']) ? $job_id = $_REQUEST['job_id'] : $job_id = '';
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
    <link href="../build/css/custom.min.css" rel="stylesheet">
    <style>@font-face {
            font-family: yourFontName ;
            src: url(../build/css/th_baijam_bold-webfont.ttf) format("truetype");
        }

        .body {
            font-family: yourFontName, serif;
        }</style>
</head>
<body class="nav-md" style="color:black;">
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

                <div class="clearfix"></div>
                <?php
                $stredit = "select * from view_data_job where job_id=$job_id";
                $objedit = mysql_query($stredit) or die("Error Query [" . $stredit . "]");
                $rowsedit = mysql_fetch_array($objedit);
                //echo $rowsedit['id_fruit'];
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h4>หมายเลขรายการ #<?= str_pad($rowsedit['job_id'], 6, 0, 0); ?></h4>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">

                                <section class="content invoice">

                                    <div class="row invoice-info">
                                        <div class="col-sm-4 invoice-col">
                                            <strong style="font-size: medium"> ต้นทาง:</strong>
                                            <?php
                                            $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $rowsedit['job_from_latitude'] . "," . $rowsedit['job_from_longitude'] . "&sensor=true";
                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, $url);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                            curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                            $response = curl_exec($ch);
                                            if ($response === FALSE) {
                                                die('Curl failed: ' . curl_error($ch));
                                            }
                                            curl_close($ch);
                                            $response_a = json_decode($response, true);
                                            $road = $response_a['results'][0]['address_components'][0]['short_name'];
                                            $tumbon = $response_a['results'][0]['address_components'][1]['short_name'];
                                            $amphur = $response_a['results'][0]['address_components'][2]['short_name'];
                                            $province = $response_a['results'][0]['address_components'][3]['short_name'];
                                            $country = $response_a['results'][0]['address_components'][4]['long_name'];
                                            $postal_code = $response_a['results'][0]['address_components'][5]['short_name'];
                                            ?>
                                            <address>
                                                <?php echo $road; ?><br/>
                                                <?php echo $tumbon; ?><br/>
                                                <?php echo $amphur; ?><br/>
                                                <?php echo $province; ?><br/>
                                                <?php echo $country; ?><br/>
                                                <?php echo $postal_code; ?>
                                            </address>

                                        </div>
                                        <!-- /.col -->
                                        <div class="col-sm-4 invoice-col">
                                            <strong style="font-size: medium"> ปลายทาง:</strong>
                                            <?php
                                            $url2 = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $rowsedit['job_to_latitude'] . "," . $rowsedit['job_to_longitude'] . "&sensor=true";
                                            $ch2 = curl_init();
                                            curl_setopt($ch2, CURLOPT_URL, $url);
                                            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
                                            curl_setopt($ch2, CURLOPT_PROXYPORT, 3128);
                                            curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
                                            curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, 0);
                                            $response = curl_exec($ch2);
                                            if ($response === FALSE) {
                                                die('Curl failed: ' . curl_error($ch2));
                                            }
                                            curl_close($ch2);
                                            $response_a = json_decode($response, true);
                                            $road2 = $response_a['results'][0]['address_components'][0]['short_name'];
                                            $tumbon2 = $response_a['results'][0]['address_components'][1]['short_name'];
                                            $amphur2 = $response_a['results'][0]['address_components'][2]['short_name'];
                                            $province2 = $response_a['results'][0]['address_components'][3]['short_name'];
                                            $country2 = $response_a['results'][0]['address_components'][4]['long_name'];
                                            $postal_code2 = $response_a['results'][0]['address_components'][5]['short_name'];
                                            ?>
                                            <address>
                                                <?php echo $road2; ?><br/>
                                                <?php echo $tumbon2; ?><br/>
                                                <?php echo $amphur2; ?><br/>
                                                <?php echo $province2; ?><br/>
                                                <?php echo $country2; ?><br/>
                                                <?php echo $postal_code2; ?>
                                            </address>


                                        </div>
                                        <!-- /.col -->
                                        <div class="col-sm-4 invoice-col">
                                            <b>สถานะ:</b>
                                            <?php
                                            $status = $rowsedit['job_status_name'];
                                            if ($status == "ยกเลิก") {
                                                ?>
                                                <span style="padding: 7px"
                                                      class="badge badge-important"><?= str_pad($rowsedit['job_status_name'], 6, 0, 0); ?> </span>
                                            <?php } else if ($status == "รอการยืนยัน") { ?>
                                                <span style="padding: 7px"
                                                      class="badge"><?= str_pad($rowsedit['job_status_name'], 6, 0, 0); ?> </span>
                                            <?php } else if ($status == "อยู่ระหว่างดำเนินการ") { ?>
                                                <span style="padding: 7px"
                                                      class="badge badge-warning"><?= str_pad($rowsedit['job_status_name'], 6, 0, 0); ?> </span>
                                            <?php } else { ?>
                                                <span style="padding: 7px"
                                                      class="badge badge-success"><?= str_pad($rowsedit['job_status_name'], 6, 0, 0); ?> </span>
                                            <?php }
                                            ?>


                                            <br><b>เวลานัดหมาย:</b> <?= $rowsedit['job_date']; ?> <?= $rowsedit['job_time']; ?>
                                            <br>
                                            <b>ผู้ใช้บริการ:</b> <?= $rowsedit['user_first_name']; ?> <?= $rowsedit['user_last_name']; ?>
                                            <br><b>ผู้ให้บริการ:</b> <?= $rowsedit['driver_first_name']; ?> <?= $rowsedit['driver_last_name']; ?>
                                            <br>
                                            <b>ประเภทรถ:</b> <?php
                                            $type = $rowsedit['driver_detail_type'];
                                            switch ($type) {
                                                case "Pickup":
                                                    ?><?= "รถกระบะ" ?><?php
                                                    break;
                                                case "Truck":
                                                    ?>
                                                    <?= "รถกระบะตู้ทึบ" ?>
                                                    <?php
                                                    break;
                                                case "EcoCar":
                                                    ?><?= "รถ 5 ประตู" ?><?php
                                            }
                                            ?>
                                            <br>
                                            <strong>ระยะทางทั้งหมด:</strong> <?= number_format($rowsedit['job_distance'], 2, '.', ''); ?>
                                            กิโลเมตร

                                            <br/> <br/>
                                            <a class="btn btn-success"
                                               target="_blank"
                                               href="https://www.google.co.th/maps/dir/<?= $rowsedit['job_from_latitude']; ?>,<?= $rowsedit['job_from_longitude']; ?>/<?= $rowsedit['job_to_latitude']; ?>,<?= $rowsedit['job_to_longitude']; ?>"><b><i
                                                        class="fa fa-map-o"></i> แผนที่เส้นทาง</b>
                                            </a>
                                            <br>
                                        </div>
                                        <!-- /.col -->
                                    </div>
                                    <br/>
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <p class="lead">รูปภาพสิ่งของ</p>
                                            <?php
                                            $jid = $rowsedit['job_id'];
                                            $sql2 = "SELECT * FROM image_job WHERE job_id=$jid";
                                            $objedit2 = mysql_query($sql2) or die("Error Query [" . $sql2 . "]");
                                            while ($rowg2 = mysql_fetch_array($objedit2)) {
                                                ?>
                                                <img src="../../images/jobs/<?= $rowg2['image_job_name']; ?>"
                                                     width="140px" height="140px" alt="..."
                                                     class="img-thumbnail">
                                            <?php } ?>
                                        </div>
                                        <div class="col-xs-6 ">
                                            <p class="lead">รายการ</p>
                                            <div class="table-responsive">
                                                <table class="table" align="center">
                                                    <tbody>
                                                    <tr>
                                                        <th style="width:50%">ราคาเริ่มต้น(0
                                                            - <?= $rowsedit['job_charge_start_km']; ?> กม.) :
                                                        </th>
                                                        <td><?php
                                                            $startPrice = $rowsedit['job_charge_start_price'];
                                                            $startKm = $rowsedit['job_charge_start_km'];
                                                            $ratePrice = $rowsedit['job_charge'];

                                                            $liftStatus = $rowsedit['job_service_lift_status'];
                                                            $liftPrice = 0;

                                                            $liftPlusStatus = $rowsedit['job_service_lift_plus_status'];
                                                            $liftPlusPrice = 0;
                                                            $cartStatus = $rowsedit['job_service_cart_status'];
                                                            $cartPrice = 0;
                                                            $distance = $rowsedit['job_distance'];
                                                            if ($liftStatus == "t") {
                                                                $liftPrice = $rowsedit['job_service_lift_price'];
                                                            }
                                                            if ($liftPlusStatus == "t") {
                                                                $liftPlusPrice = $rowsedit['job_service_lift_plus_price'];
                                                            }
                                                            if ($cartStatus == "t") {
                                                                $cartPrice = $rowsedit['job_service_cart_price'];
                                                            }
                                                            if ($distance > $startKm) {
                                                                $distance = $distance - $startKm;
                                                            } else {
                                                                $distance = 0;
                                                            }
                                                            $total = ($distance * $ratePrice) + $startPrice + $liftPrice + $liftPlusPrice + $cartPrice;
                                                            echo number_format($startPrice, 2, '.', '');
                                                            ?> บาท
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>คิดตามระยะทาง (<?php
                                                            echo number_format($distance, 2, '.', '');
                                                            ?> กม.) :
                                                        </th>
                                                        <td><?php echo number_format(($distance * $ratePrice), 2, '.', '');    ?>
                                                            บาท
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>คนชับช่วยยกของ :</th>
                                                        <td>
                                                            <?php
                                                            if ($liftStatus == "t") {
                                                                if ($liftPrice == 0) {
                                                                    echo "ฟรี";
                                                                } else {
                                                                    echo number_format($liftPrice, 2, '.', '');
                                                                    ?> บาท <?php
                                                                }
                                                            } else {
                                                                echo "-";
                                                            }
                                                            ?></td>
                                                    </tr>

                                                    <tr>
                                                        <th>ผู้ช่วยช่วยยกของ :</th>
                                                        <td>
                                                            <?php
                                                            if ($liftPlusStatus == "t") {
                                                                if ($liftPlusPrice == 0) {
                                                                    echo "ฟรี";
                                                                } else {
                                                                    echo number_format($liftPlusPrice, 2, '.', '');
                                                                    ?> บาท <?php
                                                                }
                                                            } else {
                                                                echo "-";
                                                            }
                                                            ?></td>
                                                    </tr>

                                                    <tr>
                                                        <th>รถเข็น :</th>
                                                        <td>
                                                            <?php
                                                            if ($cartStatus == "t") {
                                                                if ($cartPrice == 0) {
                                                                    echo "ฟรี";
                                                                } else {
                                                                    echo number_format( $cartPrice  , 2, '.', '');
                                                                    ?> บาท <?php
                                                                }
                                                            } else {
                                                                echo "-";
                                                            }
                                                            ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>รวม :</th>
                                                        <td><?php echo number_format( $total  , 2, '.', '');  ?>
                                                            บาท
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- /.col -->
                                    </div>
                                    <!-- /.row -->

                                    <!-- this row will not appear when printing -->
                                    <div class="row no-print">
                                        <div class="col-xs-12">
                                            <button class="btn btn-default" onclick="window.print();"><i
                                                    class="fa fa-print"></i> Print
                                            </button>

                                        </div>
                                    </div>
                                </section>
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
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../build/js/custom.min.js"></script>
</body>
</html>