<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connection.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Medinova</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Roboto:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="css/notification.css"> -->
    <link rel="stylesheet" href="css/header.css">

</head>

<!-- Topbar Start -->
<div class="container-fluid py-2 border-bottom d-none d-lg-block">
    <div class="container">
        <div class="row">
            <div class="col-md-6 text-center text-lg-start mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center">
                    <a class="text-decoration-none text-body pe-3" href=""><i class="bi bi-telephone me-2"></i>+012 345
                        6789</a>
                    <span class="text-body">|</span>
                    <a class="text-decoration-none text-body px-3" href=""><i
                            class="bi bi-envelope me-2"></i>info@example.com</a>
                </div>
            </div>
            <div class="col-md-6 text-center text-lg-end">
                <div class="d-inline-flex align-items-center">
                    <a class="text-body px-2" href="">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a class="text-body px-2" href="">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a class="text-body px-2" href="">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a class="text-body px-2" href="">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a class="text-body ps-2" href="">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Topbar End -->

<!-- Navbar Start -->
<div class="container-fluid sticky-top bg-white shadow-sm">
    <div class="container">
        <nav class="navbar navbar-expand-lg bg-white navbar-light py-3 py-lg-0">
            <a href="index.php" class="navbar-brand">
                <h1 class="m-0 text-uppercase text-primary"><i class="fa fa-clinic-medical me-2"></i>Medinova</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="index.php" class="nav-item nav-link ">Home</a>
                    <a href="add_child.php" class="nav-item nav-link">Register Child</a>
                    <a href="search-hospital.php" class="nav-item nav-link">Search Hospitals</a>
                    <a href="vaccine_report.php" class="nav-item nav-link">Reports</a>



                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Pages</a>
                        <div class="dropdown-menu m-0 slow-dropdown">
                            <a href="search-vaccines.php" class="dropdown-item">Search Vaccines</a>
                            <a href="search-hospital.php" class="dropdown-item">Appointment</a>
                        </div>
                    </div>

                    <a href="contact.php" class="nav-item nav-link">Contact</a>
                    <?php

                    if (isset($_SESSION['parent_id']) == null) {
                        echo '<a href="login.php" class="nav-item nav-link">Login</a>';
                    } else {

                    ?>
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle" style="font-size:24px"></i>
                                <?php echo $_SESSION['parent_name']; ?>
                            </a>
                            <div class="dropdown-menu slow-dropdown" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="profile.php">Your Profile</a>
                                <a class="dropdown-item" href="logout.php">Logout</a>
                                <a class="dropdown-item" href="add_child.php">Add Child</a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Notification Icon and Dropdown -->
            <?php if (isset($_SESSION['parent_id'])) { ?>
                <div class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle position-relative" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-bell" style="font-size:24px"></i>
                        <?php
                        // Get count of unread notifications from last 7 days excluding completed
                        $parent_id = $_SESSION['parent_id'];
                        $notify_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments 
                                           WHERE parent_id = '$parent_id' 
                                           AND appointment_date >= NOW() - INTERVAL 7 DAY
                                           AND status != 'completed'");
                        $count = mysqli_fetch_assoc($notify_count);
                        if ($count['count'] > 0) {
                            echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    ' . $count['count'] . '
                  </span>';
                        }
                        ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" style="width: 300px; max-height: 400px; overflow-y: auto;">
                        <h6 class="dropdown-header">Notifications</h6>
                        <?php
                        // Get recent appointments from last 7 days excluding completed status
                        $notifications = mysqli_query($conn, "SELECT a.*, c.first_name, c.last_name, v.name as vaccine_name, h.hospital_name 
                                            FROM appointments a
                                            JOIN children c ON a.child_id = c.id
                                            JOIN vaccines v ON a.vaccine_id = v.id
                                            JOIN hospitals h ON a.hospital_id = h.id
                                            WHERE a.parent_id = '$parent_id'
                                            AND a.appointment_date >= NOW() - INTERVAL 7 DAY
                                            AND a.status != 'completed'
                                            ORDER BY a.created_at DESC
                                            LIMIT 5");

                        if (mysqli_num_rows($notifications) > 0) {
                            while ($row = mysqli_fetch_assoc($notifications)) {
                                echo '<div class="dropdown-item">
                        <div class="d-flex flex-column">
                            <small class="text-muted">' . date('M d, Y', strtotime($row['appointment_date'])) . '</small>
                            <p class="mb-0">Appointment for ' . $row['first_name'] . ' ' . $row['last_name'] . '</p>
                            <small>Vaccine: ' . $row['vaccine_name'] . '</small>
                            <small>Hospital: ' . $row['hospital_name'] . '</small>
                            <small class="text-muted">Status: ' . ucfirst($row['status']) . '</small>
                        </div>
                        <hr class="my-2">
                    </div>';
                            }
                        } else {
                            echo '<div class="dropdown-item">No notifications</div>';
                        }
                        ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="profile.php">View All</a>
                    </div>
                </div>
            <?php } ?>
        </nav>
    </div>
</div>
<!-- Navbar End -->