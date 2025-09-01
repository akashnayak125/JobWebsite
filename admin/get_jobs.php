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

$sql = "SELECT j.*, c.company_name, c.company_logo, 
        CASE 
            WHEN j.application_deadline < CURDATE() THEN 'Expired'
            WHEN j.status = 0 THEN 'Draft'
            ELSE 'Active'
        END as status,
        (SELECT COUNT(*) FROM job_applications WHERE job_id = j.id) as applications_count
        FROM jobs j
        LEFT JOIN companies c ON j.company_id = c.id
        ORDER BY j.posting_date DESC";

$result = $conn->query($sql);
$jobs = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}

echo json_encode($jobs);

$conn->close();
?>
