<?php
include("connection.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>MEDINOVA</title>
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

    <!-- Signup Start -->
    <div class="container-fluid py-5">
        <div class="container d-flex justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="bg-light text-center rounded p-5">
                    <h1 class="mb-4">Sign Up</h1>

                    <!DOCTYPE html>
                    <html lang="en">

                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Signup</title>
                    </head>

                    <body>

                        <form action="" method="post" onsubmit="return validateForm()">
                            <div class="row g-3">
                                <div class="col-12">
                                    <input type="text" class="form-control bg-white border-0" placeholder="Full Name" name="uname" id="uname" style="height: 55px;" required>
                                </div>
                                <div class="col-12">
                                    <input type="email" class="form-control bg-white border-0" placeholder="Email" name="uemail" id="uemail" style="height: 55px;" required>
                                </div>
                                <div class="col-12">
                                    <input type="password" class="form-control bg-white border-0" placeholder="Password" name="upass" id="upass" style="height: 55px;" required>
                                </div>
                                <div class="col-12">
                                    <input type="tel" class="form-control bg-white border-0" placeholder="Phone No." name="uphone" id="uphone" style="height: 55px;" required>
                                </div>
                                <div class="col-12">
                                    <input type="text" class="form-control bg-white border-0" placeholder="Address" name="address" id="address" style="height: 55px;" required>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary w-100 py-3" name="btn_signup" type="submit">Sign Up</button>
                                </div>
                            </div>
                        </form>

                        <?php
                        if (isset($_POST["btn_signup"])) {
                          

                            $uname = trim($_POST["uname"]);
                            $email = trim($_POST["uemail"]);
                            $password = trim($_POST["upass"]);
                            $phone = trim($_POST["uphone"]);
                            $p_role = "parent";
                            $address = trim($_POST["address"]);
                            $created_at = date('Y-m-d H:i:s');

                            $errors = [];

                            // Check if the email, phone, or name already exists
                            $check_query = "SELECT * FROM `parents` WHERE email = '$email' OR phone_no = '$phone' OR name = '$uname'";
                            $check_result = mysqli_query($conn, $check_query);

                            if (mysqli_num_rows($check_result) > 0) {
                                $errors[] = "User with the same name, email, or phone number already exists.";
                            }

                            if (empty($errors)) {
                                // Insert into database only if no errors
                                $q = mysqli_query($conn, "INSERT INTO `parents` (`name`, `email`, `password`, `phone_no`, `role`, `address`, `created_at`) 
                VALUES ('$uname','$email','$password','$phone','$p_role','$address','$created_at')");

                                if ($q) {
                                    echo "<script> alert('You are registered now!'); 
                location.assign('login.php');</script>";
                                } else {
                                    echo "<script> alert('Registration Unsuccessful');</script>";
                                }
                            } else {
                                // Show error message if duplicate data exists
                                echo "<script> alert('" . implode("\\n", $errors) . "');</script>";
                            }
                        }


                        ?>

                        <p class="mt-3">Already have an account? <a href="login.php">Login</a></p> <!-- Login link -->
                </div>
            </div>
        </div>
    </div>
    <!-- Signup End -->

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script>
        function validateForm() {
            let fullName = document.getElementById("uname").value.trim();
            let email = document.getElementById("uemail").value.trim();
            let password = document.getElementById("upass").value.trim();
            let phone = document.getElementById("uphone").value.trim();
            let address = document.getElementById("address").value.trim();
            let errors = [];

            if (fullName === "") {
                errors.push("Full name is required.");
            } else if (!/^[a-zA-Z\s]+$/.test(fullName)) {
                errors.push("Full name must contain only letters and spaces.");
            }

            if (email === "") {
                errors.push("Email is required.");
            } else if (!/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/.test(email)) {
                errors.push("Please enter a valid email address.");
            }

            if (password === "") {
                errors.push("Password is required.");
            } else if (password.length < 6) {
                errors.push("Password must be at least 6 characters long.");
            }

            if (phone === "") {
                errors.push("Phone number is required.");
            } else if (!/^\d{10}$/.test(phone)) {
                errors.push("Please enter a valid 10-digit phone number.");
            }

            if (address === "") {
                errors.push("Address is required.");
            }

            if (errors.length > 0) {
                alert(errors.join("\n"));
                return false;
            }

            return true;
        }
    </script>

</body>

</html>