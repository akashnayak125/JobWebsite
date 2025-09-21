<?php
require_once '../config/db.php';

header('Content-Type: application/json');

try {
    // Get search term if provided
    $search = isset($_GET['term']) ? trim($_GET['term']) : '';
    
    $query = "SELECT DISTINCT job_type FROM jobs WHERE job_type IS NOT NULL";
    if (!empty($search)) {
        $query .= " AND job_type LIKE :search";
    }
    $query .= " ORDER BY job_type ASC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Common job types to suggest if not enough results
    $commonTypes = [
        'Full Time',
        'Part Time',
        'Contract',
        'Freelance',
        'Internship',
        'Remote',
        'Hybrid',
        'On-site',
        'Temporary'
    ];
    
    // If we have less than 3 results and have a search term, add suggestions
    if (count($types) < 3 && !empty($search)) {
        foreach ($commonTypes as $type) {
            if (stripos($type, $search) !== false && !in_array($type, $types)) {
                $types[] = $type;
                if (count($types) >= 5) break; // Limit to 5 suggestions
            }
        }
    }
    
    echo json_encode(['status' => 'success', 'types' => array_values(array_unique($types))]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error fetching job types']);
}
