<?php require_once 'auth.php'; require_role('admin', 'hospital'); ?>
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
    <style>
        .main-content {
            margin: 20px auto;
            max-width: 800px;
            padding: 0 15px;
        }

        .page-heading {
            text-align: center;
            font-size: 2rem;
            font-weight: 500;
            margin: 30px 0;
            color: #333;
        }

        .page-heading span {
            color: #009efb;
        }

        .card {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            border: none;
            margin-bottom: 30px;
        }

        .card-header {
            background-color: #009efb;
            padding: 15px 20px;
            border-bottom: none;
        }

        .card-header h3 {
            color: white;
            font-size: 1.5rem;
            margin: 0;
            text-align: center;
        }

        .card-body {
            padding: 30px;
            background: #fff;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            color: #333;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            height: 45px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            transition: border-color 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #009efb;
            box-shadow: none;
        }

        select.form-control {
            padding-right: 30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3E%3Cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 8px 10px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .btn-primary {
            background-color: #009efb;
            border: none;
            padding: 12px 20px;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0, 158, 251, 0.3);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #0089db;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 158, 251, 0.4);
        }

        .btn-block {
            border-radius: 4px;
        }

        .btn-info {
            background-color: #17a2b8;
            border: none;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <?php include('asside.php'); ?>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content">
                <div class="main-content">
                    <h1 class="page-heading"><span>A</span>dd <span>V</span>accine</h1>

                    <div class="card">
                        <div class="card-header">
                            <h3>Add Vaccine</h3>
                        </div>
                        <div class="card-body">
 <form action="" method="post" onsubmit="return validateForm()">
    <div class="form-group">
        <label>Vaccine Name</label>
        <input type="text" name="vname" class="form-control" id="vname" placeholder="Enter vaccine name">
    </div>

    <div class="form-group">
        <label>Vaccine Details</label>
        <input type="text" name="vdetail" class="form-control" id="vdetail" placeholder="Enter vaccine description">
    </div>

    <div class="form-group">
        <label>Vaccine Quantity</label>
        <input type="text" name="vqty" class="form-control" id="vqty" placeholder="Enter quantity">
    </div>
    <div class="form-group">
    <label for="chosehos" class="font-weight-bold">Choose Hospital</label>
    <select name="chosehos" id="chosehos" class="form-control">
        <?php
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null) { 
            $hosid = $_SESSION['user_id']; 
            if ($hosid) {
                $query1 = mysqli_query($conn, "SELECT * FROM `hospitals` WHERE id = $hosid;");
                $qr = mysqli_fetch_array($query1);
                echo "<option value='{$qr[0]}'>{$qr[2]}</option>";
            } else {
                $q = mysqli_query($conn, "SELECT * FROM `hospitals`");
                while ($cat = mysqli_fetch_array($q)) {
                    echo "<option value='{$cat[0]}'>{$cat[2]}</option>";
                }
            }
        }
        ?>
    </select>
</div>

        <div class="form-group">
        <label>Available / Unavailable</label>
        <select name="avl_unavl" class="form-control" id="avl_unavl">
            <option value="">Select Status</option>
            <option value="Available">Available</option>
            <option value="Unavailable">Unavailable</option>
        </select>
    </div>

    <button type="submit" name="add" class="btn btn-primary btn-block">Add Vaccine</button>
</form>

                            <?php
                            // Your existing PHP code for form submission
                            if (isset($_POST['add'])) {
                                $vname = $_POST['vname'];
                                $vdetail = $_POST['vdetail'];
                                $vqty = $_POST['vqty'];
                                $chosehos = $_POST['chosehos'];
                                $avl_unavl = $_POST['avl_unavl'];

                                $q = mysqli_query($conn, "INSERT INTO vaccines(`name`, `vaccines_detail`, `vaccines_qty`, `hospital_id`, `status`) 
                                 VALUES ('$vname', '$vdetail', '$vqty', '$chosehos', '$avl_unavl');");

                                if ($q) {
                                    if ($_SESSION['userrole'] == "admin") {
                                        echo "<script>alert('Vaccine added successfully');
                                      location.assign('view-vaccine.php');</script>";
                                    } else {
                                        echo "<script>alert('Vaccine added successfully');
                                           location.assign('view-hosvaccine.php');</script>";
                                    }
                                } else {
                                    echo "<script>alert('Incorrect data')</script>";
                                }
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <?php include("footer.php") ?>

</body>
<script>
    function validateForm() {
        let vname = document.getElementById("vname").value.trim();
        let vdetail = document.getElementById("vdetail").value.trim();
        let vqty = document.getElementById("vqty").value.trim();
        let chosehos = document.getElementById("chosehos").value.trim();
        let avl_unavl = document.getElementById("avl_unavl").value.trim();
        let errors = [];

        if (vname === "") {
            errors.push("Vaccine name cannot be empty.");
        } else if (!/[a-zA-Z]/.test(vname)) {
            errors.push("Vaccine name must contain at least one letter.");
        }

        if (vdetail === "") {
            errors.push("Vaccine description cannot be empty.");
        }

        if (!/^[0-9]+$/.test(vqty)) {
            errors.push("Vaccine quantity must be a number.");
        }

        if (chosehos === "") {
            errors.push("Please select a hospital.");
        }

        if (avl_unavl === "") {
            errors.push("Please select availability status.");
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }
        return true;
    }
</script>
<script>
    $(document).ready(function() {
        $("#vaccineForm").on("submit", function(e) {
            let isValid = true;

            // Validate Vaccine Name
            let vname = $("input[name='vname']").val().trim();
            if (vname === "") {
                alert("Please enter the vaccine name.");
                isValid = false;
            }

            // Validate Vaccine Details
            let vdetail = $("input[name='vdetail']").val().trim();
            if (vdetail === "") {
                alert("Please enter the vaccine details.");
                isValid = false;
            }

            // Validate Vaccine Quantity
            let vqty = $("input[name='vqty']").val().trim();
            let vqtyPattern = /^[0-9]+$/;
            if (vqty === "") {
                alert("Please enter the vaccine quantity.");
                isValid = false;
            } else if (!vqtyPattern.test(vqty)) {
                alert("Please enter a valid quantity (numbers only).");
                isValid = false;
            }

            // Validate Hospital Selection
            let chosehos = $("select[name='chosehos']").val();
            if (chosehos === "") {
                alert("Please select a hospital.");
                isValid = false;
            }

            // Validate Availability Status
            let avl_unavl = $("select[name='avl_unavl']").val();
            if (avl_unavl === "") {
                alert("Please select the availability status.");
                isValid = false;
            }

            // Prevent form submission if validation fails
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
</script>

</html>