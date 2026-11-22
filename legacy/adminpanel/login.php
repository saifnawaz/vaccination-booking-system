<?php
session_start();
include("connection.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">
    <title>Preclinic - Medical & Hospital - Bootstrap 4 Admin Template</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
     <!--[if lt IE 9]>
		<script src="assets/js/html5shiv.min.js"></script>
		<script src="assets/js/respond.min.js"></script>
	<![endif]-->
    <style>
    body{
        background-color: #009efb !important;
    }
</style>    
</head>
<body>
<?php

?>
    <div class="main-wrapper account-wrapper">
        <div class="account-page">
			<div class="account-center">
				<div class="account-box">
                    <form action="" class="form-signin" method="post">

						<div class="account-logo">
                            <a href="index-2.html"><img src="assets/img/logo-dark.png" alt=""></a>
                        </div>
                        <div class="form-group">
                            <label>Username or Email</label>
                            <input type="text" autofocus="" name="email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="pass" class="form-control">
                        </div>
                        <div class="form-group text-center">
                            <a href="forgot-password.html">Forgot your password?</a>
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" name="submit" class="btn-primary account-btn">Login</button>
                        </div>
                        <div class="text-center register-link">
                           <p>Register as Admin <a href="register.php">Register Now</a></p> 
                            <p>Register as Hospital <a href="hospital_register.php">Register Now</a></p>
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
    



<?php
if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $pass = $_POST['pass'];

 
    $qadmin = mysqli_query($conn, "SELECT * FROM `users` WHERE email='$email' AND password='$pass' AND role='admin'");
    
    if (mysqli_num_rows($qadmin) > 0) {
        $admin = mysqli_fetch_assoc($qadmin);
        $_SESSION['username'] = $admin['username'];
        $_SESSION['userrole'] = "admin";
        $_SESSION['user_id'] = $admin['id'];
        
        echo "<script>
                alert('Login successfully as Admin');
                location.assign('index.php');
              </script>";
    } else {
        
        $qhos = mysqli_query($conn, "SELECT * FROM `hospitals` WHERE email='$email' AND password='$pass' AND role='hospital'");
        
        if (mysqli_num_rows($qhos) > 0) {
            $hospital = mysqli_fetch_assoc($qhos);
            $_SESSION['username'] = $hospital['hospital_name'];
            $_SESSION['userrole'] = "hospital";
            $_SESSION['user_id'] = $hospital['id'];
            echo "<script>
                    alert('Login successfully as Hospital');
                    location.assign('index.php');
                  </script>";
        } else {
            echo "<script>
                    alert('Login failed. Incorrect email or password.');
                  </script>";
        }
    }
}


if (isset($_SESSION['username'])) {
    if ($_SESSION['userrole'] == 'admin') {
        header("Location: index.php");
    } elseif ($_SESSION['userrole'] == 'hospital') {
        header("Location: index.php");
    }
    exit;
}

?>
</body>
<script>
$(document).ready(function() {
        $("form").on("submit", function(e) {
            let isValid = true;
            let emailOrUsername = $("input[name='email']").val().trim();
            let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailOrUsername === "") {
                alert("Please enter your username or email.");
                isValid = false;
            } else if (emailPattern.test(emailOrUsername) === false && emailOrUsername.length < 3) {
                alert("Please enter a valid username or email.");
                isValid = false;
            }
            let password = $("input[name='pass']").val().trim();
            if (password === "") {
                alert("Please enter your password.");
                isValid = false;
            }
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
</script>
</html>