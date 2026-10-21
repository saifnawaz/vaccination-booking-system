<?php require_once 'auth.php'; require_role('admin'); ?>
<?php include('connection.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>User Feedback</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <style>
        .feedback-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feedback-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .search-container {
            max-width: 600px;
            margin: 0 auto 30px;
        }
        .rating-stars {
            color: #ffc107;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <?php include('asside.php'); ?>
        
        <div class="container-fluid py-5">
            <div class="container">
                <h1 class="text-center mb-5">
                    <span style="color:#009efb">U</span>ser <span style="color:#009efb">F</span>eedback
                </h1>
                
                <div class="row justify-content-center mb-4">
                    <div class="col-lg-8 search-container">
                        <input type="text" id="searchInput" class="form-control rounded-pill" placeholder="Search feedback...">
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8" id="feedbackCardsContainer">
                        <?php                
                        // Fetch feedback from database
                        $feedback_query = mysqli_query($conn, "SELECT * FROM feedback ORDER BY id DESC");
                        
                        if (mysqli_num_rows($feedback_query) == 0): ?>
                            <div class="alert alert-warning text-center">No feedback found.</div>
                        <?php 
                        endif;

                        while($feedback = mysqli_fetch_array($feedback_query)){
                            $rating = intval($feedback['user_exp']); // Assuming user_exp stores numeric ratings
                        ?>
                        <div class="feedback-card bg-light rounded p-4 mb-3" 
                             data-name="<?php echo strtolower($feedback['user_name']); ?>" 
                             data-email="<?php echo strtolower($feedback['user_email']); ?>" 
                             data-feedback="<?php echo strtolower($feedback['user_exp']); ?>">
                            <h3 class="m-0"> <?php echo htmlspecialchars($feedback['user_name']); ?> </h3>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($feedback['user_email']); ?></p>
                            <p><strong>Feedback:</strong> <?php echo htmlspecialchars($feedback['user_feedback']); ?></p>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                } ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


   
    <script>
    $(document).ready(function() {
        // Search functionality
        $('#searchInput').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            
            $('.feedback-card').each(function() {
                var isVisible = 
                    $(this).attr('data-name').includes(searchTerm) ||
                    $(this).attr('data-email').includes(searchTerm) ||
                    $(this).attr('data-feedback').includes(searchTerm);
                
                $(this).toggle(isVisible);
            });
        });
    });
    </script>
    <?php include('footer.php'); ?>

</body>
</html>