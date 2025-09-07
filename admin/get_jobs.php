<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    // Get total count before filtering
    $countSql = "SELECT COUNT(*) as total FROM jobs";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Base SQL for getting jobs
    $sql = "SELECT 
        j.id,
        j.title as job_title,
        j.location,
        j.job_nature,
        j.salary_min,
        j.salary_max,
        DATE_FORMAT(j.posting_date, '%Y-%m-%d') as posting_date,
        DATE_FORMAT(j.deadline, '%Y-%m-%d') as application_deadline,
        CASE 
            WHEN j.deadline < CURDATE() THEN 'Expired'
            WHEN j.status = 'draft' THEN 'Draft'
            ELSE 'Active'
        END as status,
        j.vacancy,
        c.company_name,
        c.company_logo,
        (SELECT COUNT(*) FROM job_applications ja WHERE ja.job_id = j.id) as applications_count
        FROM jobs j
        LEFT JOIN companies c ON j.company_id = c.id";

    // Handle search
    $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
    $whereClause = '';
    
    if (!empty($search)) {
        $whereClause = " WHERE (j.title LIKE :search 
            OR j.location LIKE :search 
            OR c.company_name LIKE :search
            OR j.job_nature LIKE :search)";
    }
    
    $sql .= $whereClause;

    // Get filtered count
    $countFilteredSql = "SELECT COUNT(*) as total FROM jobs j LEFT JOIN companies c ON j.company_id = c.id" . $whereClause;
    $countFilteredStmt = $conn->prepare($countFilteredSql);
    if (!empty($search)) {
        $searchParam = '%' . $search . '%';
        $countFilteredStmt->bindParam(':search', $searchParam);
    }
    $countFilteredStmt->execute();
    $filteredRecords = $countFilteredStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Handle ordering
    $orderColumn = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 3; // Default to posting_date
    $orderDir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc';
    
    $columns = ['job_title', 'company_name', 'location', 'posting_date', 'application_deadline', 'status', 'applications_count'];
    $orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'posting_date';
    
    if ($orderBy === 'job_title') $orderBy = 'j.title';
    else if ($orderBy === 'posting_date') $orderBy = 'j.posting_date';
    else if ($orderBy === 'application_deadline') $orderBy = 'j.deadline';
    
    $sql .= " ORDER BY " . $orderBy . " " . ($orderDir === 'asc' ? 'ASC' : 'DESC');

    // Handle pagination
    if (isset($_GET['start']) && isset($_GET['length'])) {
        $sql .= " LIMIT :start, :length";
    }

    // Prepare and execute the main query
    $stmt = $conn->prepare($sql);

    // Bind search parameter if needed
    if (!empty($search)) {
        $searchParam = '%' . $search . '%';
        $stmt->bindParam(':search', $searchParam);
    }

    // Bind pagination parameters if needed
    if (isset($_GET['start']) && isset($_GET['length'])) {
        $stmt->bindParam(':start', $_GET['start'], PDO::PARAM_INT);
        $stmt->bindParam(':length', $_GET['length'], PDO::PARAM_INT);
    }

    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data for DataTables
    $data = [];
    foreach ($jobs as $job) {
        // Format salary range
        $salary = '';
        if ($job['salary_min'] && $job['salary_max']) {
            $salary = number_format($job['salary_min']) . ' - ' . number_format($job['salary_max']);
        } elseif ($job['salary_min']) {
            $salary = 'From ' . number_format($job['salary_min']);
        } elseif ($job['salary_max']) {
            $salary = 'Up to ' . number_format($job['salary_max']);
        }

        $data[] = [
            'id' => $job['id'],
            'job_title' => $job['job_title'],
            'company_name' => $job['company_name'],
            'company_logo' => $job['company_logo'],
            'location' => $job['location'],
            'job_nature' => $job['job_nature'],
            'posting_date' => $job['posting_date'],
            'application_deadline' => $job['application_deadline'],
            'status' => $job['status'],
            'applications_count' => $job['applications_count']
        ];
    }

    echo json_encode([
        'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'An error occurred while fetching jobs. Please try again later.',
        'debug' => $e->getMessage()
    ]);
}
?>
