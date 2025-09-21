<?php
require_once 'config/db.php';
require_once 'classes/Job.php';

// Initialize filters
$filters = [
    'search' => isset($_GET['search']) ? $_GET['search'] : '',
    'location' => isset($_GET['location']) ? $_GET['location'] : '',
    'job_type' => isset($_GET['job_type']) ? $_GET['job_type'] : '',
    'experience' => isset($_GET['experience']) ? $_GET['experience'] : '',
    'salary_range' => isset($_GET['salary_range']) ? $_GET['salary_range'] : '',
    'industry' => isset($_GET['industry']) ? $_GET['industry'] : '',
    'date_posted' => isset($_GET['date_posted']) ? $_GET['date_posted'] : ''
];

// Get unique locations and job types for filters
$locationQuery = "SELECT DISTINCT location FROM jobs WHERE location IS NOT NULL ORDER BY location";
$jobTypeQuery = "SELECT DISTINCT job_type FROM jobs WHERE job_type IS NOT NULL ORDER BY job_type";
$industryQuery = "SELECT DISTINCT industry FROM companies WHERE industry IS NOT NULL ORDER BY industry";

$locations = $conn->query($locationQuery)->fetchAll(PDO::FETCH_COLUMN);
$jobTypes = $conn->query($jobTypeQuery)->fetchAll(PDO::FETCH_COLUMN);
$industries = $conn->query($industryQuery)->fetchAll(PDO::FETCH_COLUMN);

// Build the base query
$query = "SELECT 
    j.*, 
    c.company_name, 
    c.company_logo,
    c.industry";

if (!empty($filters['search'])) {
    $query .= ",
    CASE 
        WHEN j.title = :exact_title THEN 1
        WHEN j.title LIKE :partial_title THEN 2
        WHEN j.description LIKE :description THEN 3
        ELSE 4
    END as match_priority";
} else {
    $query .= ", 1 as match_priority";
}

$query .= " FROM jobs j
    LEFT JOIN companies c ON j.company_id = c.id
    WHERE 1=1";

$params = [];

// Add search conditions if search term exists
if (!empty($filters['search'])) {
    $searchTerm = '%' . $filters['search'] . '%';
    $query .= " AND (
        j.title LIKE :partial_title2
        OR j.description LIKE :description2
        OR j.location LIKE :location
        OR c.company_name LIKE :company
        OR j.requirements LIKE :requirements
    )";
    
    $params[':exact_title'] = $filters['search'];
    $params[':partial_title'] = $searchTerm;
    $params[':partial_title2'] = $searchTerm;
    $params[':description'] = $searchTerm;
    $params[':description2'] = $searchTerm;
    $params[':location'] = $searchTerm;
    $params[':company'] = $searchTerm;
    $params[':requirements'] = $searchTerm;
}

// Add other filters
if (!empty($filters['location'])) {
    $query .= " AND j.location = :filter_location";
    $params[':filter_location'] = $filters['location'];
}

if (!empty($filters['job_type'])) {
    $query .= " AND j.job_type = :job_type";
    $params[':job_type'] = $filters['job_type'];
}

if (!empty($filters['industry'])) {
    $query .= " AND c.industry = :industry";
    $params[':industry'] = $filters['industry'];
}

if (!empty($filters['experience'])) {
    $query .= " AND j.experience_level = :experience";
    $params[':experience'] = $filters['experience'];
}

if (!empty($filters['salary_range'])) {
    list($min, $max) = explode('-', $filters['salary_range']);
    $query .= " AND j.salary_min >= :salary_min AND j.salary_max <= :salary_max";
    $params[':salary_min'] = $min;
    $params[':salary_max'] = $max;
}

if (!empty($filters['date_posted'])) {
    $days = intval($filters['date_posted']);
    $query .= " AND j.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
    $params[':days'] = $days;
}

// Add sorting - match priority and newest jobs first
$query .= " ORDER BY match_priority, j.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total jobs
$totalJobs = count($jobs);
?>
<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Job Listing</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">

    <!-- CSS here -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/css/price_rangs.css">
    <link rel="stylesheet" href="assets/css/flaticon.css">
    <link rel="stylesheet" href="assets/css/slicknav.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/themify-icons.css">
    <link rel="stylesheet" href="assets/css/slick.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
            <link rel="stylesheet" href="assets/css/style.css">
            <link rel="stylesheet" href="assets/css/custom.css">
   </head><body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Area Start-->
    <div class="slider-area ">
        <div class="single-slider section-overly slider-height2 d-flex align-items-center" data-background="assets/img/hero/about.jpg">
            <div class="container">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="hero-cap text-center">
                            <h2>Get Your Job</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hero Area End -->
    
    <!-- Job List Area Start -->
    <div class="job-listing-area pt-120 pb-120">
        <div class="container">
            <div class="row">
                <!-- Left content -->
                <div class="col-xl-3 col-lg-3 col-md-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="small-section-tittle2 mb-45">
                                <div class="ion">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="12" fill="currentColor" class="bi bi-funnel-fill" viewBox="0 0 16 16">
                                        <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z"/>
                                    </svg>
                                </div>
                                <h4>Filter Jobs</h4>
                            </div>
                        </div>
                    </div>
                    
                    <form action="" method="GET">
                        <!-- Search Box -->
                        <div class="job-category-listing mb-50">
                            <div class="small-section-tittle2">
                                <h4>Search</h4>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="search" class="form-control" placeholder="Job title, company..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                            </div>

                            <!-- Location Filter -->
                            <div class="small-section-tittle2">
                                <h4>Location</h4>
                            </div>
                            <select name="location" class="form-select mb-3">
                                <option value="">All Locations</option>
                                <?php foreach($locations as $location): ?>
                                    <option value="<?php echo htmlspecialchars($location); ?>" <?php echo $filters['location'] === $location ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($location); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Job Type Filter -->
                            <div class="small-section-tittle2">
                                <h4>Job Type</h4>
                            </div>
                            <?php foreach($jobTypes as $type): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="job_type" value="<?php echo htmlspecialchars($type); ?>" 
                                        <?php echo $filters['job_type'] === $type ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        <?php echo htmlspecialchars($type); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>

                            <!-- Industry Filter -->
                            <div class="small-section-tittle2 mt-3">
                                <h4>Industry</h4>
                            </div>
                            <select name="industry" class="form-select mb-3">
                                <option value="">All Industries</option>
                                <?php foreach($industries as $industry): ?>
                                    <option value="<?php echo htmlspecialchars($industry); ?>" <?php echo $filters['industry'] === $industry ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($industry); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Date Posted Filter -->
                            <div class="small-section-tittle2">
                                <h4>Date Posted</h4>
                            </div>
                            <select name="date_posted" class="form-select mb-3">
                                <option value="">Any Time</option>
                                <option value="1" <?php echo $filters['date_posted'] === '1' ? 'selected' : ''; ?>>Last 24 hours</option>
                                <option value="7" <?php echo $filters['date_posted'] === '7' ? 'selected' : ''; ?>>Last 7 days</option>
                                <option value="30" <?php echo $filters['date_posted'] === '30' ? 'selected' : ''; ?>>Last 30 days</option>
                            </select>

                            <!-- Salary Range Filter -->
                            <div class="small-section-tittle2">
                                <h4>Salary Range</h4>
                            </div>
                            <select name="salary_range" class="form-select mb-3">
                                <option value="">Any Salary</option>
                                <option value="0-30000" <?php echo $filters['salary_range'] === '0-30000' ? 'selected' : ''; ?>>$0 - $30,000</option>
                                <option value="30000-50000" <?php echo $filters['salary_range'] === '30000-50000' ? 'selected' : ''; ?>>$30,000 - $50,000</option>
                                <option value="50000-75000" <?php echo $filters['salary_range'] === '50000-75000' ? 'selected' : ''; ?>>$50,000 - $75,000</option>
                                <option value="75000-100000" <?php echo $filters['salary_range'] === '75000-100000' ? 'selected' : ''; ?>>$75,000 - $100,000</option>
                                <option value="100000-999999999" <?php echo $filters['salary_range'] === '100000-999999999' ? 'selected' : ''; ?>>$100,000+</option>
                            </select>

                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        </div>
                    </form>
                </div>

                <!-- Right content -->
                <div class="col-xl-9 col-lg-9 col-md-8">
                    <!-- Featured_job_start -->
                    <section class="featured-job-area">
                        <div class="container">
                            <!-- Count of jobs -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="count-job mb-35">
                                        <span><?php echo $totalJobs; ?> Jobs found</span>
                                        <!-- <div class="select-job-items">
                                            <span>Sort by</span>
                                            <select name="select">
                                                <option value="">None</option>
                                                <option value="">job list</option>
                                                <option value="">job list</option>
                                                <option value="">job list</option>
                                            </select>
                                        </div> -->
                                    </div>
                                </div>
                            </div>

                            <!-- Job List -->
                            <?php foreach($jobs as $job): ?>
                                <?php
                                    // Format salary range
                                    $salary = '';
                                    if (!empty($job['salary_min']) && !empty($job['salary_max'])) {
                                        $salary = '$' . number_format($job['salary_min']) . ' - $' . number_format($job['salary_max']);
                                    } elseif (!empty($job['salary_min'])) {
                                        $salary = 'From $' . number_format($job['salary_min']);
                                    } elseif (!empty($job['salary_max'])) {
                                        $salary = 'Up to $' . number_format($job['salary_max']);
                                    } else {
                                        $salary = 'Salary not specified';
                                    }
                                ?>
                                <div class="single-job-items mb-30">
                                    <div class="job-items">
                                        <div class="company-img">
                                            <a href="job_details.php?slug=<?php echo htmlspecialchars($job['slug']); ?>">
                                                <img src="<?php echo htmlspecialchars($job['company_logo']); ?>" alt="<?php echo htmlspecialchars($job['company_name']); ?>" style="max-width: 85px;">
                                            </a>
                                        </div>
                                        <div class="job-tittle job-tittle2">
                                            <a href="job_details.php?slug=<?php echo htmlspecialchars($job['slug']); ?>">
                                                <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                                            </a>
                                            <ul>
                                                <li><?php echo htmlspecialchars($job['company_name']); ?></li>
                                                <li><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($job['location']); ?></li>
                                                <li><?php echo htmlspecialchars($salary); ?></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="items-link items-link2 f-right">
                                        <a href="job_details.php?slug=<?php echo htmlspecialchars($job['slug']); ?>"><?php echo htmlspecialchars($job['job_type']); ?></a>
                                        <span class="text-muted">
                                            <?php 
                                                $posted_date = new DateTime($job['created_at']);
                                                $now = new DateTime();
                                                $interval = $posted_date->diff($now);
                                                if ($interval->d == 0) {
                                                    echo "Posted today";
                                                } else if ($interval->d == 1) {
                                                    echo "Posted 1 day ago";
                                                } else {
                                                    echo "Posted " . $interval->d . " days ago";
                                                }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
    <!-- Job List Area End -->

    <?php include 'includes/footer.php'; ?>

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
    <script src="./assets/js/jquery.scrollUp.min.js"></script>
    <script src="./assets/js/jquery.nice-select.min.js"></script>
    <script src="./assets/js/jquery.sticky.js"></script>
    <script src="./assets/js/jquery.magnific-popup.js"></script>
    <script src="./assets/js/contact.js"></script>
    <script src="./assets/js/jquery.form.js"></script>
    <script src="./assets/js/jquery.validate.min.js"></script>
    <script src="./assets/js/mail-script.js"></script>
    <script src="./assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="./assets/js/plugins.js"></script>
    <script src="./assets/js/main.js"></script>
</body>
</html>
