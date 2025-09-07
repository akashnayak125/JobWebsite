<?php
require_once 'check_admin.php';
require_once '../config/db.php';

header('Content-Type: application/json');

// Check if ID was provided
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Job ID is required']);
    exit;
}

$jobId = (int)$_GET['id'];

try {
    // Begin transaction
    $conn->beginTransaction();

    // Check if job exists and get company ID
    $checkStmt = $conn->prepare("SELECT company_id FROM jobs WHERE id = ?");
    $checkStmt->execute([$jobId]);
    $job = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        throw new Exception('Job not found');
    }

    // Delete job applications first
    $deleteAppsStmt = $conn->prepare("DELETE FROM job_applications WHERE job_id = ?");
    $deleteAppsStmt->execute([$jobId]);

    // Delete job skills
    $deleteSkillsStmt = $conn->prepare("DELETE FROM job_skills WHERE job_id = ?");
    $deleteSkillsStmt->execute([$jobId]);

    // Delete the job
    $deleteJobStmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
    $deleteJobStmt->execute([$jobId]);

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Job deleted successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting job: ' . $e->getMessage()
    ]);
}
?>
