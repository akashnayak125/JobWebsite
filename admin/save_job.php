<?php
// Database connection parameters
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "jobwebsite";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle file upload
$target_dir = "../assets/img/company_logos/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$logo_path = "";
if(isset($_FILES["company_logo"]) && $_FILES["company_logo"]["error"] == 0) {
    $file_extension = strtolower(pathinfo($_FILES["company_logo"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is actual image
    $check = getimagesize($_FILES["company_logo"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["company_logo"]["tmp_name"], $target_file)) {
            $logo_path = "assets/img/company_logos/" . $new_filename;
        }
    }
}

// Get form data and sanitize HTML content
function sanitize_html($html) {
    // Allow basic HTML tags and attributes
    return strip_tags($html, '<p><br><ul><ol><li><strong><em><h1><h2><h3><h4><h5><h6><blockquote><a>');
}

$job_title = trim($_POST['job_title']);
$company_name = trim($_POST['company_name']);
$location = trim($_POST['location']);
$salary_range = trim($_POST['salary_range']);
$job_description = sanitize_html($_POST['job_description']);
$required_skills = sanitize_html($_POST['required_skills']);
$education_experience = sanitize_html($_POST['education_experience']);
$posting_date = trim($_POST['posting_date']);
$application_deadline = trim($_POST['application_deadline']);
$vacancy_count = (int)$_POST['vacancy_count'];
$job_nature = trim($_POST['job_nature']);
$company_website = trim($_POST['company_website']);
$company_email = trim($_POST['company_email']);
$company_description = sanitize_html($_POST['company_description']);
$job_link = $_POST['job_link'];

// Prepare and execute SQL statement
$sql = "INSERT INTO jobs (
    job_title, 
    company_name, 
    company_logo,
    location, 
    salary_range, 
    job_description, 
    required_skills, 
    education_experience, 
    posting_date, 
    application_deadline, 
    vacancy_count, 
    job_nature, 
    company_website, 
    company_email, 
    company_description,
    job_link,
    created_at
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssisssss",
    $job_title,
    $company_name,
    $logo_path,         // Added missing logo_path
    $location,
    $salary_range,
    $job_description,
    $required_skills,
    $education_experience,
    $posting_date,
    $application_deadline,
    $vacancy_count,
    $job_nature,
    $company_website,
    $company_email,
    $company_description,
    $job_link           // Added missing job_link
);

// Add better error handling and response
$response = array();

if ($stmt->execute()) {
    $response['status'] = 'success';
    $response['message'] = 'Job posted successfully!';
    $response['redirect'] = 'add_job.html';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error: ' . $stmt->error;
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?>
