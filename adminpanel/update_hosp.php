<?php require_once 'auth.php'; require_role('admin'); ?>
<?php
include("connection.php");
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
    <style>
        .card {
            border-radius: 10px;
            overflow: hidden;
        }

        .card-header {
            background-color: #009efb;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .form-control {
            border-radius: 5px;
            font-size: 1.1rem;
        }

        .btn {
            font-size: 1.2rem;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }

        .container {
            margin-top: 60px;
        }

        .table thead {
            background-color: #009efb;
            color: white;
        }

        .table td, .table th {
            text-align: center;
        }

        .btn-info, .btn-primary {
            font-size: 1.1rem;
        }

        .page-heading {
            text-align: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin-top: 50px;
        }
        
    </style>
</head>

<body id="page-top">

    <div id="wrapper">
        <?php 
        if(isset($_GET['Uid'])){
            $up=$_GET['Uid'];
            $query=mysqli_query($conn,"SELECT * FROM `hospitals` WHERE id=$up");
            $col=mysqli_fetch_array($query);
        }
        ?>
        <?php include("asside.php") ?>

        <div class="container-fluid">
            <div class="main-content">
                <div class="container my-5">
                    <h1 class="page-heading"><span style="color:#009efb">U</span>pdate <span style="color:#009efb">H</span>ospial</h1>

                    <div class="card shadow-lg">
                        <div class="card-header text-center">
                            <h3> Update Hosital</h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="post" enctype="multipart/form-data" onsubmit="return validateHospitalForm()">
                                <div class="form-group">
                                    <label for="vname" class="font-weight-bold">Hospital Name</label>
                                    <input type="text" name="hname" id="vname" value="<?php echo $col[2] ?>" class="form-control" placeholder="Enter hospital name">
                                </div>

                                <div class="form-group">
                                    <label for="vdes" class="font-weight-bold">Email</label>
                                    <input type="email" name="hemail" id="vdes" value="<?php echo $col[5] ?>" class="form-control" placeholder="Enter email address">
                                </div>

                                <div class="form-group">
                                    <label for="vqty" class="font-weight-bold">Address</label>
                                    <input type="text" name="haddress" id="vqtyad" value="<?php echo $col[3] ?>" class="form-control" placeholder="Enter address">
                                </div>

                                <div class="form-group">
                                    <label for="vqty" class="font-weight-bold">Phone</label>
                                    <input type="text" name="hphone" id="vqtyph" value="<?php echo $col[4] ?>" class="form-control" placeholder="Enter phone number">
                                </div>

                                <div class="form-group">
                                    <label for="avl_unavl" class="font-weight-bold">Active / Inactive</label>
                                    <select name="act_inact" id="avl_unavl" class="form-control">
                                        <option><?php echo $col[8] ?></option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>

                                <button type="submit" name="update" class="btn btn-primary btn-block">Update</button>
                                <a href="view_hosp.php" class="btn btn-info btn-block mt-2">View Hospitals</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("footer.php") ?>

  <script>
    function validateHospitalForm() {
        let hname = document.getElementById("vname").value.trim();
        let hemail = document.getElementById("vdes").value.trim();
        let haddress = document.getElementById("vqtyad").value.trim();
        let hphone = document.getElementById("vqtyph").value.trim(); // Phone number field
        let errors = [];

        // Validate Hospital Name (must contain letters and possibly spaces)
        if (!/[a-zA-Z\s]+/.test(hname)) {
            errors.push("Hospital name must contain only letters and spaces.");
        }

        // Validate Email
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(hemail)) {
            errors.push("Please enter a valid email address.");
        }

        // Validate Phone Number (must be exactly 10 digits)
        let phonePattern = /^[0-9]{10}$/;
        if (!phonePattern.test(hphone)) {
            errors.push("Phone number must be exactly 10 digits.");
        }

        // If there are errors, show alert
        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }
        return true;
    }
</script>

</body>
</html>

<?php
if(isset($_POST['update'])){
    $hname = $_POST['hname'];
    $hemail = $_POST['hemail'];
    $haddress = $_POST['haddress'];
    $hphone = $_POST['hphone'];
    $act_inact = $_POST['act_inact'];

    $q = mysqli_query($conn, "UPDATE `hospitals` SET `hospital_name`='$hname', `address`='$haddress', `phone`='$hphone', `email`='$hemail', `status`='$act_inact' WHERE id=$up");

    if($q){
        echo "<script>alert('Hospital updated successfully'); location.assign('view_hosp.php');</script>";
    } else {
        echo "<script>alert('Update failed')</script>";
    }
}
?>
