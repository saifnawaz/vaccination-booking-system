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

        /* Ensure the heading is visible */
        .page-heading {
            text-align: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin-top: 50px;
        }
        
    </style>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
            <?php 
            if(isset($_GET['Uid'])){
                $up=$_GET['Uid'];
                $query=mysqli_query($conn,"SELECT * FROM `hospitals` WHERE id=$up");
                $col=mysqli_fetch_array($query);
            }
            ?>
        <?php 
        include("asside.php");
         ?>

        <div class="container-fluid">
            <div class="main-content">
                <div class="container my-5">
                    <!-- Corrected Heading -->
                    <h1 class="page-heading"><span style="color:#009efb">A</span>dd <span style="color:#009efb">V</span>accine</h1>

                    <div class="card shadow-lg">
                        <div class="card-header text-center">
                            <h3>Add Vaccine</h3>
                        </div>
                        <div class="card-body">
                            <form action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="vname" class="font-weight-bold">Hospital_name</label>
                                    <input type="text" name="hname" id="vname" value="<?php echo $col [2] ?>" class="form-control" placeholder="Enter vaccine name">
                                </div>

                                <div class="form-group">
                                    <label for="vdetail" class="font-weight-bold">Email</label>
                                    <input type="email" name="hemail" id="vdes" value="<?php echo $col [5] ?>" class="form-control" placeholder="Enter vaccine description">
                                </div>

                                <div class="form-group">
                                    <label for="vqty" class="font-weight-bold">Address</label>
                                    <input type="text" name="haddress" id="vqty"value="<?php echo $col [3] ?>" class="form-control" placeholder="Enter quantity">
                                </div>
                                <div class="form-group">
                                    <label for="vqty" class="font-weight-bold">Phone</label>
                                    <input type="text" name="hphone" id="vqty"value="<?php echo $col [4] ?>" class="form-control" placeholder="Enter quantity">
                                </div>
                                <div class="form-group">
                                    <label for="avl_unavl" class="font-weight-bold">Active / Inactive</label>
                                    <select name="act_inact" id="avl_unavl" class="form-control">
                                        <option><?php echo $col [8] ?></option>
                                        <option value="active">active</option>
                                        <option value="inactive">inactive</option>
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

</body>
</html>

<?php
if(isset($_POST['update'])){
    $hname=$_POST['hname'];
   $hemail=$_POST['hemail'];
   $haddress=$_POST['haddress'];
   $hphone=$_POST['hphone'];
   $act_inact=$_POST['act_inact'];
$q = mysqli_query($conn, "UPDATE `hospitals` SET `hospital_name`='$hname',`address`='$haddress',`phone`='$hphone',`email`='$hemail',`status`='$act_inact' WHERE id=$up");


 if($q){
   echo "<script>alert('Hospitals updated successfuly');
   location.assign('view_hosp.php');
   </script>";
 }
 else{
    echo "<script>alert('Updated failed')</script>";
 }
}

?>

