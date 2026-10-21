<?php
// Session and login are handled by auth.php, which every page includes before this sidebar.
if ($_SESSION['userrole'] == 'admin') {
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-left">
        <a href="index.php" class="logo">
            <img src="assets/img/logo.png" width="35" height="35" alt=""> <span>Preclinic</span>
        </a>
    </div>
    <a id="toggle_btn" href="javascript:void(0);"><i class="fa fa-bars"></i></a>
    <a id="mobile_btn" class="mobile_btn float-left" href="#sidebar"><i class="fa fa-bars"></i></a>
    <ul class="nav user-menu float-right">
        <li class="nav-item dropdown has-arrow">
            <a href="#" class="dropdown-toggle nav-link user-link" data-toggle="dropdown">
                <span class="user-img">
                    <img class="rounded-circle" src="assets/img/user.jpg" width="24" alt="Admin">
                    <span class="status online"></span>
                </span>
                <span><?php echo $_SESSION['username']; ?></span>
            </a>
            <div class="dropdown-menu">

                <a class="dropdown-item" href="logout.php">Logout</a>
            </div>
        </li>
    </ul>
</div> 

<!-- Sidebar for Admin -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">Main</li>
                <li class="active">
                    <a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                </li>
                <li><a href="child_detail.php"><i class="fa fa-user"></i> <span>Patient Profiles</span></a></li>
                <li><a href="completed_vaccine_report.php"><i class="fa fa-calendar"></i> <span>Vaccine Report</span></a></li>
                <li><a href="view_hosp.php"><i class="fa fa-list"></i> <span>Hospital List</span></a></li>
                <li><a href="vaccines_avail.php"><i class="fa fa-medkit"></i> <span>Vaccine Availability</span></a></li>
                <li><a href="hospital_register.php"><i class="fa fa-hospital-o"></i> <span>Add Hospital</span></a></li>
                <li class="submenu">
                    <a href="#"><i class="fa fa-file-text"></i> <span>Vaccination Reports</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="add-vaccine.php">Add Vaccine</a></li>
                        <li><a href="view-vaccine.php">View Vaccine</a></li>
                    </ul>
                </li>
                <li><a href="completed_vaccines.php"><i class="fa fa-calendar-check-o"></i> <span>Completed Vaccines</span></a></li>
          <li><a href="feedback.php"><i class="fa fa-comments"></i> <span>Parents Feedback</span></a></li>

            </ul>
        </div>
    </div>
</div>

<?php 
} elseif ($_SESSION['userrole'] == 'hospital') {
?>

<!-- Hospital Header -->
<div class="header">
    <div class="header-left">
        <a href="index.php" class="logo">
            <img src="assets/img/logo.png" width="35" height="35" alt=""> <span>Preclinic</span>
        </a>
    </div>
    <a id="toggle_btn" href="javascript:void(0);"><i class="fa fa-bars"></i></a>
    <a id="mobile_btn" class="mobile_btn float-left" href="#sidebar"><i class="fa fa-bars"></i></a>
    <ul class="nav user-menu float-right">
        <!-- Notifications Dropdown -->
        <li class="nav-item dropdown d-none d-sm-block">
            <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown"><i class="fa fa-bell-o"></i> 
                <span class="badge badge-pill bg-danger float-right">3</span>
            </a>
            <div class="dropdown-menu notifications">
                <div class="topnav-dropdown-header">
                    <span>Notifications</span>
                </div>
                <div class="drop-scroll">
                    <ul class="notification-list">
                        <li class="notification-message">
                            <a href="activities.html">
                                <div class="media">
                                    <span class="avatar">
                                        <img alt="John Doe" src="assets/img/user.jpg" class="img-fluid">
                                    </span>
                                    <div class="media-body">
                                        <p class="noti-details"><span class="noti-title">John Doe</span> added new task <span class="noti-title">Patient appointment booking</span></p>
                                        <p class="noti-time"><span class="notification-time">4 mins ago</span></p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="notification-message">
                            <a href="activities.html">
                                <div class="media">
                                    <span class="avatar">V</span>
                                    <div class="media-body">
                                        <p class="noti-details"><span class="noti-title">Tarah Shropshire</span> changed the task name <span class="noti-title">Appointment booking with payment gateway</span></p>
                                        <p class="noti-time"><span class="notification-time">6 mins ago</span></p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <!-- More notifications can be added here -->
                    </ul>
                </div>
                <div class="topnav-dropdown-footer">
                    <a href="activities.html">View all Notifications</a>
                </div>
            </div>
        </li>

        <!-- Messages Dropdown -->
        <li class="nav-item dropdown d-none d-sm-block">
            <a href="javascript:void(0);" id="open_msg_box" class="hasnotifications nav-link"><i class="fa fa-comment-o"></i> 
                <span class="badge badge-pill bg-danger float-right">8</span>
            </a>
        </li>

        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown has-arrow">
            <a href="#" class="dropdown-toggle nav-link user-link" data-toggle="dropdown">
                <span class="user-img">
                    <img class="rounded-circle" src="assets/img/user.jpg" width="24" alt="Hospital User">
                    <span class="status online"></span>
                </span>
                <span><?php echo $_SESSION['username']; ?></span>
            </a>
            <div class="dropdown-menu">
      
                <a class="dropdown-item" href="logout.php">Logout</a>
            </div>
        </li>
    </ul>
</div>
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">Main</li>
                <li class="active">
                    <a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                </li>
                              <li class="submenu">
                    <a href="#"><i class="fa fa-file-text"></i> <span>Vaccination Reports</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a href="add-vaccine.php">Add Vaccine</a></li>
                        <li><a href="view-hosvaccine.php">View Vaccine</a></li>
                    </ul>
                </li>
                <li><a href="view_parent-reqhos.php?hosid=<?php echo $_SESSION['user_id']?>"><i class="fa fa-check-circle"></i> <span>Parent Requests</span></a></li>
                <li><a href="completed_vaccines.php"><i class="fa fa-calendar-check-o"></i> <span>Completed Vaccines</span></a></li>
            </ul>
        </div>
    </div>
</div>

<?php
}
?>


</body>
</html>

                 
