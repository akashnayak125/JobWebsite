<?php
session_start();

// Temporary function that always returns true (for development)
function checkAdmin() {
    // TODO: Implement proper authentication later
    return true;
}

// Check for CSRF token
function checkCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid security token'
        ]);
        exit();
    }
}
?>
