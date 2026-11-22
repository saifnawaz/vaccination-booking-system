<?php 
include('connection.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $del = mysqli_query($conn, "DELETE FROM `vaccines` WHERE id=$id");
    if ($del) {
        echo "<script>alert('Data deleted'); location.assign('view-vaccine.php');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Vaccination Reports</title>

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .main-wrapper {
            flex: 1;
            margin-left: 250px; /* Adjust based on your sidebar width */
            padding: 20px;
        }
        .container {
            margin-top: 20px;
        }
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #009efb !important;
            color: white !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #007bff !important;
            color: white !important;
        }
        .dataTables_filter {
            float: right;
            margin-bottom: 10px;
        }
        .dataTables_length {
            float: left;
            margin-bottom: 10px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .btn {
            margin: 2px;
        }
    </style>
</head>

<body>
<?php include('asside.php'); ?>

<div class="main-wrapper">
    <div class="container">
        <h1 class="text-center mt-5"><span style="color:#009efb">V</span>accination <span style="color:#009efb">R</span>eports</h1>

        <!-- Status Filter -->
        <div class="mb-3">
            <select id="statusFilter" class="form-control" style="width: 200px;">
                <option value="">All Status</option>
                <option value="Available">Available</option>
                <option value="Unavailable">Unavailable</option>
            </select>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table id="vaccineTable" class="table table-bordered table-striped">
                <thead style="background-color: #009efb;" class="text-white">
                    <tr>
                        <th>Vaccine Name</th>
                        <th>Vaccine Description</th>
                        <th>Vaccine Qty</th>
                        <th>Hospital Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>  
                    <?php 
                    $q = mysqli_query($conn, "SELECT * FROM `vaccines`");
                    while ($data = mysqli_fetch_array($q)) { 
                    ?>
                    <tr>
                        <td><?php echo $data['name']; ?></td>
                        <td><small><?php echo $data['vaccines_detail']; ?></small></td>
                        <td><?php echo $data['vaccines_qty']; ?></td>
                        <?php    
                        $hosid = $data[4];
                        $query1 = mysqli_query($conn,"SELECT * FROM `hospitals` WHERE id=$hosid;");
                        while($qr = mysqli_fetch_array($query1)){ 
                        ?>
                        <td><?php echo $qr['2']; ?></td>
                        <?php } ?>
                        <td><?php echo ucfirst($data['status']); ?></td>
                        <td>
                                    <a href="update_vacc.php?Uid=<?php echo $data['id']; ?>" class="btn btn-info btn-sm btn-action">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?id=<?php echo $data['id']; ?>" class="btn btn-primary btn-sm btn-action" onclick="return confirm('Are you sure you want to delete this item?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

    <!-- JS Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

    <!-- DataTables Initialization -->
    <script>
        $(document).ready(function() {
            var table = $('#vaccineTable').DataTable({
                "paging": true,
                "searching": true,
                "ordering": true,
                "lengthMenu": [5, 10, 25, 50]
            });

            // Status Filter
            $('#statusFilter').on('change', function() {
                var selectedValue = $(this).val();
                if (selectedValue) {
                    table.column(4).search('^' + selectedValue + '$', true, false).draw();
                } else {
                    table.column(4).search('').draw();
                }
            });
        });
    </script>
</body>
</html>