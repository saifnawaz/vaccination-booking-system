<style>
    .vaccine-card, .hospital-card {
    height: 100%; /* Ensures full height */
    display: flex;
    flex-direction: column;
}

.vaccine-card p, .hospital-card p {
    flex-grow: 1; /* Push content upwards */
}

.vaccine-card .text-center, .hospital-card .text-center {
    margin-top: auto;
}

</style>
<?php
include("connection.php");

$category = isset($_POST['category']) ? mysqli_real_escape_string($conn, $_POST['category']) : '';
$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category'])) {
    
    if ($category == '1') { 
        $query = "SELECT * FROM hospitals WHERE hospital_name LIKE ? AND status = 'active' LIMIT 6";
    } elseif ($category == '2') {
        $query = "SELECT * FROM vaccines WHERE name LIKE ? AND status = 'available' LIMIT 6";
    } else {
        $query = "SELECT * FROM hospitals WHERE hospital_name LIKE ? LIMIT 6";
    }

    
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
        die('MySQL prepare failed: ' . mysqli_error($conn)); 
    }

    
    $search_term = "%" . $search . "%"; 
    mysqli_stmt_bind_param($stmt, "s", $search_term);
    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        // Get the result
        $result = mysqli_stmt_get_result($stmt);
        ob_start();

       
        ?>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="row">
                        <?php if ($category == '1'): ?>
                            <!-- Display Hospitals -->
                            <?php while ($hospital = mysqli_fetch_assoc($result)): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="hospital-card bg-light rounded p-4" style="height: 100%;">
                                        <h3 class="text-center mb-3"><?php echo ($hospital['hospital_name']); ?></h3>
                                        <p><strong>Email:</strong> <?php echo $hospital['email']; ?></p>
                                        <p><strong>Address:</strong> <?php echo ($hospital['address']); ?></p>
                                        <p><strong>Phone:</strong> <?php echo ($hospital['phone']); ?></p>
                                        <p><strong>Status:</strong>
                                            <span class="badge <?php echo ($hospital['status'] === 'Active') ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo ($hospital['status']); ?>
                                            </span>
                                        </p>
                                        <div class="text-center">
                                            <a href="appointment.php?hid=<?php echo $hospital['id'];?>" class="btn btn-primary rounded-pill py-2 px-4">Book Appointment</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php elseif ($category == '2'): ?>
                            <!-- Display Vaccines -->
                            <?php while ($vaccine = mysqli_fetch_assoc($result)): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="vaccine-card bg-light rounded p-4" style="height: 100%;">
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
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (mysqli_num_rows($result) == 0): ?>
                        <div class="alert alert-warning text-center">No results found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
       
        echo ob_get_clean(); 
   
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    } else {
        echo 'Error executing the query: ' . mysqli_error($conn);
    }
    exit; 
}
?>



