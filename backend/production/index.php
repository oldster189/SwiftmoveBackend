<?php
include('session.php');
include('connection.php'); ?>
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
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="../build/css/custom.min.css" rel="stylesheet">
    <style>@font-face {
            font-family: yourFontName ;
            src: url(../build/css/th_baijam_bold-webfont.ttf) format("truetype");
        }

        .body {
            font-family: yourFontName, serif;

        }
        </style>
</head>
<body style="color:black;" class="nav-md">
<div class="container body" >
    <div class="main_container" >
        <div class="col-md-3 left_col" >
            <div class="left_col scroll-view" >
                <!-- sidebar menu -->
                <?php require("sidebar.php"); ?>
                <!-- /sidebar menu -->
            </div>
        </div>
        <!-- top navigation -->
        <?php require("top_navigation.php"); ?>
        <!-- /top navigation -->
        <!-- page content -->
        <div class="right_col" role="main" >
            <div class="row">
                <H1 align="center" style="margin: 20px">ระบบบริหารจัดการข้อมูล</H1>
                <H1 align="center">บริการเรียกรถขนส่งขนย้ายสิ่งของ</H1>

            </div>
        </div>
        <?php require("footer.php"); ?>
    </div>
</div>

<script src="../vendors/jquery/dist/jquery.min.js"></script><!-- Bootstrap -->
<script src="../vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="../build/js/custom.min.js"></script>
</body>
</html>