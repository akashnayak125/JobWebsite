<?php
require_once '../config/db.php';

// Set JSON response header
header('Content-Type: application/json');

// Validate required fields
if (empty($_POST['company_name']) || empty($_POST['company_email'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Company name and email are required'
    ]);
    exit;
}

// Validate email
if (!filter_var($_POST['company_email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email format'
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
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $_FILES["company_logo"]["tmp_name"]);
    finfo_close($file_info);

    if (!in_array($mime_type, $allowed_types)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Only JPG, PNG and GIF images are allowed'
        ]);
        exit;
    }

    $file_extension = strtolower(pathinfo($_FILES["company_logo"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is actual image and safe
    $check = getimagesize($_FILES["company_logo"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["company_logo"]["tmp_name"], $target_file)) {
            $logo_path = "assets/img/company_logos/" . $new_filename;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to upload image'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'File is not a valid image'
        ]);
        exit;
    }
}

// Get form data and sanitize HTML content
function sanitize_html($html) {
    return strip_tags($html, '<p><br><ul><ol><li><strong><em><h1><h2><h3><h4><h5><h6><blockquote><a>');
}

$company_name = trim($_POST['company_name']);
$company_website = trim($_POST['company_website']);
$company_email = trim($_POST['company_email']);
$company_phone = trim($_POST['company_phone']);
$company_address = trim($_POST['company_address']);
$company_size = trim($_POST['company_size']);
$industry = trim($_POST['industry']);
$company_description = sanitize_html($_POST['company_description']);

// Prepare and execute SQL statement
$sql = "INSERT INTO companies (
    company_name,
    company_website,
    company_email,
    company_phone,
    company_address,
    company_size,
    industry,
    company_logo,
    company_description,
    created_at
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    $company_name,
    $company_website,
    $company_email,
    $company_phone,
    $company_address,
    $company_size,
    $industry,
    $logo_path,
    $company_description
]);

if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Company added successfully!',
            'company_id' => $conn->lastInsertId(),
            'redirect' => 'add_company.html'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add company'
        ]);
    }
    
    // PDO automatically closes the statement when it goes out of scope
    
} catch (PDOException $e) {
    handleDatabaseError($e);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'An unexpected error occurred'
    ]);
}
?>
