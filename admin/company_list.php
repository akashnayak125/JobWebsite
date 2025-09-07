<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company List - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
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

        /* Table Styling */
        .table-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .company-logo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Custom DataTables styling */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dce7f1;
            border-radius: 4px;
            padding: 5px 10px;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #dce7f1;
            border-radius: 4px;
            padding: 5px 10px;
        }

        .dt-buttons .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
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

    <!-- Topbar -->
    <div class="topbar">
        <div class="brand">Job Portal Admin</div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Company List</h2>
            <a href="add_company.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Company
            </a>
        </div>

        <!-- Filters -->
        <div class="filters">
            <div class="row">
                <div class="col-md-4">
                    <label for="industryFilter" class="form-label">Industry</label>
                    <select class="form-select" id="industryFilter">
                        <option value="">All Industries</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="sizeFilter" class="form-label">Company Size</label>
                    <select class="form-select" id="sizeFilter">
                        <option value="">All Sizes</option>
                        <option value="1-10">1-10 employees</option>
                        <option value="11-50">11-50 employees</option>
                        <option value="51-200">51-200 employees</option>
                        <option value="201-500">201-500 employees</option>
                        <option value="501-1000">501-1000 employees</option>
                        <option value="1000+">1000+ employees</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="activeJobsFilter" class="form-label">Active Jobs</label>
                    <select class="form-select" id="activeJobsFilter">
                        <option value="">All</option>
                        <option value="with">With Active Jobs</option>
                        <option value="without">Without Active Jobs</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Company List Table -->
        <div class="table-card">
            <table id="companyTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Industry</th>
                        <th>Size</th>
                        <th>Location</th>
                        <th>Active Jobs</th>
                        <th>Total Jobs</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="../assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#companyTable').DataTable({
                ajax: {
                    url: 'get_companies.php',
                    dataSrc: function(json) {
                        if (json.error) {
                            alert(json.message || 'An error occurred while fetching data');
                            return [];
                        }
                        return json;
                    }
                },
                columns: [
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return '<div class="d-flex align-items-center">' +
                                   '<img src="../' + row.company_logo + '" class="company-logo me-3" alt="' + row.company_name + '">' +
                                   '<div>' +
                                   '<a href="company_details.php?id=' + row.id + '" class="fw-bold d-block">' + row.company_name + '</a>' +
                                   '<small class="text-muted">' + row.company_website + '</small>' +
                                   '</div>' +
                                   '</div>';
                        }
                    },
                    { data: 'industry' },
                    { data: 'company_size' },
                    { data: 'company_address' },
                    { 
                        data: 'active_jobs_count',
                        render: function(data) {
                            return '<span class="badge bg-success">' + data + '</span>';
                        }
                    },
                    { 
                        data: 'total_jobs_count',
                        render: function(data) {
                            return '<span class="badge bg-info">' + data + '</span>';
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return '<div>' +
                                   '<small><i class="fas fa-envelope"></i> ' + row.company_email + '</small><br>' +
                                   '<small><i class="fas fa-phone"></i> ' + row.company_phone + '</small>' +
                                   '</div>';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return '<div class="action-buttons">' +
                                   '<button class="btn btn-sm btn-primary me-1" onclick="editCompany(' + row.id + ')"><i class="fas fa-edit"></i></button>' +
                                   '<button class="btn btn-sm btn-info me-1" onclick="viewJobs(' + row.id + ')"><i class="fas fa-briefcase"></i></button>' +
                                   '<button class="btn btn-sm btn-danger" onclick="deleteCompany(' + row.id + ')"><i class="fas fa-trash"></i></button>' +
                                   '</div>';
                        }
                    }
                ],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'btn btn-info'
                    }
                ],
                order: [[0, 'asc']], // Sort by company name by default
                pageLength: 10,
                responsive: true
            });

            // Load industry filter options
            $.get('get_industries.php', function(data) {
                var industries = JSON.parse(data);
                var options = '<option value="">All Industries</option>';
                industries.forEach(function(industry) {
                    options += '<option value="' + industry + '">' + industry + '</option>';
                });
                $('#industryFilter').html(options);
            });

            // Apply filters
            $('#industryFilter, #sizeFilter, #activeJobsFilter').on('change', function() {
                table.draw();
            });

            // Custom filtering function
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                let industryFilter = $('#industryFilter').val();
                let sizeFilter = $('#sizeFilter').val();
                let activeJobsFilter = $('#activeJobsFilter').val();
                
                let row = table.row(dataIndex).data();
                let pass = true;

                // Industry filtering
                if (industryFilter) {
                    pass = pass && row.industry === industryFilter;
                }

                // Size filtering
                if (sizeFilter) {
                    pass = pass && row.company_size === sizeFilter;
                }

                // Active jobs filtering
                if (activeJobsFilter) {
                    switch(activeJobsFilter) {
                        case 'with':
                            pass = pass && parseInt(row.active_jobs_count) > 0;
                            break;
                        case 'without':
                            pass = pass && parseInt(row.active_jobs_count) === 0;
                            break;
                    }
                }

                return pass;
            });
        });

        // Edit company function
        function editCompany(id) {
            window.location.href = 'edit_company.php?id=' + id;
        }

        // View company jobs function
        function viewJobs(id) {
            window.location.href = 'job_list.php?company_id=' + id;
        }

        // Delete company function
        function deleteCompany(id) {
            if (confirm('Are you sure you want to delete this company? This will also delete all associated jobs.')) {
                fetch('delete_company.php?id=' + id, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#companyTable').DataTable().ajax.reload();
                    } else {
                        alert('Error deleting company: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>
