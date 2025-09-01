<?php
// Start the session
session_start();

// Database connection
require_once 'config/db.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Site Settings
    $site_name = sanitize_input($_POST['site_name']);
    $site_email = sanitize_input($_POST['site_email']);
    $site_description = sanitize_input($_POST['site_description']);

    // Job Settings
    $jobs_per_page = (int)$_POST['jobs_per_page'];
    $job_expiry_days = (int)$_POST['job_expiry_days'];
    $auto_approve_jobs = isset($_POST['auto_approve_jobs']) ? 1 : 0;
    $notify_new_jobs = isset($_POST['notify_new_jobs']) ? 1 : 0;

    // Email Settings
    $smtp_host = sanitize_input($_POST['smtp_host']);
    $smtp_port = (int)$_POST['smtp_port'];
    $smtp_username = sanitize_input($_POST['smtp_username']);
    $smtp_password = $_POST['smtp_password']; // Consider encryption
    $smtp_encryption = isset($_POST['smtp_encryption']) ? 1 : 0;

    // Appearance Settings
    $primary_color = sanitize_input($_POST['primary_color']);
    $secondary_color = sanitize_input($_POST['secondary_color']);
    $accent_color = sanitize_input($_POST['accent_color']);

    // Admin Settings
    $admin_email = sanitize_input($_POST['admin_email']);
    $admin_password = $_POST['admin_password'];

    try {
        // Update settings in database
        $sql = "UPDATE site_settings SET 
                site_name = ?, 
                site_email = ?,
                site_description = ?,
                jobs_per_page = ?,
                job_expiry_days = ?,
                auto_approve_jobs = ?,
                notify_new_jobs = ?,
                smtp_host = ?,
                smtp_port = ?,
                smtp_username = ?,
                smtp_password = ?,
                smtp_encryption = ?,
                primary_color = ?,
                secondary_color = ?,
                accent_color = ?,
                admin_email = ?
                WHERE id = 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $site_name,
            $site_email,
            $site_description,
            $jobs_per_page,
            $job_expiry_days,
            $auto_approve_jobs,
            $notify_new_jobs,
            $smtp_host,
            $smtp_port,
            $smtp_username,
            $smtp_password,
            $smtp_encryption,
            $primary_color,
            $secondary_color,
            $accent_color,
            $admin_email
        ]);

        // Update admin password if provided
        if (!empty($admin_password)) {
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $sql = "UPDATE admin_users SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$hashed_password, $admin_email]);
        }

        // Create settings file for easy access
        $settings = [
            'site_name' => $site_name,
            'site_email' => $site_email,
            'jobs_per_page' => $jobs_per_page,
            'auto_approve_jobs' => $auto_approve_jobs,
            'notify_new_jobs' => $notify_new_jobs,
            'smtp_encryption' => $smtp_encryption,
            'primary_color' => $primary_color,
            'secondary_color' => $secondary_color,
            'accent_color' => $accent_color
        ];

        file_put_contents('config/settings.json', json_encode($settings, JSON_PRETTY_PRINT));

        // Send success response
        echo json_encode(['status' => 'success', 'message' => 'Settings updated successfully']);
    } catch(PDOException $e) {
        // Send error response
        echo json_encode(['status' => 'error', 'message' => 'Error updating settings: ' . $e->getMessage()]);
    }
} else {
    // If not POST request, redirect to settings page
    header('Location: settings.html');
    exit();
}
?>
