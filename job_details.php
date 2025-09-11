<?php
// Error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set proper content type and caching headers
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: public, max-age=300'); // Cache for 5 minutes
header('Vary: Accept-Encoding');

// Handle options request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/db.php';
require_once 'classes/Job.php';

// Function to log errors
function logError($message, $context = []) {
    $logFile = __DIR__ . '/logs/error.log';
    $logDir = dirname($logFile);
    
    // Create logs directory if it doesn't exist
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' Context: ' . json_encode($context) : '';
    $logMessage = "[$timestamp] $message$contextStr\n";
    
    error_log($logMessage, 3, $logFile);
}

require_once 'config/db.php';
require_once 'classes/Job.php';

// Set headers for caching and content type
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: public, max-age=300'); // Cache for 5 minutes
header('Vary: Accept-Encoding');

// Enable CORS for specific domains if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Initialize error variable
$error = null;

// Get the slug from the URL and sanitize it
$slug = isset($_GET['slug']) ? trim(filter_var($_GET['slug'], FILTER_SANITIZE_URL)) : '';

// Validate slug
if (empty($slug)) {
    $error = "Job not found. Please check the URL and try again.";
} else {
    // Add a proper request path to the log
    $requestUri = $_SERVER['REQUEST_URI'] ?? 'Unknown URI';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User Agent';
    error_log("Accessing job details - Slug: {$slug}, URI: {$requestUri}, User Agent: {$userAgent}");
}

try {
    // Initialize Job class
    $job = new Job($conn);

    // Get job details
    $jobDetails = $job->getJobBySlug($slug);

    if (!$jobDetails) {
        $error = "Job listing not found. It may have been removed or the URL is incorrect.";
        http_response_code(404);
        // Log the 404 error
        logError("Job not found", [
            'slug' => $slug,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown URI'
        ]);
    } else {
        // Log successful access
        error_log("Job details successfully retrieved for slug: {$slug}");
        
        // Set Last-Modified header based on job's update time if available
        if (isset($jobDetails['updated_at'])) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', strtotime($jobDetails['updated_at'])) . ' GMT');
        }
    }

    // Format salary range
    $salary = (!empty($jobDetails['salary_min']) && !empty($jobDetails['salary_max']))
        ? '$' . number_format($jobDetails['salary_min']) . ' - $' . number_format($jobDetails['salary_max'])
        : 'Not Specified';

    // Set default values for optional fields
    $jobDetails = array_merge([
        'company_logo' => 'assets/img/company_logos/default.png',
        'location' => 'Not Specified',
        'requirements' => 'Not Specified',
        'qualifications' => 'Not Specified',
        'vacancy' => 1,
        'job_type' => 'Full Time',
        'deadline' => date('Y-m-d', strtotime('+30 days')),
        'company_website' => '#',
        'company_description' => 'Company description not available'
    ], $jobDetails);

} catch (PDOException $e) {
    // Log detailed error for debugging
    logError("Database error in job details", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'slug' => $slug
    ]);
    
    // Set generic error message for users
    $error = "We're experiencing technical difficulties. Please try again later.";
    http_response_code(500);
} catch (Exception $e) {
    // Log unexpected errors
    logError("Unexpected error in job details", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'slug' => $slug
    ]);
    $error = "An unexpected error occurred. Please try again later.";
    http_response_code(500);
}

?>
<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
         <title><?php echo htmlspecialchars($jobDetails['title']); ?> - Job Details</title>
        <meta name="description" content="<?php echo htmlspecialchars(substr($jobDetails['description'], 0, 160)); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="manifest" href="site.webmanifest">
		<link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">

		<!-- CSS here -->
            <link rel="stylesheet" href="assets/css/bootstrap.min.css">
            <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
            <link rel="stylesheet" href="assets/css/flaticon.css">
            <link rel="stylesheet" href="assets/css/slicknav.css">
            <link rel="stylesheet" href="assets/css/price_rangs.css">
            <link rel="stylesheet" href="assets/css/animate.min.css">
            <link rel="stylesheet" href="assets/css/magnific-popup.css">
            <link rel="stylesheet" href="assets/css/fontawesome-all.min.css">
            <link rel="stylesheet" href="assets/css/themify-icons.css">
            <link rel="stylesheet" href="assets/css/slick.css">
            <link rel="stylesheet" href="assets/css/nice-select.css">
            <link rel="stylesheet" href="assets/css/style.css">
            <link rel="stylesheet" href="assets/css/custom.css">
   </head>

   <body>
    <!-- Preloader Start -->
    <div id="preloader-active">
        <div class="preloader d-flex align-items-center justify-content-center">
            <div class="preloader-inner position-relative">
                <div class="preloader-circle"></div>
                <div class="preloader-img pere-text">
                    <img src="assets/img/logo/logo.png" alt="">
                </div>
            </div>
        </div>
    </div>
    <!-- Preloader Start -->
    <header>
        <!-- Header Start -->
       <div class="header-area header-transparrent">
           <div class="headder-top header-sticky">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-3 col-md-2">
                            <!-- Logo -->
                            <div class="logo">
                                <a href="index.html"><img src="assets/img/logo/logo.png" alt=""></a>
                            </div>  
                        </div>
                        <div class="col-lg-9 col-md-9">
                            <div class="menu-wrapper">
                                <!-- Main-menu -->
                                <div class="main-menu">
                                    <nav class="d-none d-lg-block">
                                        <ul id="navigation">
                                            <li><a href="index.html">Home</a></li>
                                            <li><a href="job_listing.html">Find a Jobs </a></li>
                                            <li><a href="about.html">About</a></li>
                                            <li><a href="#">Page</a>
                                                <ul class="submenu">
                                                    <li><a href="blog.html">Blog</a></li>
                                                    <li><a href="single-blog.html">Blog Details</a></li>
                                                    <li><a href="elements.html">Elements</a></li>
                                                </ul>
                                            </li>
                                            <li><a href="contact.html">Contact</a></li>
                                        </ul>
                                    </nav>
                                </div>          
                                <!-- Header-btn -->
                                <div class="header-btn d-none f-right d-lg-block">
                                    <a href="#" class="btn head-btn1">Register</a>
                                    <a href="#" class="btn head-btn2">Login</a>
                                </div>
                            </div>
                        </div>
                        <!-- Mobile Menu -->
                        <div class="col-12">
                            <div class="mobile_menu d-block d-lg-none"></div>
                        </div>
                    </div>
                </div>
           </div>
       </div>
        <!-- Header End -->
    </header>
    <main>
        <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <div class="container">
                <div class="alert-content">
                    <?php echo htmlspecialchars($error); ?>
                    <br>
                    <a href="job_listing.php" class="btn btn-primary mt-3">Return to Job Listings</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Hero Area Start-->
        <div class="slider-area">
            <div class="single-slider section-overly slider-height2 d-flex align-items-center" data-background="assets/img/hero/about.jpg">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="hero-cap text-center">
                                <h2><?php echo htmlspecialchars($jobDetails['title']); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Hero Area End -->
        <!-- job post company Start -->
        <div class="job-post-company pt-120 pb-120">
            <div class="container">
                <div class="row justify-content-between">
                    <!-- Left Content -->
                    <div class="col-xl-7 col-lg-8">
                        <!-- job single -->
                        <div class="single-job-items mb-50">
                            <div class="job-items">
                                <div class="company-img company-img-details">
                                    <a href="#">
                                        <?php
                                        $logoPath = !empty($jobDetails['company_logo']) 
                                            ? htmlspecialchars($jobDetails['company_logo'])
                                            : 'assets/img/logo/logo.png';
                                        
                                        // If the path starts with http or https, it's an external URL
                                        if (!preg_match('/^https?:\/\//', $logoPath)) {
                                            // Use relative path without leading slash for local files
                                            $logoPath = ltrim($logoPath, '/');
                                        }
                                        ?>
                                        <img src="<?php echo $logoPath; ?>" 
                                             alt="<?php echo htmlspecialchars($jobDetails['company_name']); ?>"
                                             onerror="this.src='assets/img/logo/logo.png';"
                                             class="img-fluid company-logo">
                                    </a>
                                </div>
                                <div class="job-tittle">
                                    <a href="#">
                                        <h4><?php echo htmlspecialchars($jobDetails['title']); ?></h4>
                                    </a>
                                    <ul>
                                        <li><?php echo htmlspecialchars($jobDetails['company_name']); ?></li>
                                        <li><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($jobDetails['location']); ?></li>
                                        <li><?php echo htmlspecialchars($salary); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- job single End -->
                       
                        <div class="job-post-details">
                            <div class="post-details1 mb-50">
                                <!-- Small Section Tittle -->
                                <div class="small-section-tittle">
                                    <h4>Job Description</h4>
                                </div>
                                <div class="job-description">
                                    <?php echo $jobDetails['description']; ?>
                                </div>
                            </div>
                            <div class="post-details2 mb-50">
                                 <!-- Small Section Tittle -->
                                <div class="small-section-tittle">
                                    <h4>Required Knowledge, Skills, and Abilities</h4>
                                </div>
                                <div class="requirements">
                                    <?php echo $jobDetails['requirements']; ?>
                                </div>
                            </div>
                            <div class="post-details2 mb-50">
                                 <!-- Small Section Tittle -->
                                <div class="small-section-tittle">
                                    <h4>Education + Experience</h4>
                                </div>
                                <div class="qualifications">
                                    <?php echo $jobDetails['qualifications']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Right Content -->
                    <div class="col-xl-4 col-lg-4">
                        <div class="post-details3 mb-50">
                            <!-- Small Section Tittle -->
                           <div class="small-section-tittle">
                               <h4>Job Overview</h4>
                           </div>
                          <ul>
                              <li>Posted date : <span><?php echo date('d M Y', strtotime($jobDetails['created_at'])); ?></span></li>
                              <li>Location : <span><?php echo htmlspecialchars($jobDetails['location']); ?></span></li>
                              <li>Vacancy : <span><?php echo htmlspecialchars($jobDetails['vacancy']); ?></span></li>
                              <li>Job nature : <span><?php echo htmlspecialchars($jobDetails['job_type']); ?></span></li>
                              <li>Salary : <span><?php echo htmlspecialchars($salary); ?></span></li>
                              <li>Application deadline : <span><?php echo date('d M Y', strtotime($jobDetails['deadline'])); ?></span></li>
                          </ul>
                         <div class="apply-btn2">
                            <a href="apply.php?job_id=<?php echo $jobDetails['id']; ?>" class="btn">Apply Now</a>
                         </div>
                       </div>
                        <div class="post-details4 mb-50">
                            <!-- Small Section Tittle -->
                           <div class="small-section-tittle">
                               <h4>Company Information</h4>
                           </div>
                            <span><?php echo htmlspecialchars($jobDetails['company_name']); ?></span>
                            <p><?php echo htmlspecialchars($jobDetails['company_description']); ?></p>
                            <ul>
                                <li>Name: <span><?php echo htmlspecialchars($jobDetails['company_name']); ?></span></li>
                                <li>Web : <span><?php echo htmlspecialchars($jobDetails['company_website']); ?></span></li>
                                <li>Email: <span><?php echo htmlspecialchars($jobDetails['company_email']); ?></span></li>
                            </ul>
                       </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- job post company End -->
        <?php endif; ?>
    </main>
    
    <?php 
    $footerPath = __DIR__ . '/includes/footer.php';
    if (file_exists($footerPath)) {
        include $footerPath;
    } else {
        error_log("Footer file not found at: " . $footerPath);
    }
    ?>

    <!-- JS here -->
    <script src="./assets/js/vendor/modernizr-3.5.0.min.js"></script>
    <script src="./assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="./assets/js/popper.min.js"></script>
    <script src="./assets/js/bootstrap.min.js"></script>
    <script src="./assets/js/jquery.slicknav.min.js"></script>
    <script src="./assets/js/owl.carousel.min.js"></script>
    <script src="./assets/js/slick.min.js"></script>
    <script src="./assets/js/price_rangs.js"></script>
    <script src="./assets/js/wow.min.js"></script>
    <script src="./assets/js/animated.headline.js"></script>
    <script src="./assets/js/jquery.magnific-popup.js"></script>
    <script src="./assets/js/jquery.scrollUp.min.js"></script>
    <script src="./assets/js/jquery.nice-select.min.js"></script>
    <script src="./assets/js/jquery.sticky.js"></script>
    <script src="./assets/js/contact.js"></script>
    <script src="./assets/js/jquery.form.js"></script>
    <script src="./assets/js/jquery.validate.min.js"></script>
    <script src="./assets/js/mail-script.js"></script>
    <script src="./assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="./assets/js/plugins.js"></script>
    <script src="./assets/js/main.js"></script>
</body>
</html>
