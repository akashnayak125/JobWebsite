<?php
require_once 'check_admin.php';
require_once '../config/db.php';

// Get statistics
try {
    // Total jobs count
    $jobsStmt = $conn->query("SELECT COUNT(*) FROM jobs");
    $totalJobs = $jobsStmt->fetchColumn();

    // Active jobs count
    $activeJobsStmt = $conn->query("SELECT COUNT(*) FROM jobs WHERE status = 'published'");
    $activeJobs = $activeJobsStmt->fetchColumn();

    // Total companies count
    $companiesStmt = $conn->query("SELECT COUNT(*) FROM companies");
    $totalCompanies = $companiesStmt->fetchColumn();

    // Total applications count
    $applicationsStmt = $conn->query("SELECT COUNT(*) FROM job_applications");
    $totalApplications = $applicationsStmt->fetchColumn();

    // Recent job applications
    $recentApplicationsStmt = $conn->query("
        SELECT 
            ja.*, 
            j.title as job_title, 
            c.company_name,
            u.first_name,
            u.last_name,
            u.email
        FROM job_applications ja
        JOIN jobs j ON ja.job_id = j.id
        JOIN companies c ON j.company_id = c.id
        JOIN users u ON ja.user_id = u.id
        ORDER BY ja.applied_at DESC
        LIMIT 5
    ");
    $recentApplications = $recentApplicationsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Jobs by category
    $jobsByCategoryStmt = $conn->query("
        SELECT 
            jc.name as category_name, 
            COUNT(j.id) as count,
            COUNT(CASE WHEN j.status = 'published' THEN 1 END) as active_count
        FROM job_categories jc
        LEFT JOIN jobs j ON jc.id = j.category_id
        GROUP BY jc.id, jc.name
        ORDER BY count DESC
    ");
    $jobsByCategory = $jobsByCategoryStmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly job trends (last 6 months)
    $jobTrendsStmt = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as job_count,
            COUNT(DISTINCT company_id) as company_count
        FROM jobs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY month
        ORDER BY month ASC
    ");
    $jobTrends = $jobTrendsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Top performing companies
    $topCompaniesStmt = $conn->query("
        SELECT 
            c.company_name,
            COUNT(j.id) as job_count,
            COUNT(DISTINCT ja.id) as application_count
        FROM companies c
        LEFT JOIN jobs j ON c.id = j.company_id
        LEFT JOIN job_applications ja ON j.id = ja.job_id
        GROUP BY c.id, c.company_name
        ORDER BY job_count DESC
        LIMIT 5
    ");
    $topCompanies = $topCompaniesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Application status distribution
    $applicationStatusStmt = $conn->query("
        SELECT 
            status,
            COUNT(*) as count
        FROM job_applications
        GROUP BY status
    ");
    $applicationStatus = $applicationStatusStmt->fetchAll(PDO::FETCH_ASSOC);

    // Most popular job types
    $jobTypesStmt = $conn->query("
        SELECT 
            job_type,
            COUNT(*) as count,
            COUNT(DISTINCT company_id) as company_count
        FROM jobs
        GROUP BY job_type
        ORDER BY count DESC
    ");
    $jobTypes = $jobTypesStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while fetching dashboard data';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css">
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

        /* Dashboard Cards */
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: 100%;
        }

        .stats-card .icon {
            width: 48px;
            height: 48px;
            background: var(--accent-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .stats-card .title {
            color: #6c757d;
            font-size: 0.875rem;
            margin: 10px 0;
        }

        .stats-card .value {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        /* Chart Cards */
        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 24px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .chart-card h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--gray-200);
        }

        /* Table Styles */
        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
            color: var(--secondary-color);
            font-size: 0.95rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: rgba(241, 196, 15, 0.2);
            color: #d68910;
        }

        .status-reviewed {
            background-color: rgba(52, 152, 219, 0.2);
            color: #2874a6;
        }

        .status-shortlisted {
            background-color: rgba(46, 204, 113, 0.2);
            color: #196f3d;
        }

        .status-rejected {
            background-color: rgba(231, 76, 60, 0.2);
            color: #a93226;
        }

        .status-hired {
            background-color: rgba(155, 89, 182, 0.2);
            color: #6c3483;
        }

        /* Progress Bars */
        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: var(--gray-200);
        }

        .progress-bar {
            border-radius: 4px;
        }

        /* Buttons */
        .btn-group .btn {
            padding: 0.375rem 1rem;
            font-size: 0.875rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .btn-outline-primary {
            border-color: var(--accent-color);
            color: var(--accent-color);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary.active {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        /* Small Text */
        .text-muted {
            font-size: 0.8rem;
        }

        /* Stats Cards Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 20px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        .stats-card {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .stats-card:nth-child(1) { animation-delay: 0.1s; }
        .stats-card:nth-child(2) { animation-delay: 0.2s; }
        .stats-card:nth-child(3) { animation-delay: 0.3s; }
        .stats-card:nth-child(4) { animation-delay: 0.4s; }
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
            <a class="nav-link active" href="dashboard.php">
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
            <!-- Stats Row -->
            <div class="row">
                <!-- Total Jobs -->
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="icon bg-primary">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="title">Total Jobs</div>
                        <div class="value"><?php echo $totalJobs; ?></div>
                    </div>
                </div>

                <!-- Active Jobs -->
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="title">Active Jobs</div>
                        <div class="value"><?php echo $activeJobs; ?></div>
                    </div>
                </div>

                <!-- Companies -->
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="icon bg-info">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="title">Companies</div>
                        <div class="value"><?php echo $totalCompanies; ?></div>
                    </div>
                </div>

                <!-- Applications -->
                <div class="col-md-3 mb-4">
                    <div class="stats-card">
                        <div class="icon bg-warning">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="title">Total Applications</div>
                        <div class="value"><?php echo $totalApplications; ?></div>
                    </div>
                </div>
            </div>

            <!-- Job Trends Chart -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Job Posting Trends (Last 6 Months)</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary active" data-view="jobs">Jobs</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-view="companies">Companies</button>
                            </div>
                        </div>
                        <canvas id="jobTrendsChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Jobs by Category Chart -->
                <div class="col-md-6">
                    <div class="chart-card h-100">
                        <h5 class="mb-3">Jobs by Category</h5>
                        <canvas id="jobsByCategoryChart"></canvas>
                    </div>
                </div>

                <!-- Application Status Distribution -->
                <div class="col-md-6">
                    <div class="chart-card h-100">
                        <h5 class="mb-3">Application Status Distribution</h5>
                        <canvas id="applicationStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Top Companies -->
                <div class="col-md-6">
                    <div class="chart-card">
                        <h5 class="mb-3">Top Performing Companies</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th>Jobs Posted</th>
                                        <th>Applications</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topCompanies as $company): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($company['company_name']); ?></td>
                                        <td><?php echo number_format($company['job_count']); ?></td>
                                        <td><?php echo number_format($company['application_count']); ?></td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <?php 
                                                $performance = $company['job_count'] > 0 ? 
                                                    ($company['application_count'] / $company['job_count']) * 100 : 0;
                                                ?>
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: <?php echo min(100, $performance); ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Applications -->
                <div class="col-md-6">
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Recent Applications</h5>
                            <a href="applications.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Job</th>
                                        <th>Company</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentApplications as $app): ?>
                                    <tr>
                                        <td>
                                            <div><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($app['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($app['job_title']); ?></td>
                                        <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                                <?php echo ucfirst($app['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Types Analysis -->
            <div class="row">
                <div class="col-12">
                    <div class="chart-card">
                        <h5 class="mb-3">Job Types Distribution</h5>
                        <canvas id="jobTypesChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart color palette
        const colors = {
            primary: '#3498db',
            success: '#2ecc71',
            warning: '#f1c40f',
            danger: '#e74c3c',
            info: '#3498db',
            purple: '#9b59b6',
            orange: '#e67e22',
            teal: '#1abc9c',
            pink: '#e84393',
            indigo: '#6c5ce7'
        };

        // Jobs Trends Chart
        const jobTrendsData = <?php echo json_encode($jobTrends); ?>;
        const jobTrendsChart = new Chart(document.getElementById('jobTrendsChart'), {
            type: 'line',
            data: {
                labels: jobTrendsData.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: '2-digit' });
                }),
                datasets: [
                    {
                        label: 'Jobs Posted',
                        data: jobTrendsData.map(item => item.job_count),
                        borderColor: colors.primary,
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Active Companies',
                        data: jobTrendsData.map(item => item.company_count),
                        borderColor: colors.success,
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Jobs by Category Chart
        const jobsByCategoryData = <?php echo json_encode($jobsByCategory); ?>;
        const categoryChart = new Chart(document.getElementById('jobsByCategoryChart'), {
            type: 'bar',
            data: {
                labels: jobsByCategoryData.map(item => item.category_name),
                datasets: [
                    {
                        label: 'Total Jobs',
                        data: jobsByCategoryData.map(item => item.count),
                        backgroundColor: colors.primary,
                        order: 2
                    },
                    {
                        label: 'Active Jobs',
                        data: jobsByCategoryData.map(item => item.active_count),
                        backgroundColor: colors.success,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        // Application Status Chart
        const applicationStatusData = <?php echo json_encode($applicationStatus); ?>;
        const statusColors = {
            'pending': colors.warning,
            'reviewed': colors.info,
            'shortlisted': colors.success,
            'rejected': colors.danger,
            'hired': colors.purple
        };
        
        new Chart(document.getElementById('applicationStatusChart'), {
            type: 'doughnut',
            data: {
                labels: applicationStatusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
                datasets: [{
                    data: applicationStatusData.map(item => item.count),
                    backgroundColor: applicationStatusData.map(item => statusColors[item.status.toLowerCase()])
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                },
                cutout: '60%'
            }
        });

        // Job Types Chart
        const jobTypesData = <?php echo json_encode($jobTypes); ?>;
        new Chart(document.getElementById('jobTypesChart'), {
            type: 'bar',
            data: {
                labels: jobTypesData.map(item => item.job_type),
                datasets: [
                    {
                        label: 'Jobs',
                        data: jobTypesData.map(item => item.count),
                        backgroundColor: colors.primary
                    },
                    {
                        label: 'Companies',
                        data: jobTypesData.map(item => item.company_count),
                        backgroundColor: colors.success
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Handle job trends view toggle
        document.querySelectorAll('[data-view]').forEach(button => {
            button.addEventListener('click', function() {
                const view = this.dataset.view;
                const buttons = document.querySelectorAll('[data-view]');
                buttons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                jobTrendsChart.data.datasets.forEach(dataset => {
                    if (view === 'jobs' && dataset.label.includes('Jobs')) {
                        dataset.hidden = false;
                    } else if (view === 'companies' && dataset.label.includes('Companies')) {
                        dataset.hidden = false;
                    } else {
                        dataset.hidden = true;
                    }
                });
                jobTrendsChart.update();
            });
        });
    </script>
</body>
</html>
