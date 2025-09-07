<?php
require_once 'check_admin.php';
require_once '../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Applications - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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

        .table-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.875rem;
        }

        .status-new {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-reviewing {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-accepted {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
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
            <a class="nav-link" href="company_list.php">
                <i class="fas fa-th-list"></i> Company List
            </a>
            <a class="nav-link active" href="applications.php">
                <i class="fas fa-users"></i> Applications
            </a>
            <a class="nav-link" href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Job Applications</h2>
        </div>

        <!-- Filters -->
        <div class="filters mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="dateFilter" class="form-label">Application Date</label>
                    <select class="form-select" id="dateFilter">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="new">New</option>
                        <option value="reviewing">Reviewing</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="jobFilter" class="form-label">Job Position</label>
                    <select class="form-select" id="jobFilter">
                        <option value="">All Positions</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="companyFilter" class="form-label">Company</label>
                    <select class="form-select" id="companyFilter">
                        <option value="">All Companies</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="table-card">
            <table id="applicationsTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Applicant Name</th>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Application Details Modal -->
    <div class="modal fade" id="applicationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <div class="status-buttons">
                        <button type="button" class="btn btn-warning" onclick="updateStatus('reviewing')">Mark as Reviewing</button>
                        <button type="button" class="btn btn-success" onclick="updateStatus('accepted')">Accept</button>
                        <button type="button" class="btn btn-danger" onclick="updateStatus('rejected')">Reject</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#applicationsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'get_applications.php',
                    type: 'GET'
                },
                columns: [
                    { 
                        data: 'applicant_name',
                        render: function(data, type, row) {
                            return '<a href="#" onclick="showApplication(' + row.id + ')">' + data + '</a>';
                        }
                    },
                    { data: 'job_title' },
                    { data: 'company_name' },
                    { 
                        data: 'applied_date',
                        render: function(data) {
                            return new Date(data).toLocaleDateString();
                        }
                    },
                    { 
                        data: 'status',
                        render: function(data) {
                            return '<span class="status-badge status-' + data.toLowerCase() + '">' + 
                                   data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return '<div class="btn-group">' +
                                   '<button class="btn btn-sm btn-primary" onclick="showApplication(' + row.id + ')">' +
                                   '<i class="fas fa-eye"></i></button>' +
                                   '<button class="btn btn-sm btn-danger" onclick="deleteApplication(' + row.id + ')">' +
                                   '<i class="fas fa-trash"></i></button>' +
                                   '</div>';
                        }
                    }
                ],
                order: [[3, 'desc']]
            });

            // Apply filters
            $('#dateFilter, #statusFilter, #jobFilter, #companyFilter').on('change', function() {
                table.draw();
            });
        });

        function showApplication(id) {
            // Load application details into modal
            $.get('get_application_details.php?id=' + id, function(data) {
                $('#applicationModal .modal-body').html(data);
                $('#applicationModal').modal('show');
            });
        }

        function updateStatus(status) {
            var applicationId = $('#applicationModal').data('applicationId');
            $.post('update_application_status.php', {
                id: applicationId,
                status: status
            }, function(response) {
                if (response.success) {
                    $('#applicationModal').modal('hide');
                    $('#applicationsTable').DataTable().ajax.reload();
                } else {
                    alert('Error updating status: ' + response.message);
                }
            });
        }

        function deleteApplication(id) {
            if (confirm('Are you sure you want to delete this application?')) {
                $.ajax({
                    url: 'delete_application.php?id=' + id,
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            $('#applicationsTable').DataTable().ajax.reload();
                        } else {
                            alert('Error deleting application: ' + response.message);
                        }
                    }
                });
            }
        }
    </script>
</body>
</html>
