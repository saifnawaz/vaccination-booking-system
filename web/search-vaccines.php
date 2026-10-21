<?php
include("header.php");
include("connection.php"); // Include your database connection file

// Check the database connection
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Get the search term, sort order, and filter status from the GET request
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? mysqli_real_escape_string($conn, $_GET['sort']) : 'name';
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Build the base query
$query = "SELECT * FROM vaccines WHERE name LIKE ?";

// Add filtering if a status is selected
if ($filter_status) {
    $query .= " AND status = ?";
}

// Add sorting based on the selected column
$query .= " ORDER BY $sort";

// Prepare the SQL statement
$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    die('MySQL prepare failed: ' . mysqli_error($conn)); // If preparation failed, show the error
}

// Bind parameters based on whether filter status is set
$search_term = "%" . $search . "%"; // Add % to allow partial matching
if ($filter_status) {
    mysqli_stmt_bind_param($stmt, "ss", $search_term, $filter_status); // For two parameters: search and filter
} else {
    mysqli_stmt_bind_param($stmt, "s", $search_term); // For one parameter: search only
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
    <title>Vaccine Search</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Search, Sort, and Filter Form -->
    <div class="container py-4">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search vaccine..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="sort" class="form-control">
                <option value="name" <?php echo ($sort == 'name') ? 'selected' : ''; ?>>Sort by Name</option>
                <option value="status" <?php echo ($sort == 'status') ? 'selected' : ''; ?>>Sort by Status</option>
            </select>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="Available" <?php echo ($filter_status == 'Available') ? 'selected' : ''; ?>>Available</option>
                <option value="Unavailable" <?php echo ($filter_status == 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
            </select>
            <button type="submit" class="btn btn-primary">Apply</button>
        </form>
    </div>

    <!-- Vaccine List -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php while ($vaccine = mysqli_fetch_assoc($result)): ?>
                        <div class="vaccine-card bg-light rounded p-4 mb-3">
                            <h3 class="text-center mb-3"><?php echo htmlspecialchars($vaccine['name']); ?></h3>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($vaccine['vaccines_detail']); ?></p>
                            <p><strong>Quantity:</strong> <?php echo htmlspecialchars($vaccine['vaccines_qty']); ?></p>
                            <p><strong>Status:</strong>
                                <span class="badge <?php echo ($vaccine['status'] === 'Available') ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo htmlspecialchars($vaccine['status']); ?>
                                </span>
                            </p>
                            <div class="text-center">
                                <a href="search-hospital.php" class="btn btn-primary rounded-pill py-2 px-4">View Hospital</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if (mysqli_num_rows($result) == 0): ?>
                        <div class="alert alert-warning text-center">No vaccines found.</div>
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
