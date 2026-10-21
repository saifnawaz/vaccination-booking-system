<?php include('connection.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Hospital Requests - Admin</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .table th { background-color: #009efb; color: white; }
    </style>
</head>
<body>
    <?php include('asside.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center"><span style="color:#009efb">H</span>ospital <span style="color:#009efb">R</span>egistration Requests</h2>
        
        <div class="table-responsive mt-4">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Hospital Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $query = mysqli_query($conn, "SELECT * FROM hospitals WHERE status = 'Pending'");
                    while ($row = mysqli_fetch_assoc($query)) {
                    ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="hospital_id" value="<?php echo $row['id']; ?>">
                                <select name="status" class="form-control mb-2">
                                    <option value="Approved">Approve</option>
                                    <option value="Rejected">Reject</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    // Handle status update
    if (isset($_POST['update_status'])) {
        $id = $_POST['hospital_id'];
        $status = $_POST['status'];
        $update = mysqli_query($conn, "UPDATE hospitals SET status='$status' WHERE id=$id");

        if ($update) {
            echo "<script>alert('Status updated successfully'); location.reload();</script>";
        }
    }
    ?>

    <?php include('footer.php'); ?>
</body>
</html>
