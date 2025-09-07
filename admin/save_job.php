<?php
// Ensure no output before headers
ob_start();

require_once 'check_admin.php';
checkAdmin();

// Validate CSRF token
if (!isset($_POST['csrf_token'])) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Security token missing'
    ]);
    exit();
}

validateCSRFToken($_POST['csrf_token']);

try {
    require_once '../config/db.php';
    
    // Set error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Log the connection status
    if ($conn) {
        error_log("Database connection successful");
    } else {
        error_log("Database connection failed but didn't throw an exception");
    }
} catch (Exception $e) {
    ob_end_clean();
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

try {
    // Debug information
    error_log('Received POST data: ' . print_r($_POST, true));
    
    // Validate required fields
    if (empty($_POST['job_title']) || empty($_POST['company_id'])) {
        $missing = [];
        if (empty($_POST['job_title'])) $missing[] = 'job title';
        if (empty($_POST['company_id'])) $missing[] = 'company';
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Required fields missing: ' . implode(', ', $missing)
        ]);
        exit;
    }

    // Validation helper functions
    function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    function validateURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    function sanitize_html($html) {
        // Allow basic HTML tags and attributes
        return strip_tags($html, '<p><br><ul><ol><li><strong><em><h1><h2><h3><h4><h5><h6><blockquote><a>');
    }

    // Validate required fields
    $required_fields = [
        'job_title' => 'Job Title',
        'company_id' => 'Company',
        'job_description' => 'Job Description',
        'location' => 'Location',
        'job_nature' => 'Job Type',
        'posting_date' => 'Posting Date',
        'application_deadline' => 'Application Deadline'
    ];

    $errors = [];
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field])) {
            $errors[] = $label . ' is required';
        }
    }

    // Validate dates
    if (!empty($_POST['posting_date']) && !validateDate($_POST['posting_date'])) {
        $errors[] = 'Invalid posting date format';
    }
    if (!empty($_POST['application_deadline']) && !validateDate($_POST['application_deadline'])) {
        $errors[] = 'Invalid application deadline format';
    }

    // Validate job link
    if (!empty($_POST['job_link']) && !validateURL($_POST['job_link'])) {
        $errors[] = 'Invalid job application link';
    }

    // Validate vacancy count
    if (!empty($_POST['vacancy_count']) && (!is_numeric($_POST['vacancy_count']) || $_POST['vacancy_count'] < 1)) {
        $errors[] = 'Vacancy count must be a positive number';
    }

    // Check if company exists
    if (!empty($_POST['company_id'])) {
        $stmt = $conn->prepare("SELECT id FROM companies WHERE id = ?");
        $stmt->execute([$_POST['company_id']]);
        if (!$stmt->fetch()) {
            $errors[] = 'Selected company does not exist';
        }
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        ob_end_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Validation failed: ' . implode(', ', $errors)
        ]);
        exit;
    }

    // Sanitize and prepare data
    $job_title = htmlspecialchars(trim($_POST['job_title']), ENT_QUOTES, 'UTF-8');
    $location = htmlspecialchars(trim($_POST['location']), ENT_QUOTES, 'UTF-8');
    $salary_range = trim($_POST['salary_range']);
    $job_description = sanitize_html($_POST['job_description']);
    $required_skills = sanitize_html($_POST['required_skills']);
    $education_experience = sanitize_html($_POST['education_experience']);
    $posting_date = trim($_POST['posting_date']);
    $application_deadline = trim($_POST['application_deadline']);
    $vacancy_count = (int)$_POST['vacancy_count'];
    $job_type = trim($_POST['job_nature']); // Map job_nature to job_type
    $job_link = trim($_POST['job_link']);

    // Parse salary range into min and max
    $salary_parts = explode('-', $salary_range);
    $salary_min = !empty($salary_parts[0]) ? (float)preg_replace('/[^0-9.]/', '', trim($salary_parts[0])) : null;
    $salary_max = !empty($salary_parts[1]) ? (float)preg_replace('/[^0-9.]/', '', trim($salary_parts[1])) : null;

    // Generate slug from title
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $job_title)));
    $slug = trim($slug, '-');

    // Prepare and execute SQL statement
    $sql = "INSERT INTO jobs (
        title,
        slug,
        company_id,
        description,
        requirements,
        education_experience,
        location,
        job_type,
        salary_min,
        salary_max,
        vacancy,
        posting_date,
        deadline,
        job_link,
        status,
        created_at
    ) VALUES (
        :title,
        :slug,
        :company_id,
        :description,
        :requirements,
        :education,
        :location,
        :job_type,
        :salary_min,
        :salary_max,
        :vacancy,
        :posting_date,
        :deadline,
        :job_link,
        'published',
        NOW()
    )";

    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        ':title' => $job_title,
        ':slug' => $slug,
        ':company_id' => $_POST['company_id'],
        ':description' => $job_description,
        ':requirements' => $required_skills,
        ':education' => $education_experience,
        ':location' => $location,
        ':job_type' => $job_type,
        ':salary_min' => $salary_min,
        ':salary_max' => $salary_max,
        ':vacancy' => $vacancy_count,
        ':posting_date' => $posting_date,
        ':deadline' => $application_deadline,
        ':job_link' => $job_link
    ]);

    if ($result) {
        // Set session message and redirect
        session_start();
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Job posted successfully!'
        ];
        
        // Redirect to add_job.php
        header('Location: add_job.php');
        exit;
    } else {
        throw new Exception('Failed to save job posting');
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => 'Failed to save job posting. Please try again.'
    ];
    header('Location: add_job.php');
}
?>
