<?php require_once 'auth.php'; require_role('hospital'); ?>
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
    <style>
        html,
        body {
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
    </style>
    <!--[if lt IE 9]>
		<script src="assets/js/html5shiv.min.js"></script>
		<script src="assets/js/respond.min.js"></script>
	<![endif]-->

</head>

<body>
    <div class="main-wrapper">
        <?php include('asside.php'); ?>

        <div style="margin-top: 100px;" class="container mb-5">
            <h1 class="text-center"><span style="color:#009efb">V</span>accination <span style="color:#009efb">R</span>eports</h1>
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-striped">
                    <thead style="background-color: #009efb;" class="text-white">
                        <tr>
                            <th>Vaccine_name</th>
                            <th>Vaccine_description</th>
                            <th>Vaccine_qty</th>
                            <th>Hopital_name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php
                            $hosp_id = $_SESSION['user_id'];

                            $q = mysqli_query($conn, "SELECT * FROM `vaccines` WHERE hospital_id = $hosp_id");

                            while ($data = mysqli_fetch_array($q)) { ?>
                        <tr>
                            <td><?php echo $data['name']; ?></td>
                            <td><?php echo $data['vaccines_detail']; ?></td>
                            <td><?php echo $data['vaccines_qty']; ?></td>
                            <?php $hosid = $data[4];
                                $query1 = mysqli_query($conn, "SELECT * FROM `hospitals` WHERE id=$hosid;");
                                while ($qr = mysqli_fetch_array($query1)) { ?>
                                <td><?php
                                    echo $qr['2'];
                                    ?></td>
                            <?php } ?>
                            <td><?php echo $data['status']; ?></td>
                            <td>
                                <a href="update_vacc.php?Uid=<?php echo $data['id']; ?>" class="btn btn-info btn-sm btn-action">
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
                    <?php
                    ?>
                </table>
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST['delete_id'])) {
        $id = $_POST['delete_id'];
        $del = mysqli_query($conn, "DELETE FROM `vaccines` WHERE id=$id");
        if ($del) {
            echo "<script>alert('Data deleted');
            location.assign('view-hosvaccine.php');
            </script>";
        }
    }
    ?>

    <?php include('footer.php'); ?>

</body>

</html>