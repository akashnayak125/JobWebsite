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

$sql = "SELECT DISTINCT industry FROM companies ORDER BY industry";
$result = $conn->query($sql);
$industries = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $industries[] = $row['industry'];
    }
}

echo json_encode($industries);

$conn->close();
?>
