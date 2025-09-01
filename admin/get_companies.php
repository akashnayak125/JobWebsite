<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    $sql = "SELECT c.*, 
            (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.deadline >= CURDATE()) as active_jobs_count,
            (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id) as total_jobs_count
            FROM companies c
            ORDER BY c.company_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process image paths and handle nulls
    foreach ($companies as &$company) {
        // Set default values for null fields
        $company['company_logo'] = $company['company_logo'] ?? 'assets/img/company_logos/default.png';
        $company['company_website'] = $company['company_website'] ?? '';
        $company['company_phone'] = $company['company_phone'] ?? 'N/A';
        $company['company_address'] = $company['company_address'] ?? 'N/A';
        $company['active_jobs_count'] = (int)$company['active_jobs_count'];
        $company['total_jobs_count'] = (int)$company['total_jobs_count'];
    }

    echo json_encode($companies);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Database error occurred'
    ]);
}
?>
