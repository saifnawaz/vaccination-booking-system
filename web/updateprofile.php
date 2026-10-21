<?php require_once 'auth.php'; ?>
<?php
include("connection.php");

if(isset($_GET["id"])){
    $uid = $_GET["id"];
    $query = mysqli_query($conn, "SELECT * FROM `parents` WHERE id=$uid");
    $parent = mysqli_fetch_array($query);
}

if (isset($_POST["btn_update"])) {
    $uname = trim($_POST["uname"]);
    $email = trim($_POST["uemail"]);
    $password = trim($_POST["upass"]);
    $phone = trim($_POST["uphone"]);
    $address = trim($_POST["address"]);
    $p_role = "parent";
    $created_at = date('Y-m-d H:i:s');

    $errors = [];

    if (!preg_match("/^[a-zA-Z\s]+$/", $uname)) {
        $errors[] = "Full Name must contain only letters and spaces.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    if (empty($address)) {
        $errors[] = "Address cannot be empty.";
    }

    if (empty($errors)) {
        $q = mysqli_query($conn, "UPDATE `parents` SET `name`='$uname', `email`='$email', `password`='$password', `phone_no`='$phone', `role`='$p_role', `address`='$address', `created_at`='$created_at' WHERE id=$uid");

        if ($q) {
            echo "<script>alert('Profile Updated Successfully'); location.assign('profile.php');</script>";
        } else {
            echo "<script>alert('Update Unsuccessful');</script>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MEDINOVA</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <!-- Stylesheets -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid py-5">
    <div class="container d-flex justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="bg-light text-center rounded p-5">
                <h1 class="mb-4">Update Profile</h1>
                <form action="" method="post" onsubmit="return validateForm()">

                    <div class="row g-3">
                        <div class="col-12">
                            <input type="text" class="form-control bg-white border-0" placeholder="Full Name" value="<?php echo htmlspecialchars($parent[1]); ?>" name="uname" id="uname" style="height: 55px;" required>
                        </div>
                        <div class="col-12">
                            <input type="email" class="form-control bg-white border-0" placeholder="Email" value="<?php echo htmlspecialchars($parent[2]); ?>" name="uemail" id="uemail" style="height: 55px;" required>
                        </div>
                        <div class="col-12">
                            <input type="password" class="form-control bg-white border-0" placeholder="Password" value="<?php echo htmlspecialchars($parent[3]); ?>" name="upass" id="upass" style="height: 55px;" required>
                        </div>
                        <div class="col-12">
                            <input type="tel" class="form-control bg-white border-0" placeholder="Phone No." value="<?php echo htmlspecialchars($parent[4]); ?>" name="uphone" id="uphone" style="height: 55px;" required>
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control bg-white border-0" placeholder="Address" value="<?php echo htmlspecialchars($parent[6]); ?>" name="address" id="address" style="height: 55px;" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100 py-3" name="btn_update" type="submit">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function validateForm() {
    let uname = document.getElementById("uname").value.trim();
    let email = document.getElementById("uemail").value.trim();
    let pass = document.getElementById("upass").value.trim();
    let phone = document.getElementById("uphone").value.trim();
    let address = document.getElementById("address").value.trim();
    let errors = [];

    if (!/^[a-zA-Z\s]+$/.test(uname)) {
        errors.push("Full Name must contain only letters and spaces.");
    }

    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        errors.push("Please enter a valid email address.");
    }

    if (pass.length < 6) {
        errors.push("Password must be at least 6 characters long.");
    }

    let phonePattern = /^[0-9]{10}$/;
    if (!phonePattern.test(phone)) {
        errors.push("Phone number must be exactly 10 digits.");
    }

    if (address === "") {
        errors.push("Address cannot be empty.");
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
