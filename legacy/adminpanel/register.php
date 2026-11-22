<?php
include("connection.php");

if (isset($_POST["signup"])) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $pass = trim($_POST["pass"]);
    $number = trim($_POST["number"]);
    $role = 'admin';

    $errors = [];

    if (!preg_match("/[a-zA-Z]/", $username)) {
        $errors[] = "Username must contain at least one letter.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($pass) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (!preg_match("/^[0-9]{10}$/", $number)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($checkUser) > 0) {
        $errors[] = "Username is already taken.";
    }

    if (empty($errors)) {
        $username = mysqli_real_escape_string($conn, $username);
        $email = mysqli_real_escape_string($conn, $email);
        $pass = mysqli_real_escape_string($conn, $pass); 
        $number = mysqli_real_escape_string($conn, $number);

        $q = mysqli_query($conn, "INSERT INTO users (username, password, email, phone, role) 
            VALUES ('$username', '$pass', '$email', '$number', '$role')");

        if ($q) {
            echo "<script>alert('Registration successful!'); location.assign('index.php');</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    } else {
        echo "<script>alert('" . implode("\\n", $errors) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Signup</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <style>
    body{
        background-color: #009efb !important;
    }
</style>
</head>

<body>
    <div class="main-wrapper account-wrapper">
        <div class="account-page">
            <div class="account-center">
                <div class="account-box">
                    <form action="" class="form-signin" method="post" onsubmit="return validateForm()">
                        <div class="account-logo text-center mb-4">
                            <a href="index.php">
                                <img src="assets/img/logo-dark.png" alt="Logo">
                            </a>
                        </div>

                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" id="pass" name="pass" class="form-control" placeholder="Enter your password" required>
                        </div>

                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" id="number" name="number" class="form-control" placeholder="Enter your mobile number" required>
                        </div>

                        <div class="form-group text-center">
                            <input type="submit" name="signup" value="Sign Up" class="btn btn-primary account-btn">
                        </div>

                        <div class="text-center login-link mt-3">
                            Already have an account? <a href="login.php">Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
        function validateForm() {
            let username = document.getElementById("username").value.trim();
            let email = document.getElementById("email").value.trim();
            let pass = document.getElementById("pass").value.trim();
            let number = document.getElementById("number").value.trim();

            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            let phonePattern = /^[0-9]{10}$/;
            let usernamePattern = /[a-zA-Z]/;

            let errors = [];

            if (!usernamePattern.test(username)) {
                errors.push("Username must contain at least one letter.");
            }

            if (!emailPattern.test(email)) {
                errors.push("Please enter a valid email address.");
            }

            if (pass.length < 6) {
                errors.push("Password must be at least 6 characters long.");
            }

            if (!phonePattern.test(number)) {
                errors.push("Please enter a valid 10-digit phone number.");
            }

            if (errors.length > 0) {
                alert(errors.join("\\n"));
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
