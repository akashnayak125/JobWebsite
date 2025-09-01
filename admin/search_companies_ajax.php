<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    $search = $_GET['term'] ?? '';
    
    if (empty($search)) {
        echo json_encode([]);
        exit;
    }

    $sql = "SELECT 
            id,
            company_name,
            company_website,
            company_email,
            company_phone,
            company_address,
            industry,
            company_size,
            company_description,
            company_logo
        FROM companies 
        WHERE company_name LIKE :search 
        ORDER BY company_name ASC
        LIMIT 10";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['search' => "%$search%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for autocomplete
    $formatted = array_map(function($company) {
        return [
            'id' => $company['id'],
            'label' => $company['company_name'], // This will be shown in the autocomplete dropdown
            'value' => $company['company_name'], // This will be put in the input field
            'company' => $company // All company data
        ];
    }, $results);

    echo json_encode($formatted);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Database error occurred']);
}
?>
