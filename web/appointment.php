<?php require_once 'auth.php'; ?>
<?php
include("header.php");
?>

<body>
    <!-- Appointment Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row gx-5">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="mb-4">
                        <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5">Appointment</h5>
                        <h1 class="display-4">Schedule An Appointment For Your Family</h1>
                    </div>
                    <p class="mb-5">Easily schedule an appointment with your preferred hospital or get vaccinated to protect your loved ones. Simply select the hospital, the vaccine, and the child for whom you want to make the appointment, and book a time that works for you.</p>
                    <a class="btn btn-primary rounded-pill py-3 px-5 me-3" href="">Find Hospital</a>
                </div>
                <div class="col-lg-6">
                    <div class="bg-light text-center rounded p-5">
                        <h1 class="mb-4">Book An Appointment</h1>
                        <form method="post">
                            <div class="row g-3">
                                <!-- Hospital Dropdown -->
                                <div class="col-12 col-sm-6">
                                    <select class="form-select bg-white border-0" name="hospital-cat" style="height: 55px;">
                                        <option selected disabled>Choose Hospital</option>
                                        <?php
                                        if (isset($_GET["hid"])) {
                                            $hosid = $_GET["hid"];
                                            $hosidquery = mysqli_query($conn, "SELECT * FROM `hospitals` WHERE id = $hosid");
                                            if ($hos = mysqli_fetch_array($hosidquery)) { ?>
                                                <option selected value="<?php echo $hos[0]; ?>"><?php echo $hos[2]; ?></option>
                                            <?php }
                                        } else {
                                            $q = mysqli_query($conn, "SELECT * FROM `hospitals`");
                                            while ($hos = mysqli_fetch_array($q)) { ?>
                                                <option value="<?php echo $hos[0]; ?>"><?php echo $hos[2]; ?></option>
                                        <?php }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Vaccine Dropdown -->
                                <div class="col-12 col-sm-6">
                                    <select class="form-select bg-white border-0" name="vaccine" style="height: 55px;">
                                        <option selected disabled>Select Vaccine</option>
                                        <?php
                                        if (isset($_GET["hid"])) {
                                            $hosid = $_GET["hid"];
                                            $vacidquery = mysqli_query($conn, "SELECT * FROM `vaccines` WHERE hospital_id = $hosid AND vaccines_qty > 0");
                                            while ($vac = mysqli_fetch_array($vacidquery)) { ?>
                                                <option value="<?php echo $vac[0]; ?>"><?php echo $vac[1]; ?></option>
                                            <?php }
                                        } else {
                                            $q = mysqli_query($conn, "SELECT * FROM `vaccines` WHERE vaccines_qty > 0");
                                            while ($vac = mysqli_fetch_array($q)) { ?>
                                                <option value="<?php echo $vac[0]; ?>"><?php echo $vac[1]; ?></option>
                                        <?php }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- Child Dropdown -->
                                <div class="col-12 col-sm-6">
                                    <select class="form-select bg-white border-0" name="child" style="height: 55px;">
                                        <option selected disabled>Select Child</option>
                                        <?php
                                        $id = $_SESSION['parent_id'];
                                        $q = mysqli_query($conn, "SELECT * FROM `children` WHERE `parent_id` = $id");
                                        while ($cid = mysqli_fetch_array($q)) { ?>
                                            <option value="<?php echo $cid['id'] ?>"><?php echo $cid[2] ?></option>
                                        <?php }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <input type="email" class="form-control bg-white border-0" placeholder="Your Email" name="uemail" style="height: 55px;">
                                </div>

                                <div class="col-12 col-sm-6">
                                    <input type="date" class="form-control bg-white border-0" name="date" style="height: 55px;">
                                </div>

                                <div class="col-12 col-sm-6">
                                    <input type="time" class="form-control bg-white border-0" name="time" style="height: 55px;">
                                </div>

                                <div class="col-12">
                                    <button class="btn btn-primary w-100 py-3" type="submit" name="btn_app">Make An Appointment</button>
                                </div>
                            </div>
                        </form>

                        <?php
                        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_app'])) {
                            $pname = $_SESSION['parent_name'];
                            $pchild = $_POST['child'];
                            $hospital_id = $_POST['hospital-cat'];
                            $vaccine_id = $_POST['vaccine'];
                            $email = $_POST['uemail'];
                            $date = $_POST['date'];
                            $time = $_POST['time'];
                            $created_at = date('Y-m-d H:i:s');
                            $parent_id = $_SESSION['parent_id'];

                            if (empty($date)) {
                                echo "<script>alert('Please select a valid date!');</script>";
                            } else {
                                // Check vaccine 
                                $stock_check = mysqli_query($conn, "SELECT vaccines_qty FROM `vaccines` WHERE id = $vaccine_id");
                                $stock_data = mysqli_fetch_assoc($stock_check);
                                $current_stock = $stock_data['vaccines_qty'];
                                // Book the appointment
                                if ($current_stock > 0) {

                                    $q = mysqli_query($conn, "INSERT INTO `appointments`(`parent_name`, `child_id`, `hospital_id`, `vaccine_id`, `email`, `appointment_date`, `appointment_time`, `created_at`, `parent_id`) 
                VALUES ('$pname','$pchild','$hospital_id','$vaccine_id','$email','$date','$time','$created_at','$parent_id')");

                                    if ($q) {
                                        // Minus vaccine 
                                        mysqli_query($conn, "UPDATE `vaccines` SET vaccines_qty = vaccines_qty - 1 WHERE id = $vaccine_id");

                                        // Check if new stock is 0 then make it unavailable
                                        $new_stock_check = mysqli_query($conn, "SELECT vaccines_qty FROM `vaccines` WHERE id = $vaccine_id");
                                        $new_stock_data = mysqli_fetch_assoc($new_stock_check);
                                        if ($new_stock_data['vaccines_qty'] == 0) {
                                            mysqli_query($conn, "UPDATE `vaccines` SET status = 'unavailable' WHERE id = $vaccine_id");
                                        }

                                        echo "<script>alert('Your Appointment has been booked');
                       location.assign('index.php')</script>";
                                    } else {
                                        echo "<script>alert('Error booking appointment');</script>";
                                    }
                                } else {
                                    echo "<script>alert('This vaccine is out of stock! Please choose another.');</script>";
                                }
                            }
                        }
                        ?>


                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Appointment End -->

    <!-- Footer -->
    <?php
    include("footer.php");
    ?>

    <script>
        $(document).ready(function() {
            $("form").on("submit", function(e) {
                let isValid = true;
                let today = new Date().toISOString().split('T')[0];
                let currentTime = new Date().toTimeString().split(' ')[0].slice(0, 5);


                if ($("select[name='hospital-cat']").val() === null) {
                    alert("Please select a hospital.");
                    isValid = false;
                }


                if ($("select[name='vaccine']").val() === null) {
                    alert("Please select a vaccine.");
                    isValid = false;
                }


                if ($("select[name='child']").val() === null) {
                    alert("Please select a child.");
                    isValid = false;
                }


                let email = $("input[name='uemail']").val();
                let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                    alert("Please enter a valid email address.");
                    isValid = false;
                }


                let selectedDate = $("input[name='date']").val();
                if (selectedDate === "" || selectedDate < today) {
                    alert("Please select a valid future date.");
                    isValid = false;
                }


                let selectedTime = $("input[name='time']").val();
                if (selectedTime === "") {
                    alert("Please select a time.");
                    isValid = false;
                } else if (selectedDate === today && selectedTime < currentTime) {
                    alert("Please select a valid future time.");
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>