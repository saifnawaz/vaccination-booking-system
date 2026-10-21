<?php include('connection.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Completed Vaccines</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <style>
        .vaccine-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .vaccine-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .search-container {
            max-width: 600px;
            margin: 0 auto 30px;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include('asside.php'); ?>
        
        <div class="container-fluid py-5">
            <div class="container">
                <h1 class="text-center mb-5">
                    <span style="color:#009efb">C</span>ompleted <span style="color:#009efb">V</span>accines
                </h1>
                
                <div class="row justify-content-center mb-4">
                    <div class="col-lg-8 search-container">
                        <input type="text" id="searchInput" class="form-control rounded-pill" placeholder="Search vaccines...">
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8" id="vaccineCardsContainer">
                        <?php               
                        $status='completed';
                        $status_query = mysqli_query($conn, "SELECT * FROM `appointments` WHERE status='$status'");
                        
                        if (mysqli_num_rows($status_query) == 0): ?>
                            <div class="alert alert-warning text-center">No completed vaccines found.</div>
                        <?php 
                        endif;

                        while($data = mysqli_fetch_array($status_query)){
                            $parent_id = $data[12];
                            $parent_query = mysqli_query($conn,"SELECT * FROM `parents` WHERE id='$parent_id'");
                            $parent_data = mysqli_fetch_array($parent_query);
                            
                            $child_id = $data[3];
                            $child_query = mysqli_query($conn,"SELECT * FROM `children` WHERE id='$child_id'");
                            $child_data = mysqli_fetch_array($child_query);
                            
                            $hosid = $data[4];
                            $hospital_query = mysqli_query($conn,"SELECT * FROM `hospitals` WHERE id=$hosid;");
                            $hospital_data = mysqli_fetch_array($hospital_query);
                            
                            $vaccine_id = $data[5];
                            $vaccine_query = mysqli_query($conn,"SELECT * FROM `vaccines` WHERE id=$vaccine_id;");
                            $vaccine_data = mysqli_fetch_array($vaccine_query);
                        ?>
                        <div class="vaccine-card bg-light rounded p-4 mb-3" 
                             data-parent="<?php echo strtolower($parent_data[1]); ?>" 
                             data-child="<?php echo strtolower($child_data['first_name']); ?>" 
                             data-hospital="<?php echo strtolower($hospital_data[2]); ?>" 
                             data-vaccine="<?php echo strtolower($vaccine_data[1]); ?>">
                            <h3 class="text-center mb-3"><?php echo $child_data['first_name']; ?></h3>
                            <p><strong>Parent Name:</strong> <?php echo $parent_data[1]; ?></p>
                            <p><strong>Hospital:</strong> <?php echo $hospital_data[2]; ?></p>
                            <p><strong>Vaccine:</strong> <?php echo $vaccine_data[1]; ?></p>
                            <p><strong>Appointment Date:</strong> <?php echo $data[7]; ?></p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-success">Completed</span>
                            </p>
                            <div class="text-center">
                                <button class="btn btn-primary rounded-pill py-2 px-4" 
                                        data-toggle="modal" 
                                        data-target="#reportModal"
                                        data-child="<?php echo $child_data['first_name']; ?>"
                                        data-parent="<?php echo $parent_data[1]; ?>"
                                        data-hospital="<?php echo $hospital_data[2]; ?>"
                                        data-vaccine="<?php echo $vaccine_data[1]; ?>"
                                        data-date="<?php echo $data[7]; ?>">
                                    View Report
                                </button>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vaccine Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-4 font-weight-bold">Child Name:</div>
                        <div class="col-8" id="modalChildName"></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 font-weight-bold">Parent Name:</div>
                        <div class="col-8" id="modalParentName"></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 font-weight-bold">Hospital:</div>
                        <div class="col-8" id="modalHospitalName"></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 font-weight-bold">Vaccine:</div>
                        <div class="col-8" id="modalVaccineName"></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 font-weight-bold">Appointment Date:</div>
                        <div class="col-8" id="modalAppointmentDate"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Search functionality
        $('#searchInput').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            
            $('.vaccine-card').each(function() {
                var isVisible = 
                    $(this).attr('data-parent').includes(searchTerm) ||
                    $(this).attr('data-child').includes(searchTerm) ||
                    $(this).attr('data-hospital').includes(searchTerm) ||
                    $(this).attr('data-vaccine').includes(searchTerm);
                
                $(this).toggle(isVisible);
            });
        });

        // Modal population
        $('#reportModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var childName = button.data('child');
            var parentName = button.data('parent');
            var hospitalName = button.data('hospital');
            var vaccineName = button.data('vaccine');
            var appointmentDate = button.data('date');

            var modal = $(this);
            modal.find('#modalChildName').text(childName);
            modal.find('#modalParentName').text(parentName);
            modal.find('#modalHospitalName').text(hospitalName);
            modal.find('#modalVaccineName').text(vaccineName);
            modal.find('#modalAppointmentDate').text(appointmentDate);
        });
    });
    </script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">

</body>
</html>