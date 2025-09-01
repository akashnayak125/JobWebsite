<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
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
