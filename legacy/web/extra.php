<?php
if (isset($_GET["id"])) {
    $vacid = $_GET["id"];
    // Perform a query to get the vaccine with the given ID
    $vacidquery = mysqli_query($conn, "SELECT * FROM `vaccines` WHERE id = $vacid");

    // Check if the query returned a result
    if (mysqli_num_rows($vacidquery) > 0) {
        // Fetch the vaccine data
        $vacidquery_result = mysqli_fetch_assoc($vacidquery);
    }

    // If $vacid is set, fetch all vaccines for the dropdown
    if (empty($vacid)) {
        $q = mysqli_query($conn, "SELECT * FROM `vaccines`");
        while ($vac = mysqli_fetch_array($q)) {
            ?>
            <option value="<?php echo $vac['id']; ?>"><?php echo $vac['name']; ?></option>
            <?php
        }
    } else {
        // Display the name of the selected vaccine
        ?>
        <option selected value="<?php echo htmlspecialchars($vacidquery_result['id']); ?>"><?php echo htmlspecialchars($vacidquery_result['name']); ?></option>

        <?php
    }
}
?>



<!-- notification -->


<?php

// Include database connection

// Get tomorrow's date
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Get parent ID from session
$parent_id = $_SESSION['parent_id'];

// Query to fetch tomorrow's appointment for the logged-in parent
$query = "SELECT 
    CONCAT(c.first_name, ' ', c.last_name) as child_name,
    a.appointment_date,
    a.appointment_time,
    h.hospital_name,
    v.name as vaccine_name
FROM appointments a
JOIN children c ON a.child_id = c.id
JOIN hospitals h ON a.hospital_id = h.id
JOIN vaccines v ON a.vaccine_id = v.id
WHERE a.parent_id = 2
LIMIT 0, 25;";

$result = mysqli_query($conn, $query);
$appointments = [];

// Fetch data
while ($row = mysqli_fetch_assoc($result)) {
    $appointments[] = $row;
}

// Close database connection
?>

<!-- JavaScript Alert to Show Notification -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        <?php if (!empty($appointments)) { ?>
            let message = "📢 Reminder: Your child's vaccine appointment is tomorrow!\n\n";
            <?php foreach ($appointments as $app) { ?>
                message += "👶 Child: <?php echo $app['child_name']; ?>\n";
                message += "💉 Vaccine: <?php echo $app['vaccine_name']; ?>\n";
                message += "🏥 Hospital: <?php echo $app['hospital_name']; ?>\n";
                message += "📅 Date: <?php echo $app['appointment_date']; ?>\n";
                message += "⏰ Time: <?php echo $app['appointment_time']; ?>\n\n";
            <?php } ?>
            alert(message);
        <?php } ?>
    });
</script>

<!-- end notification -->