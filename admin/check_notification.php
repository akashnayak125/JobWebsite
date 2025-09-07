<?php
session_start();
header('Content-Type: application/json');

$response = ['hasNotification' => false];

if (isset($_SESSION['notification'])) {
    $response = [
        'hasNotification' => true,
        'type' => $_SESSION['notification']['type'],
        'message' => $_SESSION['notification']['message']
    ];
    // Clear the notification after sending
    unset($_SESSION['notification']);
}

echo json_encode($response);
?>
