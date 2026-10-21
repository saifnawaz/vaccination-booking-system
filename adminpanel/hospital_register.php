<?php
include("connection.php");
?>
<!DOCTYPE html>
<html lang="en">


<!-- register24:03-->

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
    <div class="main-wrapper  account-wrapper">
        <div class="account-page">
            <div class="account-center">
                <div class="account-box">
                    <form action="" class="form-signin" method="post" onsubmit="return validateForm()">
                        <div class="account-logo">
                            <a href=""><img src="assets/img/logo-dark.png" alt=""></a>
                        </div>
                        <div class="form-group">
                            <label>Hospital Name</label>
                            <input type="text" name="hosname" class="form-control" id="hosname">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" id="email">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="pass" class="form-control" id="pass">
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" name="number" class="form-control" id="number">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control" id="address">
                        </div>

                        <div class="form-group text-center">
                            <input type="submit" name="sinup" value="Signup" class="btn btn-primary account-btn">
                        </div>
                        <div class="text-center login-link">
                            Already have an account? <a href="login.php">Login</a>
                        </div>
                    </form>

                    <?php
if (isset($_POST["sinup"])) {
    $hosname = $_POST["hosname"];
    $email = $_POST["email"];
    $pass = $_POST["pass"];
    $number = $_POST["number"];
    $address = $_POST["address"];

    $checkUser = mysqli_query($conn, "SELECT * FROM hospitals WHERE email='$email' OR hospital_name='$hosname'");
    
    if (mysqli_num_rows($checkUser) > 0) {
        echo "<script>alert('Error: Hospital name or email already exists. Please use a different one.');</script>";
    } else {
        $q = mysqli_query($conn, "INSERT INTO `hospitals`(`hospital_name`, `address`, `phone`, `email`, `password`) VALUES ('$hosname','$address','$number','$email','$pass')");

        if ($q) {
            echo "<script>alert('Hospital registration successful');
            location.assign('login.php');</script>";
        } else {
            echo "<script>alert('Error during registration');</script>";
        }
    }
}
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/jquery-3.2.1.min.js"></script>
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>


<script>
    function validateForm() {
        let hosname = document.getElementById("hosname").value.trim();
        let email = document.getElementById("email").value.trim();
        let pass = document.getElementById("pass").value.trim();
        let number = document.getElementById("number").value.trim();
        let address = document.getElementById("address").value.trim();
        let errors = [];

        if (!/[a-zA-Z\s]+/.test(hosname)) {
            errors.push("Hospital name must contain only letters and spaces.");
            
        }

        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            errors.push("Please enter a valid email address.");
            
        }

        if (pass.length < 6) {
            errors.push("Password must be at least 6 characters long.");
        }

        let phonePattern = /^[0-9]{10}$/;
        if (!phonePattern.test(number)) {
            errors.push("Mobile number must be exactly 10 digits.");
        }

        if (address === "") {
            errors.push("Address cannot be empty.");
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }

        return true;
    }
</script>
<?php
?>

</html>