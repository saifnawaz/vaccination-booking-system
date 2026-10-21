<?php
include('searchforindex.php');
include("header.php");
// if ($_SESSION['role'] !== "parent") {
//     echo ("<script> location.assign('login.php')</script>");
// } else {    

?>

<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 mb-5 vaccine-header"
    style="background-image: url('https://www.cabinet-zenou.fr/images/blog/91_l-employeur-peut-il-obliger-son-salarie-de-se-vacciner-contre-le-virus-covid-19.jpg'); background-size: cover; background-position: center;">
    <div class="container py-5">
        <div class="row justify-content-start">
            <div class="col-lg-8 text-center text-lg-start">
                <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5"
                    style="border-color: rgba(256, 256, 256, .3) !important;">Vaccination Saves Lives</h5>
                <h3 class="display-1 text-white mb-md-4">Protect your child Protect their future.</h3>
                <div class="pt-2">
                    <a href="search-hospital.php" class="btn btn-light rounded-pill py-md-3 px-md-5 mx-2">Find A Hospital</a>
                    <!-- <a href="sear" class="btn btn-outline-light rounded-pill py-md-3 px-md-5 mx-2">Make an
                        Appointment</a> -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search for Hospitals and Vaccines Starts -->
<div>
    <div class="container py-5">
        <div class="text-center mx-auto mb-5" style="max-width: 500px;">
            <h5 class="d-inline-block text-white text-uppercase border-bottom border-5">Find A Hospital & Vaccines</h5>
            <h1 class="display-4 mb-4">Find Hospitals & Vaccines</h1>
            <h5 class="text-white fw-normal">Discover the nearest hospitals and available vaccines. Stay healthy with the best care options nearby.</h5>
        </div>
        <div class="mx-auto" style="width: 100%; max-width: 600px;">
            <!-- Search Form -->
            <form id="searchForm" class="input-group">
                <select name="category" id="category" class="form-select border-primary w-25" style="height: 60px;">
                    <option value="1" selected>Hospital</option>
                    <option value="2">Vaccine</option>
                </select>
                <input type="text" name="search" id="search" class="form-control border-primary w-50" placeholder="Search location or vaccine">
                <button type="button" id="searchButton" class="btn btn-primary border-0 w-25">Search</button>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div id="resultsSection" class="container-fluid py-5" style="display: none; opacity: 0;">
        <div class="container">
           
           
        </div>
    </div>
</div>
<!-- Search for Hospitals and Vaccines Ends -->

<!-- About Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row gx-5">
            <div class="col-lg-5 mb-5 mb-lg-0" style="min-height: 500px;">
                <div class="position-relative h-100">
                    <img class="position-absolute w-100 h-100 rounded" src="img/about-us.jpg" style="object-fit: cover;">
                </div>
            </div>
            <div class="col-lg-7">
                <div class="mb-4">
                    <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5">About Us</h5>
                    <h1 class="display-4">Best Medical Care For Yourself and Your Family</h1>
                </div>
                <p>At our website, we provide top-quality healthcare services, prioritizing the well-being of you and
                    your loved ones. Our team is committed to delivering exceptional care, from routine checkups to
                    specialized treatments.

                    We specialize in offering safe, effective vaccinations to ensure your family stays protected from
                    preventable diseases. With a dedicated team of professionals, we offer personalized care to make
                    sure every patient receives the right protection at the right time.</p>
                <div class="row g-3 pt-3">
                    <div class="col-sm-3 col-6">
                        <div class="bg-light text-center rounded-circle py-4">
                            <i class="fa fa-3x fa-syringe text-primary mb-3"></i>
                            <h6 class="mb-0">Flu Vaccine<small class="d-block text-primary">Seasonal Protection</small>
                            </h6>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="bg-light text-center rounded-circle py-4">
                            <i class="fa fa-3x fa-shield-alt text-primary mb-3"></i>
                            <h6 class="mb-0">COVID-19 Vaccine<small class="d-block text-primary">Stay Safe</small></h6>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="bg-light text-center rounded-circle py-4">
                            <i class="fa fa-3x fa-clipboard-check text-primary mb-3"></i>
                            <h6 class="mb-0">Routine Vaccines<small class="d-block text-primary">For All Ages</small>
                            </h6>
                        </div>
                    </div>
                    <div class="col-sm-3 col-6">
                        <div class="bg-light text-center rounded-circle py-4">
                            <i class="fa fa-3x fa-virus text-primary mb-3"></i>
                            <h6 class="mb-0">Travel Vaccines<small class="d-block text-primary">Stay Protected
                                    Globally</small></h6>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- About End -->


<!-- Services Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="text-center mx-auto mb-5" style="max-width: 500px;">
            <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5">Vaccination Services</h5>
            <h1 class="display-4">Comprehensive Vaccines for Your Protection</h1>
        </div>
        <div class="row g-5">
            <div class="col-lg-4 col-md-6">
                <div
                    class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="service-icon mb-4">
                        <i class="fa fa-2x fa-syringe text-white"></i>
                    </div>
                    <h4 class="mb-3">Flu Vaccination</h4>
                    <p class="m-0">Get vaccinated to protect yourself and your family from seasonal flu outbreaks. Safe
                        and effective protection for all ages.</p>

                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div
                    class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="service-icon mb-4">
                        <i class="fa fa-2x fa-shield-alt text-white"></i>
                    </div>
                    <h4 class="mb-3">COVID-19 Vaccination</h4>
                    <p class="m-0">Protect yourself and others from COVID-19 with the latest vaccines available. Stay
                        safe and help reduce the spread of the virus.</p>

                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div
                    class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="service-icon mb-4">
                        <i class="fa fa-2x fa-heart text-white"></i>
                    </div>
                    <h4 class="mb-3">Routine Vaccines</h4>
                    <p class="m-0">Ensure you and your family stay up-to-date with necessary vaccines like MMR, tetanus,
                        and more. Routine vaccination for better health.</p>

                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div
                    class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="service-icon mb-4">
                        <i class="fa fa-2x fa-baby-carriage text-white"></i>
                    </div>
                    <h4 class="mb-3">Childhood Vaccines</h4>
                    <p class="m-0">Protect your little ones with essential childhood vaccinations like DTP, polio, and
                        more. Ensure a healthy start to life for your children.</p>

                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div
                    class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="service-icon mb-4">
                        <i class="fa fa-2x fa-globe-americas text-white"></i>
                    </div>
                    <h4 class="mb-3">Travel Vaccines</h4>
                    <p class="m-0">Prepare for international travel with recommended vaccinations. Stay safe and avoid
                        health risks while abroad.</p>

                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div
                    class="service-item bg-light rounded d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="service-icon mb-4">
                        <i class="fa fa-2x fa-stethoscope text-white"></i>
                    </div>
                    <h4 class="mb-3">Vaccine Consultations</h4>
                    <p class="m-0">Need guidance on vaccines? Our experts offer consultations to help you choose the
                        right vaccines for you and your family.</p>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Services End -->


<div class="container-fluid bg-primary my-5 py-5">
    <!-- Testimonial Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 500px;">
                <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5">Testimonial</h5>
                <h1 class="display-4">Patients Say About Our Services</h1>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="owl-carousel testimonial-carousel">
                        <?php
                        $query = mysqli_query($conn, "SELECT * FROM `feedback`"); // Closing the query correctly with a semicolon
                        while ($query1 = mysqli_fetch_assoc($query)) { // Corrected to use 'while' to loop through the results
                            // Now you can work with $query1, which will be an associative array
                            // Example: echo $query1['column_name'];
                            
                        ?>
                            <div class="testimonial-item text-center">
                                <div class="position-relative mb-5">
                                    <img class="img-fluid rounded-circle mx-auto" src="img/testimonial-1.jpg" alt="">
                                    <div class="position-absolute top-100 start-50 translate-middle d-flex align-items-center justify-content-center bg-white rounded-circle"
                                        style="width: 60px; height: 60px;">
                                        <i class="fa fa-quote-left fa-2x text-primary"></i>
                                    </div>
                                </div>
                                <p class="fs-4 fw-normal"><?php echo $query1['user_feedback'] ?></p>
                                <hr class="w-25 mx-auto">
                                <h3><?php echo $query1['user_name'] ?></h3>

                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Testimonial End -->

<?php
if (isset($_SESSION['parent_id']) == null) {
} else {
    $_SESSION['parent_id'];
?>

    <!-- Feedback Start -->
    <div class="container-fluid bg-primary my-5 py-5">
        <div class="container py-5">
            <div class="row gx-5">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="mb-4">
                        <h5 class="d-inline-block text-white text-uppercase border-bottom border-5">Feedback</h5>
                        <h1 class="display-4">We Value Your Feedback</h1>
                    </div>
                    <p class="text-white mb-5">At our organization, your experience matters to us. We are constantly
                        striving to improve our services, and your feedback plays a vital role in helping us achieve that.
                        Whether it’s a suggestion for improvement, a comment on your experience, or simply letting us know
                        how we’re doing, we want to hear from you. Your input allows us to better understand your needs and
                        ensure we’re offering the best possible service for you and your family. Please take a moment to
                        share your thoughts with us – your voice makes a difference!</p>
                    <a class="btn btn-dark rounded-pill py-3 px-5 me-3" href="">Contact Support</a>
                    <a class="btn btn-outline-dark rounded-pill py-3 px-5" href="">Learn More</a>
                </div>
                <div class="col-lg-6">
                    <div class="bg-white text-center rounded p-5">
                        <h1 class="mb-4">Provide Your Feedback</h1>
                        <form method="post" class="p-4 bg-white shadow-lg rounded-3 ">
                            <h4 class="text-center text-primary mb-3">We Value Your Feedback</h4>

                            <div class="mb-3">
                                <textarea class="form-control border rounded-3 p-3" placeholder="Your Feedback" rows="4" name="usr_feedback"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="rating" class="form-label fw-semibold">Rate Your Experience</label>
                                <select class="form-select border rounded-3 p-2" id="rating" name="usr_exp">
                                    <option selected disabled>Choose Rating</option>
                                    <option value="1">Poor</option>
                                    <option value="2">Fair</option>
                                    <option value="3">Good</option>
                                    <option value="4">Very Good</option>
                                    <option value="5">Excellent</option>
                                </select>
                            </div>

                            <button class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm" type="submit" name="btn_sbt">
                                Submit Feedback
                            </button>
                        </form>

                    <?php
                    if (isset($_POST['btn_sbt'])) {
                        $uname = $_SESSION['parent_name'];
                        $uemail = $_SESSION['email'];
                        $ufeed = $_POST['usr_feedback'];
                        $uexp = $_POST['usr_exp'];
                        $q = mysqli_query($conn, "INSERT INTO `feedback`(`user_name`, `user_email`, `user_feedback`, `user_exp`) VALUES ('$uname','$uemail','$ufeed','$uexp')");

                        if ($q) {
                            echo ("<script> alert('Thanks for your Feedback')</script>");
                        } else {

                            echo ("<script> alert('Please add correct info')</script>");
                        }
                    }
                }
                    ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Feedback End-->

    <!-- Footer Start -->
    <?php
    include("footer.php")
    ?>
    <!-- Footer End -->

    <script>
        // Search functionality
        document.getElementById('searchButton').addEventListener('click', function() {
            let category = document.getElementById('category').value;
            let search = document.getElementById('search').value;


            let formData = new FormData();
            formData.append('category', category);
            formData.append('search', search);


            let xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('resultsSection').innerHTML = xhr.responseText;
                }
            };
            xhr.send(formData);
        });

        $(document).ready(function() {
            $("form").on("submit", function(e) {
                let isValid = true;

                let feedback = $("textarea[name='usr_feedback']").val().trim();
                if (feedback === "") {
                    alert("Please provide your feedback.");
                    isValid = false;
                }


                if ($("select[name='usr_exp']").val() === null) {
                    alert("Please rate your experience.");
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });

        // Show the search results section

        const searchButton = document.getElementById("searchButton");
const searchInput = document.getElementById("search");
const categorySelect = document.getElementById("category");
const resultsSection = document.getElementById("resultsSection");


function triggerAnimation() {
    
    resultsSection.style.display = "block";
    resultsSection.style.opacity = "0";
    resultsSection.style.transform = "translateY(-50px)";
    resultsSection.style.transition = "none"; 
    
    setTimeout(() => {
        resultsSection.style.transition = "transform 0.6s cubic-bezier(0.23, 1, 0.32, 1), opacity 0.6s ease-out";
        resultsSection.style.opacity = "1";
        resultsSection.style.transform = "translateY(0)";
    }, 50);
}


searchButton.addEventListener("click", triggerAnimation);


searchInput.addEventListener("input", triggerAnimation);
categorySelect.addEventListener("change", triggerAnimation);



    </script>