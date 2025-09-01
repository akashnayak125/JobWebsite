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

$search = isset($_GET['q']) ? $_GET['q'] : '';
$search = $conn->real_escape_string($search);

$sql = "SELECT id, company_name, company_logo, company_website, company_email, industry, company_description 
        FROM companies 
        WHERE company_name LIKE ?
        LIMIT 5";

$stmt = $conn->prepare($sql);
$searchTerm = "%{$search}%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$companies = [];
while($row = $result->fetch_assoc()) {
    $companies[] = $row;
}

echo json_encode($companies);

$stmt->close();
$conn->close();
?>
