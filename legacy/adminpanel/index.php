<?php
session_start();
include("connection.php");
if (isset($_SESSION['username']) == null) {
    echo "<script>location.assign('login.php')</script>";
} else {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
        <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">
        <title>Preclinic - Medical & Hospital - Bootstrap 4 Admin Template</title>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="assets/css/style.css">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
            integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <!-- Add Font Awesome CDN -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

        <!--[if lt IE 9]>
		<script src="assets/js/html5shiv.min.js"></script>
		<script src="assets/js/respond.min.js"></script>
	<![endif]-->
        <style>
            .card-body.p-0 {
                max-height: 300px;
                /* Adjust height as needed */
                overflow-y: auto;
                /* Enables vertical scrolling */
            }

            .contact-list {
                max-height: 250px;
                /* Adjust height as needed */
                overflow-y: auto;
                /* Enables vertical scrolling */
            }

            /* Custom scrollbar style */
            .card-body.p-0::-webkit-scrollbar,
            .contact-list::-webkit-scrollbar {
                width: 6px;
            }

            .card-body.p-0::-webkit-scrollbar-track,
            .contact-list::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            .card-body.p-0::-webkit-scrollbar-thumb,
            .contact-list::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 10px;
            }

            .card-body.p-0::-webkit-scrollbar-thumb:hover,
            .contact-list::-webkit-scrollbar-thumb:hover {
                background: #555;
            }

            .spam {
                height: 30px;
                width: 100px;
                font-size: 13px;
                color: black;
            }

            .card {
                width: 100%;
                /* Ensure the card takes the full width */
                max-width: none;
                /* Remove any max-width constraints */
                overflow: visible;
                /* Prevent overflow that causes scrollbars */
            }

            .table-responsive {
                width: 100%;
                /* Ensure the table is full width */
                overflow-x: visible;
                /* Disable horizontal scrolling */
            }

            .new-patient-table {
                width: 100%;
                /* Ensure the table fills the container */
                table-layout: auto;
                /* Allow the table layout to adjust based on content */
            }

            .new-patient-table th,
            .new-patient-table td {
                white-space: nowrap;
                /* Prevent wrapping of text inside cells */
            }
        </style>
        <?php
        include('asside.php'); ?>
        <div class="main-wrapper">
            <div class="page-wrapper">
                <div class="content">
                    <!-- for hospital -->
                    <?php
                    if (isset($_SESSION['userrole']) && $_SESSION['userrole'] == 'hospital') {
                        $hospital_id = $_SESSION['user_id'];
                    ?>
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                                <div class="dash-widget">
                                    <span class="dash-widget-bg1"><i class="fa fa-user-o"></i></span>
                                    <div class="dash-widget-info text-right">
                                        <h3> <?php
                                                echo "<br><br>";
                                                echo "<p><small><b>Registered Children.</b></small></p>";
                                                $q = mysqli_query($conn, "SELECT * FROM `children`");
                                                $count = mysqli_num_rows($q);
                                                echo "<i class='fas fa-child' style='font-size:48px;color:#009efb'></i> => " . $count;

                                                ?></h3>
                                        <span class="widget-title1">Registered Children <i class="fa fa-check"
                                                aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                                <div class="dash-widget">
                                    <span class="dash-widget-bg2"><i class="fa fa-medkit"></i></span>
                                    <div class="dash-widget-info text-right">
                                        <h3>
                                            <?php
                                            echo "<br><br>";
                                            echo "<p><small><b>Availability of vaccines.</b></small></p>";
                                            $q = mysqli_query($conn, "SELECT status FROM `vaccines` WHERE status='available' and hospital_id='$hospital_id';");
                                            $count = mysqli_num_rows($q);
                                            echo "<i class='fas fa-syringe' style='font-size:48px;color:#28a745'></i> => " . $count;

                                            ?>
                                        </h3>
                                        <span class="widget-title2">Available Vaccines <i class="fa fa-check"
                                                aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                                <div class="dash-widget">
                                    <span class="dash-widget-bg3"><i class="fa fa-clock-o" aria-hidden="true"></i></span>
                                    <div class="dash-widget-info text-right">
                                        <h3> <?php
                                                echo "<br><br>";
                                                echo "<p><small><b>The pending vaccines</b></small></p>";
                                                $q = mysqli_query($conn, "SELECT status FROM `appointments` WHERE status='pending' and hospital_id='$hospital_id';");
                                                $count = mysqli_num_rows($q);
                                                echo "<i class='fas fa-clock-o' style='font-size:48px;color:rgb(118, 144, 155)'></i> => " . $count;

                                                ?></h3>
                                        <span class="widget-title3">Pending Vaccinations <i class="fa fa-check"
                                                aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                                <div class="dash-widget">
                                    <span class="dash-widget-bg4"><i class="fa fa-check-circle-o" aria-hidden="true"></i></span>
                                    <div class="dash-widget-info text-right">
                                        <h3> <?php
                                                echo "<br><br>";
                                                echo "<p><small><b>Completed vaccines.</b></small></p>";
                                                $q = mysqli_query($conn, "SELECT status FROM `appointments` WHERE status='completed' and hospital_id='$hospital_id';");
                                                $count = mysqli_num_rows($q);
                                                echo "<i class='fas fa-check-circle' style='font-size:48px;color:rgb(241, 185, 0)'></i> => " . $count;

                                                ?></h3>
                                        <span class="widget-title4">Completed Vaccinations <i class="fa fa-check"
                                                aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    } else {
                    ?>
                        <!-- for admin -->
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                                <div class="dash-widget">
                                    <span class="dash-widget-bg1"><i class="fa fa-user-o"></i></span>
                                    <div class="dash-widget-info text-right">
                                        <h3> <?php
                                                echo "<br><br>";
                                                echo "<p><small><b>Registered Children.</b></small></p>";
                                                $q = mysqli_query($conn, "SELECT * FROM `children`");
                                                $count = mysqli_num_rows($q);
                                                echo "<i class='fas fa-child' style='font-size:48px;color:#009efb'></i> => " . $count;

                                                ?></h3>
                                        <span class="widget-title1">Registered Children <i class="fa fa-check"
                                                aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                                <div class="dash-widget">
                                    <span class="dash-widget-bg2"><i class="fa fa-medkit"></i></span>
                                    <div class="dash-widget-info text-right">
                                        <h3>
                                            <?php
                                            echo "<br><br>";
                                            echo "<p><small><b>Availability of vaccines.</b></small></p>";
                                            $q = mysqli_query($conn, "SELECT status FROM `vaccines` WHERE status='available'");
                                            $count = mysqli_num_rows($q);
                                            echo "<i class='fas fa-syringe' style='font-size:48px;color:#28a745'></i> => " . $count;

                                            ?>
                                        </h3>
                                        <span class="widget-title2">Available Vaccines <i class="fa fa-check"
                                                aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                                <div class="dash-widget">
                                    <span class="dash-widget-bg3"><i class="fa fa-clock-o" aria-hidden="true"></i></span>
                                    <div class="dash-widget-info text-right">
                                        <h3> <?php
                                                echo "<br><br>";
                                                echo "<p><small><b>The pending vaccines</b></small></p>";
                                                $q = mysqli_query($conn, "SELECT status FROM `appointments` WHERE status='pending'");
                                                $count = mysqli_num_rows($q);
                                                echo "<i class='fas fa-clock-o' style='font-size:48px;color:rgb(118, 144, 155)'></i> => " . $count;

                                                ?></h3>
                                        <span class="widget-title3">Pending Vaccinations <i class="fa fa-check"
                                                aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                                <div class="dash-widget">
                                    <span class="dash-widget-bg4"><i class="fa fa-check-circle-o" aria-hidden="true"></i></span>
                                    <div class="dash-widget-info text-right">
                                        <h3> <?php
                                                echo "<br><br>";
                                                echo "<p><small><b>Completed vaccines.</b></small></p>";
                                                $q = mysqli_query($conn, "SELECT status FROM `appointments` WHERE status='completed'");
                                                $count = mysqli_num_rows($q);
                                                echo "<i class='fas fa-check-circle' style='font-size:48px;color:rgb(241, 185, 0)'></i> => " . $count;

                                                ?></h3>
                                        <span class="widget-title4">Completed Vaccinations <i class="fa fa-check"
                                                aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php              }
                    ?>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="chart-title">
                                        <h4>Patient Total</h4>
                                        <span class="float-right"><i class="fa fa-caret-up" aria-hidden="true"></i> 15%
                                            Higher than Last Month</span>
                                    </div>
                                    <canvas id="linegraph"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="chart-title">
                                        <h4>Patients In</h4>
                                        <div class="float-right">
                                            <ul class="chat-user-total">
                                                <li><i class="fa fa-circle current-users" aria-hidden="true"></i>ICU</li>
                                                <li><i class="fa fa-circle old-users" aria-hidden="true"></i> OPD</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <canvas id="bargraph"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-8 col-xl-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="font-weight-bold d-inline-block">Detail of vaccines</h4>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Vaccine Name</th>
                                                    <th>Vaccine Detail</th>
                                                    <th>Hospital</th>
                                                    <th class="text-right">Quantity</th>
                                                </tr>
                                                <!-- for hospital -->
                                            </thead>
                                            <?php
                                            if (isset($_SESSION['userrole']) && $_SESSION['userrole'] == 'hospital') {
                                                $hospital_id = $_SESSION['user_id'];

                                            ?>
                                                <tbody>
                                                    <?php

                                                    $q = mysqli_query($conn, "SELECT * FROM `vaccines` WHERE hospital_id='$hospital_id'");
                                                    while ($data = mysqli_fetch_array($q)) {


                                                    ?>
                                                        <tr>

                                                            <td style="min-width: 200px;">
                                                                <a class="avatar" href="profile.html">B</a>
                                                                <h2><a href="profile.html"><?php echo $data[1];
                                                                                            $hosid = $data[4];
                                                                                            $query1 = mysqli_query($conn, "SELECT * FROM `hospitals` WHERE id=$hosid;");
                                                                                            while ($qr = mysqli_fetch_array($query1)) { ?>
                                                                            <span class=""><a href="view-vaccine.php"
                                                                                    class="btn btn-outline-primary take-btn spam"><?php echo $data[5]; ?></span></a>
                                                                    </a></a></h2>
                                                            </td>
                                                            <td>
                                                                <h5 class="time-title p-0"><?php echo $data[2]; ?></h5>
                                                            </td>
                                                            <td>
                                                                <h5 class="time-title p-0"><?php echo $qr['hospital_name']; ?></h5>
                                                                <p><span><?php
                                                                                                echo $qr['3'];
                                                                            ?></span></p>
                                                            </td>

                                                            <td class="text-right">
                                                                <h5 class="time-title p-0"><?php echo $data['3']; ?></h5>
                                                            </td>

                                                        </tr>
                                                <?php  }
                                                                                        } ?>

                                                </tbody>
                                            <?php
                                            } else {
                                            ?>
                                                <!-- for admin -->
                                                <tbody>
                                                    <?php

                                                    $q = mysqli_query($conn, "SELECT * FROM `vaccines`");
                                                    while ($data = mysqli_fetch_array($q)) {


                                                    ?>
                                                        <tr>

                                                            <td style="min-width: 200px;">
                                                                <a class="avatar" href="profile.html">B</a>
                                                                <h2><a href="profile.html"><?php echo $data[1];
                                                                                            $hosid = $data[4];
                                                                                            $query1 = mysqli_query($conn, "SELECT * FROM `hospitals` WHERE id=$hosid;");
                                                                                            while ($qr = mysqli_fetch_array($query1)) { ?>
                                                                            <span class=""><a href="view-vaccine.php"
                                                                                    class="btn btn-outline-primary take-btn spam"><?php echo $data[5]; ?></span></a>
                                                                    </a></a></h2>
                                                            </td>
                                                            <td>
                                                                <h5 class="time-title p-0"><?php echo $data[2]; ?></h5>
                                                            </td>
                                                            <td>
                                                                <h5 class="time-title p-0"><?php echo $qr['hospital_name']; ?></h5>
                                                                <p><span><?php
                                                                                                echo $qr['3'];
                                                                            ?></span></p>
                                                            </td>

                                                            <td class="text-right">
                                                                <h5 class="time-title p-0"><?php echo $data['3']; ?></h5>
                                                            </td>

                                                        </tr>
                                                <?php  }
                                                                                        } ?>

                                                </tbody>

                                            <?php }
                                            ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 col-xl-4">
                            <div class="card member-panel">
                                <div class="card-header bg-white">
                                    <h4 class="card-title mb-0 font-weight-bold">Hospitals</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="contact-list">
                                        <?php
                                        $query1 = mysqli_query($conn, "SELECT * FROM `hospitals`;");
                                        while ($qr = mysqli_fetch_array($query1)) { ?>
                                            <li>
                                                <div class="contact-cont">
                                                    <div class="float-left user-img m-r-10">
                                                        <a href="profile.html" title="John Doe"><img src="assets/img/user.jpg"
                                                                alt="" class="w-40 rounded-circle"><span
                                                                class="status online"></span></a>
                                                    </div>
                                                    <div class="contact-info">
                                                        <span class="contact-name text-ellipsis"><?php echo $qr[2]; ?></span>
                                                        <span class="contact-date"><?php echo $qr[3]; ?></span>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <div class="card-footer text-center bg-white">
                                    <a href="" class="text-muted">All Hospitals</a>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-8 col-xl-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="text-center font-weight-bold">Appointments</h4>
                                </div>
                                <div class="card-block">
                                    <div class="table-responsive">
                                        <table class="table mb-0 new-patient-table">
                                            <thead>
                                                <tr>
                                                    <th>Parent Name</th>
                                                    <th>C.Name</th>
                                                    <th>Vaccine</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                                <!-- for hospital -->
                                            </thead>
                                            <?php
                                            if (isset($_SESSION['userrole']) && $_SESSION['userrole'] == 'hospital') {
                                                $hospital_id = $_SESSION['user_id'];
                                            ?>
                                                <tbody>
                                                    <?php
                                                    $q = mysqli_query($conn, "SELECT * FROM `appointments` where hospital_id='$hospital_id';");
                                                    while ($data = mysqli_fetch_array($q)) {
                                                        $hosid = $data[4];
                                                        $query1 = mysqli_query($conn, "SELECT * FROM `hospitals` WHERE id=$hosid;");
                                                        $qr = mysqli_fetch_array($query1);
                                                    ?>
                                                        <tr>
                                                            <td style="min-width: 200px;">
                                                                <a class="avatar" href="profile.html">B</a>
                                                                <h2><a href="profile.html"><?php echo $data[1]; ?></a></h2>
                                                            </td>
                                                            <td>
                                                                <h5 class="time-title p-0"><?php echo $data[2]; ?></h5>
                                                            </td>
                                                            <td>
                                                                <h5 class="time-title p-0"><?php echo $data[3]; ?></h5>
                                                            </td>
                                                            <td class="text-right">
                                                                <h5 class="time-title p-0"><?php echo $data[7]; ?></h5>
                                                            </td>
                                                            <td class="text-right">
                                                                <h5 class="time-title p-0"><?php echo $data[9]; ?></h5>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            <?php
                                            } else {
                                            ?>

                                                <tbody>
                                                    <?php
                                                    $q = mysqli_query($conn, "SELECT * FROM `appointments`");
                                                    while ($data = mysqli_fetch_array($q)) {
                                                        $hosid = $data[4];
                                                        $query1 = mysqli_query($conn, "SELECT * FROM `hospitals` WHERE id=$hosid;");
                                                        $qr = mysqli_fetch_array($query1);
                                                    ?>
                                                        <tr>
                                                            <td style="min-width: 200px;">
                                                                <a class="avatar" href="profile.html">B</a>
                                                                <h2><a href="profile.html"><?php echo $data[1]; ?></a></h2>
                                                            </td>
                                                            <td>
                                                                <h5 class="time-title p-0"><?php echo $data[2]; ?></h5>
                                                            </td>
                                                            <td>
                                                                <h5 class="time-title p-0"><?php echo $data[3]; ?></h5>
                                                            </td>
                                                            <td class="text-right">
                                                                <h5 class="time-title p-0"><?php echo $data[7]; ?></h5>
                                                            </td>
                                                            <td class="text-right">
                                                                <h5 class="time-title p-0"><?php echo $data[9]; ?></h5>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            <?php  }
                                            ?>
                                        </table>
                                    </div>
                                </div>

                                <div class="notification-box">
                                    <div class="msg-sidebar notifications msg-noti">
                                        <div class="topnav-dropdown-header">
                                            <span>Messages</span>
                                        </div>
                                        <div class="drop-scroll msg-list-scroll" id="msg_list">
                                            <ul class="list-box">
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">R</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author">Richard Miles </span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item new-message">
                                                            <div class="list-left">
                                                                <span class="avatar">J</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author">John Doe</span>
                                                                <span class="message-time">1 Aug</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">T</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author"> Tarah Shropshire </span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">M</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author">Mike Litorus</span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">C</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author"> Catherine Manseau </span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">D</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author"> Domenic Houston </span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">B</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author"> Buster Wigton </span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">R</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author"> Rolland Webber </span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">C</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author"> Claire Mapes </span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">M</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author">Melita Faucher</span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">J</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author">Jeffery Lalor</span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">L</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author">Loren Gatlin</span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="chat.html">
                                                        <div class="list-item">
                                                            <div class="list-left">
                                                                <span class="avatar">T</span>
                                                            </div>
                                                            <div class="list-body">
                                                                <span class="message-author">Tarah Shropshire</span>
                                                                <span class="message-time">12:28 AM</span>
                                                                <div class="clearfix"></div>
                                                                <span class="message-content">Lorem ipsum dolor sit amet,
                                                                    consectetur adipiscing</span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="topnav-dropdown-footer">
                                            <a href="chat.html">See all messages</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <?php
                        include('footer.php');
                        ?>

                        </body>

    </html>
<?php
}
?>