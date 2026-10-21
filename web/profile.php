<?php require_once 'auth.php'; ?>
<?php

include("header.php");
?>
<style>
    .profile-picture {
        width: 150px;
        height: 150px;
        margin-bottom: 20px;

    }

    .profile-picture img {
        width: 100%;
        height: auto;
        border-radius: 50%;
    }

    /* header {
        background-color: #007bff;
        color: white;
        padding: 10px 0;
    } */

    /* header .logo {
        font-size: 24px;
        font-weight: bold;
        margin-left: 20px;
    } */
</style>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-4 text-center">
            <div class="d-flex justify-content-center align-items-center mb-3">
                <div class="profile-picture rounded-circle overflow-hidden"
                    style="width: 150px; height: 150px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <img src="img/8121295.png" alt="User Profile Picture" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </div>
            <h2 class="mb-3"><?php
                                $id = $_SESSION['parent_id'];
                                $q = mysqli_query($conn, "SELECT * FROM `parents` WHERE `id` = $id ");
                                $udet = mysqli_fetch_array($q)
                                ?>
                <p value="<?php echo $udet[0] ?>"><?php echo $udet[1] ?></p>
            </h2>
            <p><strong>Email:</strong> <?php echo $udet[2] ?></p>
            <p><strong>Phone Number:</strong> <?php echo $udet[4] ?></p>
            <p><strong>Address:</strong> <?php echo $udet[6] ?></p>
            <a href="updateprofile.php?id=<?php echo $udet[0] ?>"
                class="btn btn-primary rounded-pill py-2 px-4">Update</a>

        </div>

        <div class="col-md-8">

            <h3 class="mt-4 d-inline-block text-primary text-uppercase border-bottom border-5">Children</h3>
            <div class="row">
                <?php
                $q = mysqli_query($conn, "SELECT * FROM `children` WHERE `parent_id` = $id ");
                while ($child = mysqli_fetch_array($q)) {
                ?>
                    <div class="col-md-6">
                        <div class="card mb-4 shadow-sm rounded-3" style="transition: transform 0.2s, box-shadow 0.2s; ">
                            <div class="card-body p-4">
                                <h5 class="card-title text-primary"><?php echo $child[2] ?></h5>
                                <p class="card-text"><strong>Last Name:</strong> <?php echo $child[3] ?></p>
                                <p class="card-text"><strong>Date of Birth:</strong> <?php echo $child[4] ?></p>
                                <p class="card-text"><strong>Gender:</strong> <?php echo $child[5] ?></p>
                                <p class="card-text"><strong>Blood Group:</strong> <?php echo $child[6] ?></p>
                                <p class="card-text"><strong>Medical Condition:</strong> <?php echo $child[7] ?></p>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>

            <div class="table-responsive mt-4">
                <?php
                $q = mysqli_query($conn, "SELECT * FROM `appointments` where parent_id='$id';");
                while ($data = mysqli_fetch_array($q)) {
                ?>
                    <h3 class="mt-4 d-inline-block text-primary text-uppercase border-bottom border-5">Appointment</h3>
                    <div class="row">
                        <div class="container mt-4">
                            <div class="row">
                                <?php
                                $q = mysqli_query($conn, "SELECT * FROM `appointments`");
                                while ($data = mysqli_fetch_array($q)) {
                                ?>
                                    <div class="col-md-6">
                                        <div class="card mb-4 shadow-sm rounded-3">
                                            <div class="card-body p-4">

                                                <?php
                                                $parent_id = $data[12];
                                                $query1 = mysqli_query($conn, "SELECT * FROM `parents` WHERE id='$parent_id'");
                                                while ($qr = mysqli_fetch_array($query1)) {
                                                ?>
                                                    <h5 class="card-title text-primary"><?php echo $qr[1]; ?> (Parent)</h5>
                                                <?php } // Closing the parent loop 
                                                ?>

                                                <?php
                                                $child_id = $data[3];
                                                $query1 = mysqli_query($conn, "SELECT * FROM `children` WHERE id='$child_id'");
                                                while ($qr = mysqli_fetch_array($query1)) {
                                                ?>
                                                    <p class="card-text"><strong>Child Name:</strong>
                                                        <?php echo $qr['first_name']; ?></p>
                                                <?php } // Closing the child loop 
                                                ?>

                                                <?php
                                                $hosid = $data[4];
                                                $query1 = mysqli_query($conn, "SELECT * FROM `hospitals` WHERE id=$hosid;");
                                                while ($qr = mysqli_fetch_array($query1)) {
                                                ?>
                                                    <p class="card-text"><strong>Hospital Name:</strong> <?php echo $qr['2']; ?></p>
                                                <?php } // Closing the hospital loop 
                                                ?>

                                                <?php
                                                $hosid = $data[5];
                                                $query1 = mysqli_query($conn, "SELECT * FROM `vaccines` WHERE id=$hosid;");
                                                while ($qr = mysqli_fetch_array($query1)) {
                                                ?>
                                                    <p class="card-text"><strong>Vaccine Name:</strong> <?php echo $qr[1] ?></p>
                                                <?php } // Closing the vaccine loop 
                                                ?>

                                                <p class="card-text"><strong>Status:</strong> <?php echo $data[9]; ?></p>
                                                <p class="card-text"><strong>Appointment Date:</strong> <?php echo $data[7]; ?></p>
                                                
                                            </div>
                                        </div>
                                    </div>
                            <?php }
                            } // Closing the appointments loop 
                            ?>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>

<?php
include("footer.php");
?>
</body>

</html>