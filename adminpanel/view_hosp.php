<?php require_once 'auth.php'; require_role('admin'); ?>
<?php include('connection.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">
    <title>Hospital Management System</title>

    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Bootstrap and DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-wrapper {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 240px;
            background-color: #343a40;
            color: #fff;
            flex-shrink: 0;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 240px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table {
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }

        .table thead {
            background-color: #009efb;
            color: white;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .table td,
        .table th {
            text-align: center;
            vertical-align: middle;
        }

        h1 {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        h1 span {
            color: #009efb;
        }

        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
            left: 240px;
            z-index: 1000;
        }

        .btn-action {
            margin: 0 3px;
            min-width: 80px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: fixed;
                z-index: 10;
            }

            .main-content {
                margin-left: 0;
                padding: 10px;
            }

            footer {
                left: 0;
            }

            h1 {
                font-size: 1.5rem;
            }

            .table-responsive {
                font-size: 14px;
            }
        }
        .main-content{
       margin-top: 100px;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <!-- Sidebar -->
        <?php include('asside.php'); ?>

        <!-- Main Content -->
        <div class="main-content">
            <h1 class="text-center"><span>H</span>ospital <span>D</span>etails</h1>
            <div class="table-responsive mt-4">
                <table id="hospitalTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Hospital Name</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q = mysqli_query($conn, "SELECT * FROM `hospitals`");
                        while ($data = mysqli_fetch_array($q)) { ?>
                            <tr>
                                <td><?php echo $data['hospital_name']; ?></td>
                                <td><?php echo $data['email']; ?></td>
                                <td><?php echo $data['address']; ?></td>
                                <td><?php echo $data['phone']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo ($data['status'] == 'active') ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($data['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="update_hosp.php?Uid=<?php echo $data['id']; ?>" class="btn btn-info btn-sm btn-action">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="post" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $data['id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm btn-action">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
   
    <?php
    if (isset($_POST['delete_id'])) {
        $id = intval($_POST['delete_id']); 
    
        $del_appointments = mysqli_query($conn, "DELETE FROM appointments WHERE vaccine_id IN (SELECT id FROM vaccines WHERE hospital_id = '$id')");
    
        if ($del_appointments) {
            $del_vaccines = mysqli_query($conn, "DELETE FROM vaccines WHERE hospital_id = '$id'");
    
            if ($del_vaccines) {
                $del_hospital = mysqli_query($conn, "DELETE FROM hospitals WHERE id = '$id'");
                
                if ($del_hospital) {
                    echo "<script>alert('Data deleted'); location.assign('view_hosp.php');</script>";
                } else {
                    echo "<script>alert('Error deleting hospital');</script>";
                }
            } else {
                echo "<script>alert('Error deleting vaccines');</script>";
            }
        } else {
            echo "<script>alert('Error deleting appointments');</script>";
        }
    }
    
    ?>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

    <!-- Initialize DataTable -->
    <script>
        $(document).ready(function () {
            $('#hospitalTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                lengthMenu: [5, 10, 25, 50],
                pageLength: 5,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records..."
                }
            });
        });
    </script>
</body>

</html>
