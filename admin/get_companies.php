<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "jobwebsite";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.application_deadline >= CURDATE()) as active_jobs_count,
        (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id) as total_jobs_count
        FROM companies c
        ORDER BY c.company_name ASC";

$result = $conn->query($sql);
$companies = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }
}

echo json_encode($companies);

$conn->close();
?>
