<?php
class Database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "jobpost";
    private $username = "root";
    private $password = "";
    private $conn;

    // Get the database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $e) {
            error_log("Connection Error: " . $e->getMessage());
            throw new PDOException("Database connection failed");
        }

        return $this->conn;
    }
}

// Create connection instance
$database = new Database();
$conn = $database->getConnection();

// Function to handle database errors
function handleDatabaseError($e) {
    // Log the error (in production, you should log to a file instead of displaying)
    error_log("Database Error: " . $e->getMessage());
    
    // In development, you might want to see the error
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo "Database Error: " . $e->getMessage();
    } else {
        // In production, show a generic error message
        echo "An error occurred. Please try again later.";
    }
}

// Helper function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Helper function to validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function to format date
function format_date($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

// Helper function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting based on environment
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development'); // Change to 'production' when going live
}

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata'); // Change this to your timezone
?>
