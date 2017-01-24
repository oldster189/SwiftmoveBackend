<?php
/**
 * Created by PhpStorm.
 * User: Old'ster
 * Date: 15/11/2559
 * Time: 2:39
 */ ?>
<div class="navbar nav_title" style="border: 0;">
    <a href="index.php" class="site_title"><i class="fa fa-gears"></i> <span>ผู้ดูแลระบบ</span></a>
</div>
<div class="clearfix"></div>
<br/>
<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
    <div class="menu_section">
        <ul class="nav side-menu">
            <li><a href="index.php"><i class="fa fa-home"></i> หน้าแรก </a></li>

            <li><a><i class="fa fa-user"></i> จัดการข้อมูลผู้ใช้บริการ <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                    <li><a href="frm_user.php"> เพิ่มผู้ใช้บริการ </a></li>
                    <li><a href="tb_user.php"> รายการข้อมูลผู้ใช้บริการทั้งหมด </a></li>
                </ul>
            </li>
            <li><a><i class="fa fa-car"></i> จัดการข้อมูลผู้ให้บริการ <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu">
                    <li><a href="frm_driver.php"> เพิ่มผู้ให้บริการ </a></li>
                    <li><a href="tb_driver.php"> รายการข้อมูลผู้ให้บริการทั้งหมด </a></li>
                </ul>
            </li>
            <li><a href="tb_job.php"><i class="fa fa-database"></i> รายงานข้อมูลขนย้ายสิ่งของ</a>
            <li><a href="tb_comment.php"><i class="fa fa-comments"></i> รายงานข้อมูลความคิดเห็น </a>
            <li><a href="tb_rating.php"><i class="fa fa-star"></i> รายงานข้อมูลคะแนน </a>

        </ul>
    </div>
</div>

