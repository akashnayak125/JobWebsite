<?php
require_once 'check_admin.php';
require_once '../config/db.php';

// Get company ID from URL
$companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get company details
    $stmt = $conn->prepare("
        SELECT c.*,
            (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id AND j.status = 'published') as active_jobs_count,
            (SELECT COUNT(*) FROM jobs j WHERE j.company_id = c.id) as total_jobs_count
        FROM companies c
        WHERE c.id = ?
    ");
    $stmt->execute([$companyId]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        header('Location: company_list.php');
        exit;
    }

    // Get recent jobs from this company
    $jobsStmt = $conn->prepare("
        SELECT j.*,
            (SELECT COUNT(*) FROM job_applications ja WHERE ja.job_id = j.id) as applications_count
        FROM jobs j
        WHERE j.company_id = ?
        ORDER BY j.posting_date DESC
        LIMIT 5
    ");
    $jobsStmt->execute([$companyId]);
    $recentJobs = $jobsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while fetching company details';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($company['company_name']); ?> - Company Details</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome-all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --topbar-height: 60px;
            --primary-color: #2d3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
        }

        body {
            min-height: 100vh;
            background-color: #f5f6fa;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--primary-color);
            padding-top: var(--topbar-height);
            color: white;
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 25px;
            font-size: 1.1em;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: var(--secondary-color);
            border-left-color: var(--accent-color);
        }

        .sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
        }

        /* Topbar Styles */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--topbar-height);
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 900;
            display: flex;
            align-items: center;
            padding: 0 30px;
        }

        .brand {
            color: var(--primary-color);
            font-size: 1.5em;
            font-weight: bold;
            margin-left: var(--sidebar-width);
        }

        /* Main Content Area */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: calc(var(--topbar-height) + 30px) 30px 30px;
        }

        .company-header {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .company-logo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }

        .content-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .info-list {
            list-style: none;
            padding: 0;
        }

        .info-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .info-list i {
            width: 25px;
            color: var(--accent-color);
        }

        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-published { background-color: #d4edda; color: #155724; }
        .status-draft { background-color: #fff3cd; color: #856404; }
        .status-expired { background-color: #f8d7da; color: #721c24; }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="brand">Job Portal Admin</div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-link" href="add_job.php">
                <i class="fas fa-plus-circle"></i> Add Job
            </a>
            <a class="nav-link" href="job_list.php">
                <i class="fas fa-list"></i> Job List
            </a>
            <a class="nav-link" href="add_company.php">
                <i class="fas fa-building"></i> Add Company
            </a>
            <a class="nav-link active" href="company_list.php">
                <i class="fas fa-th-list"></i> Company List
            </a>
            <a class="nav-link" href="applications.php">
                <i class="fas fa-users"></i> Applications
            </a>
            <a class="nav-link" href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </nav>
    </div>

    <!-- Toast Notification -->
    <div id="deleteToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <!-- Company Header -->
            <div class="company-header">
                <div class="row">
                    <div class="col-md-2">
                        <img src="../<?php echo htmlspecialchars($company['company_logo'] ?? 'assets/img/company_logos/default.png'); ?>" 
                             alt="<?php echo htmlspecialchars($company['company_name']); ?>" 
                             class="company-logo">
                    </div>
                    <div class="col-md-7">
                        <h2><?php echo htmlspecialchars($company['company_name']); ?></h2>
                        <p class="text-muted"><?php echo htmlspecialchars($company['industry']); ?></p>
                        <div class="mt-3">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($company['company_size']); ?> Employees</span>
                            <span class="badge bg-success ms-2"><?php echo $company['active_jobs_count']; ?> Active Jobs</span>
                            <span class="badge bg-info ms-2"><?php echo $company['total_jobs_count']; ?> Total Jobs</span>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="edit_company.php?id=<?php echo $company['id']; ?>" class="btn btn-primary mb-2">
                            <i class="fas fa-edit"></i> Edit Company
                        </a>
                        <a href="job_list.php?company_id=<?php echo $company['id']; ?>" class="btn btn-success mb-2">
                            <i class="fas fa-briefcase"></i> View Jobs
                        </a>
                        <button onclick="deleteCompany(<?php echo $company['id']; ?>)" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Company
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-8">
                    <!-- Company Description -->
                    <div class="content-card">
                        <h4>About the Company</h4>
                        <div class="mt-3">
                            <?php echo $company['company_description']; ?>
                        </div>
                    </div>

                    <!-- Recent Jobs -->
                    <div class="content-card">
                        <h4>Recent Job Postings</h4>
                        <div class="table-responsive mt-3">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Posted Date</th>
                                        <th>Status</th>
                                        <th>Applications</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentJobs as $job): ?>
                                        <tr>
                                            <td>
                                                <a href="job_details.php?id=<?php echo $job['id']; ?>">
                                                    <?php echo htmlspecialchars($job['title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($job['posting_date'])); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower($job['status']); ?>">
                                                    <?php echo ucfirst($job['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo $job['applications_count']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="deleteJob(<?php echo $job['id']; ?>)" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if (empty($recentJobs)): ?>
                                <p class="text-center text-muted">No jobs posted yet.</p>
                            <?php endif; ?>
                            <div class="text-end mt-3">
                                <a href="job_list.php?company_id=<?php echo $company['id']; ?>" class="btn btn-primary">
                                    View All Jobs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <!-- Company Information -->
                    <div class="content-card">
                        <h4>Company Information</h4>
                        <ul class="info-list mt-3">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="ms-2"><?php echo htmlspecialchars($company['company_address']); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-globe"></i>
                                <span class="ms-2">
                                    <a href="<?php echo htmlspecialchars($company['company_website']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($company['company_website']); ?>
                                    </a>
                                </span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span class="ms-2">
                                    <a href="mailto:<?php echo htmlspecialchars($company['company_email']); ?>">
                                        <?php echo htmlspecialchars($company['company_email']); ?>
                                    </a>
                                </span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span class="ms-2"><?php echo htmlspecialchars($company['company_phone']); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-users"></i>
                                <span class="ms-2"><?php echo htmlspecialchars($company['company_size']); ?> Employees</span>
                            </li>
                            <li>
                                <i class="fas fa-industry"></i>
                                <span class="ms-2"><?php echo htmlspecialchars($company['industry']); ?></span>
                            </li>
                        </ul>
                    </div>

                    <!-- Quick Actions -->
                    <div class="content-card">
                        <h4>Quick Actions</h4>
                        <div class="d-grid gap-2 mt-3">
                            <a href="add_job.php?company_id=<?php echo $company['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Post New Job
                            </a>
                            <a href="applications.php?company_id=<?php echo $company['id']; ?>" class="btn btn-info">
                                <i class="fas fa-users"></i> View All Applications
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script>
        function showToast(message) {
            const toastEl = document.getElementById('deleteToast');
            toastEl.querySelector('.toast-body').textContent = message;
            const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
        }

        function deleteCompany(id) {
            if (confirm('Are you sure you want to delete this company? This will also delete all associated jobs and applications.')) {
                fetch('delete_company.php?id=' + id, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'company_list.php';
                    } else {
                        showToast(data.message || 'Error deleting company');
                    }
                })
                .catch(error => {
                    showToast('An error occurred while deleting the company');
                });
            }
        }

        function deleteJob(id) {
            if (confirm('Are you sure you want to delete this job?')) {
                fetch('delete_job.php?id=' + id, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showToast(data.message || 'Error deleting job');
                    }
                })
                .catch(error => {
                    showToast('An error occurred while deleting the job');
                });
            }
        }
    </script>
</body>
</html>
