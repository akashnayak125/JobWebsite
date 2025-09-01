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
$stmt->bind_param("sssssssss",
    $company_name,
    $company_website,
    $company_email,
    $company_phone,
    $company_address,
    $company_size,
    $industry,
    $logo_path,
    $company_description
);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Company added successfully!',
        'company_id' => $conn->insert_id
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
