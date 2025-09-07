<?php
require_once 'check_admin.php';
require_once '../config/db.php';

// Get job ID from URL
$jobId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get job details with company information
    $stmt = $conn->prepare("
        SELECT 
            j.*,
            c.company_name,
            c.company_logo,
            c.company_website,
            c.company_email,
            c.industry,
            (SELECT COUNT(*) FROM job_applications ja WHERE ja.job_id = j.id) as applications_count
        FROM jobs j
        LEFT JOIN companies c ON j.company_id = c.id
        WHERE j.id = ?
    ");
    $stmt->execute([$jobId]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        header('Location: job_list.php');
        exit;
    }

    // Get job skills
    $skillsStmt = $conn->prepare("SELECT skill_name FROM job_skills WHERE job_id = ?");
    $skillsStmt->execute([$jobId]);
    $skills = $skillsStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while fetching job details';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details - Admin Panel</title>
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: calc(var(--topbar-height) + 30px) 30px 30px;
        }

        .job-header {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .company-logo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .job-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

        .skill-badge {
            background: #e9ecef;
            color: #495057;
            padding: 5px 15px;
            border-radius: 20px;
            margin: 0 5px 5px 0;
            display: inline-block;
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
            <a class="nav-link active" href="job_list.php">
                <i class="fas fa-list"></i> Job List
            </a>
            <a class="nav-link" href="add_company.php">
                <i class="fas fa-building"></i> Add Company
            </a>
            <a class="nav-link" href="company_list.php">
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

    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <!-- Job Header -->
            <div class="job-header">
                <div class="row">
                    <div class="col-md-2">
                        <img src="../<?php echo htmlspecialchars($job['company_logo'] ?? 'assets/img/company_logos/default.png'); ?>" 
                             alt="<?php echo htmlspecialchars($job['company_name']); ?>" 
                             class="company-logo">
                    </div>
                    <div class="col-md-7">
                        <h2><?php echo htmlspecialchars($job['title']); ?></h2>
                        <h5 class="text-muted"><?php echo htmlspecialchars($job['company_name']); ?></h5>
                        <div class="mt-3">
                            <span class="status-badge status-<?php echo strtolower($job['status']); ?>">
                                <?php echo ucfirst($job['status']); ?>
                            </span>
                            <span class="badge bg-info ms-2">
                                <?php echo $job['applications_count']; ?> Applications
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn btn-primary mb-2">
                            <i class="fas fa-edit"></i> Edit Job
                        </a>
                        <button onclick="deleteJob(<?php echo $job['id']; ?>)" class="btn btn-danger mb-2">
                            <i class="fas fa-trash"></i> Delete Job
                        </button>
                        <a href="job_applications.php?id=<?php echo $job['id']; ?>" class="btn btn-info">
                            <i class="fas fa-users"></i> View Applications
                        </a>
                    </div>
                </div>
            </div>

            <!-- Job Content -->
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-8">
                    <div class="job-content mb-4">
                        <h4>Job Description</h4>
                        <div class="mt-3">
                            <?php echo $job['description']; ?>
                        </div>
                    </div>

                    <div class="job-content mb-4">
                        <h4>Required Skills</h4>
                        <div class="mt-3">
                            <?php echo $job['requirements']; ?>
                        </div>
                        <?php if (!empty($skills)): ?>
                            <div class="mt-3">
                                <?php foreach ($skills as $skill): ?>
                                    <span class="skill-badge"><?php echo htmlspecialchars($skill); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="job-content">
                        <h4>Education and Experience</h4>
                        <div class="mt-3">
                            <?php echo $job['education_experience']; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <div class="job-content">
                        <h4>Job Details</h4>
                        <ul class="info-list mt-3">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="ms-2"><?php echo htmlspecialchars($job['location']); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-briefcase"></i>
                                <span class="ms-2"><?php echo htmlspecialchars($job['job_type']); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-money-bill-wave"></i>
                                <span class="ms-2">
                                    $<?php echo number_format($job['salary_min']); ?> - 
                                    $<?php echo number_format($job['salary_max']); ?>
                                </span>
                            </li>
                            <li>
                                <i class="fas fa-calendar-alt"></i>
                                <span class="ms-2">Posted: <?php echo date('M d, Y', strtotime($job['posting_date'])); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span class="ms-2">Deadline: <?php echo date('M d, Y', strtotime($job['deadline'])); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-users"></i>
                                <span class="ms-2"><?php echo $job['vacancy']; ?> Openings</span>
                            </li>
                        </ul>

                        <h4 class="mt-4">Company Information</h4>
                        <ul class="info-list mt-3">
                            <li>
                                <i class="fas fa-industry"></i>
                                <span class="ms-2"><?php echo htmlspecialchars($job['industry']); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-globe"></i>
                                <span class="ms-2">
                                    <a href="<?php echo htmlspecialchars($job['company_website']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($job['company_website']); ?>
                                    </a>
                                </span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span class="ms-2">
                                    <a href="mailto:<?php echo htmlspecialchars($job['company_email']); ?>">
                                        <?php echo htmlspecialchars($job['company_email']); ?>
                                    </a>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script>
        function deleteJob(id) {
            if (confirm('Are you sure you want to delete this job? This action cannot be undone.')) {
                fetch('delete_job.php?id=' + id, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Job deleted successfully');
                        window.location.href = 'job_list.php';
                    } else {
                        alert('Error deleting job: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the job');
                });
            }
        }
    </script>
</body>
</html>
