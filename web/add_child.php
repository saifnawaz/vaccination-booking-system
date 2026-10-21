<?php require_once 'auth.php'; ?>
<?php
include("header.php");
?>

    <style>
        select.form-select {
            border: 1px solid #ced4da !important;
        }
    </style>
    <!-- Add child start  -->
    <div class="container-fluid py-5">
        <div class="container mt-5">
            <!-- Child Form -->
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card shadow-lg border-0 rounded-4">
                            <div class="card-body p-4">
                                <h4 class="text-center text-primary fw-bold mb-4">Add Child</h4>
                                <form action="" method="POST" onsubmit="return validateForm()">
    <div class="row g-3">
        <!-- Child First Name -->
        <div class="col-md-6">
            <label class="form-label fw-semibold">Child's First Name</label>
            <input type="text" class="form-control rounded-3 p-2" name="child_first_name" id="child_first_name" placeholder="Enter First Name">
        </div>
        <!-- Child Last Name -->
        <div class="col-md-6">
            <label class="form-label fw-semibold">Child's Last Name</label>
            <input type="text" class="form-control rounded-3 p-2" name="child_last_name" id="child_last_name" placeholder="Enter Last Name">
        </div>

        <!-- Date of Birth -->
        <div class="col-md-6">
            <label class="form-label fw-semibold">Date of Birth</label>
            <input type="date" class="form-control rounded-3 p-2" name="dob" id="dob">
        </div>

        <!-- Gender -->
        <div class="col-md-6">
            <label class="form-label fw-semibold">Gender</label>
            <select class="form-select rounded-3 p-2" name="gender" id="gender">
                <option value="" selected disabled>Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <!-- Blood Group -->
        <div class="col-md-6">
            <label class="form-label fw-semibold">Blood Group</label>
            <input type="text" class="form-control rounded-3 p-2" name="blood_gr" id="blood_gr" placeholder="Enter Blood Group">
        </div>

        <!-- Medical Condition -->
        <div class="col-md-6">
            <label class="form-label fw-semibold">Medical Condition</label>
            <input type="text" class="form-control rounded-3 p-2" name="medi_con" id="medi_con" placeholder="Enter Medical Condition">
        </div>

        <!-- Submit Button -->
        <div class="col-12 mt-3">
            <button type="submit" name="add_child" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm">Add Child</button>
        </div>
    </div>
</form>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if (isset($_POST["add_child"])) {
                $parent_id = $_SESSION['parent_id'];
                $child_firstname = $_POST["child_first_name"];
                $child_lastname = $_POST["child_last_name"];
                $dob = $_POST["dob"];
                $gender = $_POST["gender"];
                $blood_group = $_POST["blood_gr"];
                $medical_condition = $_POST["medi_con"];
                $created_at = date('Y-m-d H:i:s');
                $q = mysqli_query($conn, "INSERT INTO `children`(`parent_id`,`first_name`, `last_name`, `dob`, `gender`, `blood_group`, `medical_conditions`, `created_at`) VALUES ('$parent_id','$child_firstname','$child_lastname',' $dob','$gender','$blood_group','$medical_condition','$created_at')");
                if ($q) {
                    echo ("<script> alert('Your Child has been added successfully')</script>");
                } else {
                    echo ("<script> alert('Failed to add child')</script>");
                }
            }
            ?>

        </div>
    </div>
    <?php
    include("footer.php")
    ?>
   
<script>
    function validateForm() {
        let childFirstName = document.getElementById("child_first_name").value.trim();
        let childLastName = document.getElementById("child_last_name").value.trim();
        let dob = document.getElementById("dob").value.trim();
        let gender = document.getElementById("gender").value.trim();
        let bloodGroup = document.getElementById("blood_gr").value.trim();
        let medicalCondition = document.getElementById("medi_con").value.trim();
        let errors = [];

        if (childFirstName === "") {
            errors.push("Child's first name cannot be empty.");
        } else if (!/^[a-zA-Z\s]+$/.test(childFirstName)) {
            errors.push("Child's first name must contain only letters and spaces.");
        }

        if (childLastName === "") {
            errors.push("Child's last name cannot be empty.");
        } else if (!/^[a-zA-Z\s]+$/.test(childLastName)) {
            errors.push("Child's last name must contain only letters and spaces.");
        }

        if (dob === "") {
            errors.push("Date of birth cannot be empty.");
        }

        if (gender === "") {
            errors.push("Please select gender.");
        }

        if (bloodGroup === "") {
            errors.push("Blood group cannot be empty.");
        } else if (!/^[A-Za-z]+([+-])?$/.test(bloodGroup)) {
            errors.push("Please enter a valid blood group.");
        }

        if (medicalCondition === "") {
            errors.push("Medical condition cannot be empty.");
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }
        return true;
    }
</script>
    </body>

    </html>