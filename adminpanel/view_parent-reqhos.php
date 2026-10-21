<?php include('connection.php'); ?>
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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>





    
        <style>
        html, body {
    height: 100%;
    width: 90%;
    margin-left: 120px;
    padding: 0;
    display: flex;
    flex-direction: column;
}

.main-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.container {
    flex: 1;
}

footer {
    background: #333;
    color: white;
    text-align: center;
    padding: 10px;
    position: relative;
    bottom: 0;
    width: 100%;
}
/* DataTables customization */
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #009efb !important;
    color: white !important;
    border: none;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #007bff !important;
    color: white !important;
}

.dataTables_wrapper .dataTables_filter {
    float: right;
    margin-bottom: 10px;
}

.dataTables_wrapper .dataTables_length {
    float: left;
    margin-bottom: 10px;
}

#searchBox {
    border: 1px solid #ddd;
    padding: 8px;
    border-radius: 4px;
}

    </style>
    <!--[if lt IE 9]>
		<script src="assets/js/html5shiv.min.js"></script>
		<script src="assets/js/respond.min.js"></script>
	<![endif]-->

</head>

<body>
    <div class="main-wrapper">
    <?php include('asside.php'); ?>

    <?php
            if (isset($_GET['id'])&& isset($_POST['status'])) {
                $id = $_GET['id'];
                $status=$_POST['status'];
                $update = mysqli_query($conn, "UPDATE `appointments` SET `status`='$status' WHERE id=$id");
                if ($update) {
                   $hospital_id=$_SESSION['user_id'];
                     echo "<script>alert('Status Updated');
                    location.assign('view_parent-reqhos.php?hosid=$hospital_id');
                    </script>";
                }
            }
        
        ?>

        <div style="margin-top: 100px; margin-left: 125px" class="container mb-5">
    <h1 class="text-center"><span style="color:#009efb">P</span>arent <span style="color:#009efb">R</span>equests</h1>
    
    <!-- Search Box -->
    <div class="row mb-3">
        <div class="col-md-4">
            <!-- <input type="text" id="searchBox" class="form-control" placeholder="Search anything..."> -->
        </div>
    </div>

    <div class="table-responsive mt-4">
        <table id="vaccineTable" class="table table-bordered table-striped">
            <thead style="background-color: #009efb;" class="text-white">
                <tr>
                    <th>Parent_Name</th>
                    <th>Child_Name</th>
                    <th>Hospital_Name</th>
                    <th>Vaccine_Name</th>
                    <th>Status</th>
                    <th>Appointment_Date</th>
                </tr>
            </thead>
            <tbody>
    <?php 
    if(isset($_GET['hosid'])){
        $hosid=$_GET['hosid'];
    
    $q = mysqli_query($conn, "SELECT * FROM `appointments` where hospital_id='$hosid'");
    while ($data = mysqli_fetch_array($q)){  
    ?>
    <tr>
        <?php
        $parent_id = $data[12];
        $query1 = mysqli_query($conn,"SELECT * FROM `parents` WHERE id='$parent_id'");
        while($qr = mysqli_fetch_array($query1)){ 
        ?>
        <td><?php echo $qr[1]; ?></td>
        <?php }  ?>
        
        <?php    
        $child_id = $data[3];
        $query1 = mysqli_query($conn,"SELECT * FROM `children` WHERE id='$child_id'");
        while($qr = mysqli_fetch_array($query1)){ 
        ?>
        <td><?php echo $qr['first_name']; ?></td>
        <?php }  ?>
        
        <?php    
        $hosid = $data[4];
        $query1 = mysqli_query($conn,"SELECT * FROM `hospitals` WHERE id=$hosid;");
        while($qr = mysqli_fetch_array($query1)){ 
        ?>
        <td><?php echo $qr['2']; ?></td>
        <?php } ?>

        <?php
        $hosid = $data[5];
        $query1 = mysqli_query($conn,"SELECT * FROM `vaccines` WHERE id=$hosid;");
        while($qr = mysqli_fetch_array($query1)){ 
        ?>
        <td><?php echo $qr[1] ?></td>
        <?php }?>
        
        <td>
        <form method="POST" action="view_parent-reqhos.php?id=<?php echo $data[0];?>">
            <div class="form-row align-items-center">
                <div class="col-auto">
                    <select name="status" id="avl_unavl" class="form-control">
                        <option value=""> <?php echo $data[9]; ?> </option>
                        <option value="Approved" class="text-primary">Approved</option>
                        <option value="Rejected" class="text-danger">Rejected</option>
                        <option value="Completed" class="text-success">Completed</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </form>

        </td>
        <td>
            <?php echo $data[7]; ?>
        </td>
    </tr>
    <?php }
}  ?>
</tbody>

        </table>
    </div>
</div>
    </div>


    <?php include('footer.php'); ?>


</body>
</html>


