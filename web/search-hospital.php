<?php
include("header.php");
include("connection.php");


if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? mysqli_real_escape_string($conn, $_GET['sort']) : 'hospital_name';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

$query = "SELECT * FROM hospitals WHERE hospital_name LIKE ?";

if ($filter_status) {
    $query .= " AND status = ?";
}

$query .= " ORDER BY $sort";

// Prepare the SQL statement
$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    die('MySQL prepare failed: ' . mysqli_error($conn)); 
}

$search_term = "%" . $search . "%"; 
if ($filter_status) {
    mysqli_stmt_bind_param($stmt, "ss", $search_term, $filter_status); // search and filter
} else {
    mysqli_stmt_bind_param($stmt, "s", $search_term); // search only
}

// Execute the query
mysqli_stmt_execute($stmt);

// Get the result
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Search</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Search, Sort, and Filter Form -->
    <div class="container py-4">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search hospital..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="sort" class="form-control">
                <option value="hospital_name" <?php echo ($sort == 'hospital_name') ? 'selected' : ''; ?>>Sort by Name</option>
                <option value="status" <?php echo ($sort == 'status') ? 'selected' : ''; ?>>Sort by Status</option>
            </select>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="Active" <?php echo ($filter_status == 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Inactive" <?php echo ($filter_status == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>
    </div>

    <!-- Hospital List -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php while ($hospital = mysqli_fetch_assoc($result)): ?>
                        <div class="hospital-card bg-light rounded p-4 mb-3">
                            <h3 class="text-center mb-3"><?php echo htmlspecialchars($hospital['hospital_name']); ?></h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($hospital['email']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($hospital['address']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($hospital['phone']); ?></p>
                            <p><strong>Status:</strong>
                                <span class="badge <?php echo ($hospital['status'] === 'Active') ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo htmlspecialchars($hospital['status']); ?>
                                </span>
                            </p>
                            <div class="text-center">
                                <a href="appointment.php?hid=<?php echo $hospital['id'];?>" class="btn btn-primary rounded-pill py-2 px-4">Book Appointment</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($result) == 0): ?>
                        <div class="alert alert-warning text-center">No hospitals found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include("footer.php"); ?>

    <?php
    // Close the statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    ?>
</body>
</html>
