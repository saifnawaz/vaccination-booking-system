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

        

        <div style="margin-top: 100px; margin-left: 125px" class="container mb-5">
    <h1 class="text-center"><span style="color:#009efb">C</span>hildrens <span style="color:#009efb">D</span>etails</h1>
    
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
                    <th>Child_name</th>
                    <th>Parent_name</th>
                    <th>DOB</th>
                    <th>Gender</th>
                    <th>Blood_Group</th>
                    <th>Medical_conditions</th>
                </tr>
            </thead>
            <tbody>  
    <?php 
    $q = mysqli_query($conn, "SELECT * FROM `children`");
    while ($data = mysqli_fetch_array($q)) { 
    ?>
    <tr>
        <td><?php echo $data[2]?></td>
        <?php 
        $pid = $data[1];
        $pname_query = mysqli_query($conn, "SELECT name FROM `parents` WHERE id='$pid'");
        $pname_data = mysqli_fetch_assoc($pname_query);
        ?>
        <td><?php echo $pname_data['name']; ?></td>
        <td><?php echo $data[4]?></td>
        <td><?php echo $data[5]?></td>
        <td><?php echo $data[6]?></td>
        <td><?php echo $data[7]?></td>
    </tr>
    <?php } ?>
</tbody>

        </table>
    </div>
</div>
    </div>

    <?php
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $del = mysqli_query($conn, "DELETE FROM `vaccines` WHERE id=$id");
        if ($del) {
            echo "<script>alert('Data deleted');
            location.assign('view-vaccine.php');
            </script>";
        }
    }
    ?>

    <?php include('footer.php'); ?>
    <script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#vaccineTable').DataTable({
        pageLength: 10,
        ordering: true,
        responsive: true
    });
    
    // Custom search box
    $('#searchBox').on('keyup', function() {
        table.search(this.value).draw();
    });
});

var table = $('#vaccineTable').DataTable({
    pageLength: 10,
    ordering: true,
    responsive: true,
    columnDefs: [
        { 
            targets: -1,
            orderable: false 
        }
    ]
});

$(document).ready(function() {
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#vaccineTable')) {
        // Destroy the existing DataTable first
        $('#vaccineTable').DataTable().destroy();
    }
    
    // Initialize DataTable
    var table = $('#vaccineTable').DataTable({
        pageLength: 10,
        ordering: true,
        responsive: true,
        columnDefs: [
            { 
                targets: -1,
                orderable: false 
            }
        ]
    });
    
    // Custom search box
    $('#searchBox').on('keyup', function() {
        table.search(this.value).draw();
    });
});

</script>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

</body>
</html>

