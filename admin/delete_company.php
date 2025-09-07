<?php
require_once '../config/db.php';

header('Content-Type: application/json');

try {
    $companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($companyId <= 0) {
        throw new Exception('Invalid company ID');
    }

    // Start transaction
    $conn->beginTransaction();

    // Delete company logo
    $stmt = $conn->prepare("SELECT company_logo FROM companies WHERE id = ?");
    $stmt->execute([$companyId]);
    $logo = $stmt->fetchColumn();

    if ($logo) {
        $logoPath = "../" . $logo;
        if (file_exists($logoPath)) {
            unlink($logoPath);
        }
    }

    // Delete all job applications for this company's jobs
    $stmt = $conn->prepare("
        DELETE ja FROM job_applications ja
        INNER JOIN jobs j ON ja.job_id = j.id
        WHERE j.company_id = ?
    ");
    $stmt->execute([$companyId]);

    // Delete all job skills for this company's jobs
    $stmt = $conn->prepare("
        DELETE js FROM job_skills js
        INNER JOIN jobs j ON js.job_id = j.id
        WHERE j.company_id = ?
    ");
    $stmt->execute([$companyId]);

    // Delete all jobs for this company
    $stmt = $conn->prepare("DELETE FROM jobs WHERE company_id = ?");
    $stmt->execute([$companyId]);

    // Finally, delete the company
    $stmt = $conn->prepare("DELETE FROM companies WHERE id = ?");
    $stmt->execute([$companyId]);

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Company and all associated data deleted successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting company: ' . $e->getMessage()
    ]);
}
?>
