<?php
session_start();

include("connection.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>MEDINOVA - Hospital Website Template</title>
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

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid py-2 border-bottom d-none d-lg-block">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-lg-start mb-2 mb-lg-0">
                    <div class="d-inline-flex align-items-center">
                        <a class="text-decoration-none text-body pe-3" href=""><i class="bi bi-telephone me-2"></i>+012
                            345 6789</a>
                        <span class="text-body">|</span>
                        <a class="text-decoration-none text-body px-3" href=""><i
                                class="bi bi-envelope me-2"></i>info@example.com</a>
                    </div>
                </div>
                <div class="col-md-6 text-center text-lg-end">
                    <div class="d-inline-flex align-items-center">
                        ` <a class="text-body px-2" href="">
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
    <div class="container-fluid sticky-top bg-white shadow-sm mb-5">
        <div class="container">
            <nav class="navbar navbar-expand-lg bg-white navbar-light py-3 py-lg-0">
                <a href="index.html" class="navbar-brand">
                    <h1 class="m-0 text-uppercase text-primary"><i class="fa fa-clinic-medical me-2"></i>Medinova</h1>
                </a>

            </nav>
        </div>
    </div>
    <!-- Navbar End -->

    <!-- Login Start -->
    <div class="container-fluid py-5">
        <div class="container d-flex justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="bg-light text-center rounded p-5">
                    <h1 class="mb-4">Login</h1>
                    <form action="" method="post">
                        <div class="row g-3">
                            <div class="col-12">
                                <input type="email" class="form-control bg-white border-0" placeholder="Email"
                                    name="email" style="height: 55px;" required>
                            </div>
                            <div class="col-12">
                                <input type="password" class="form-control bg-white border-0" placeholder="Password"
                                    name="password" style="height: 55px;" required>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100 py-3" type="submit" name="submit">Login</button>
                            </div>
                        </div>
                    </form>
                    <p class="mt-3">New here? <a href="signup.php">Sign up</a></p> <!-- Sign up link -->
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST['submit'])) {
        $email = $_POST['email'];
        $pass = $_POST['password'];

        

        $query = mysqli_query($conn, "SELECT * FROM `parents` WHERE email='$email' AND password = '$pass'");
        $row = mysqli_fetch_assoc($query);

        if ($row) {
            $_SESSION['parent_id'] = $row['id'];
            $_SESSION['parent_name'] = $row['name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = "parent";
            echo ("<script> alert('Login successfully');
                    location.assign('index.php')</script>");
        } else {
            echo ("<script>alert('Login unsuccessfully');
            </script>");
        }
    }
    // session_destroy()
    ?>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script>
    $(document).ready(function() {
        $("form").on("submit", function(e) {
            let isValid = true;

            // Validate email
            let email = $("input[name='email']").val();
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert("Please enter a valid email address.");
                isValid = false;
            }

            // Validate password (checking if it's not empty)
            let password = $("input[name='password']").val();
            if (password.trim() === "") {
                alert("Please enter your password.");
                isValid = false;
            }

            // If the form is not valid, prevent submission
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>

</html>