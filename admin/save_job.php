<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// Validate required fields
if (empty($_POST['job_title']) || empty($_POST['company_name'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Job title and company name are required'
    ]);
    exit;
}

try {

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

// Parse salary range into min and max
$salary_parts = explode('-', $salary_range);
$salary_min = !empty($salary_parts[0]) ? (float)trim($salary_parts[0]) : null;
$salary_max = !empty($salary_parts[1]) ? (float)trim($salary_parts[1]) : null;

// Generate slug from title
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $job_title)));
$slug = trim($slug, '-');

// Prepare and execute SQL statement
$sql = "INSERT INTO jobs (
    title,
    slug,
    company_id,
    category_id,
    description,
    requirements,
    education_experience,
    location,
    job_type,
    job_nature,
    salary_min,
    salary_max,
    vacancy,
    deadline,
    is_remote,
    status,
    created_at
) VALUES (
    :title,
    :slug,
    :company_id,
    :category_id,
    :description,
    :requirements,
    :education,
    :location,
    :job_type,
    :job_nature,
    :salary_min,
    :salary_max,
    :vacancy,
    :deadline,
    :is_remote,
    'published',
    NOW()
)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':title' => $job_title,
    ':slug' => $slug,
    ':company_id' => null, // This should be set based on the logged-in company or selected company
    ':category_id' => $_POST['category_id'] ?? null,
    ':description' => $job_description,
    ':requirements' => $required_skills,
    ':education' => $education_experience,
    ':location' => $location,
    ':job_type' => $_POST['job_type'] ?? 'Full Time',
    ':job_nature' => $job_nature,
    ':salary_min' => $salary_min,
    ':salary_max' => $salary_max,
    ':vacancy' => $vacancy_count,
    ':deadline' => $application_deadline,
    ':is_remote' => isset($_POST['is_remote']) ? 1 : 0
]);

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
