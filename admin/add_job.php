<?php
require_once 'check_admin.php';
checkAdmin();
$csrf_token = checkCSRFToken();

// Get industries from settings
require_once '../config/db.php';

// Get job statistics
try {
    $statsStmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_jobs,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as active_jobs,
            (SELECT COUNT(*) FROM job_applications) as total_applications,
            (SELECT COUNT(*) FROM companies) as total_companies
        FROM jobs
    ");
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching stats: " . $e->getMessage());
    $stats = [
        'total_jobs' => 0,
        'active_jobs' => 0,
        'total_applications' => 0,
        'total_companies' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job - Admin Panel</title>
    
    <!-- Security headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https:; script-src 'self' 'unsafe-inline' https://cdn.tiny.cloud https://code.jquery.com; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:;">
    
    <!-- CSS -->
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

        .main-content {
            margin-left: var(--sidebar-width);
            padding: calc(var(--topbar-height) + 30px) 30px 30px;
        }

        .form-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        #notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: none;
            animation: fadeInOut 3s;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }

        .company-suggestion {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .company-logo-small {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }

        #company_search_results {
            position: absolute;
            width: 100%;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .stats-card .number {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-color);
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
            <a class="nav-link active" href="add_job.php">
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
        <!-- Notification -->
        <div id="notification" class="alert" role="alert">
            <i class="fas fa-check-circle"></i> <span id="notification-message"></span>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h6 class="text-muted">Total Jobs</h6>
                    <div class="number"><?php echo $stats['total_jobs']; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h6 class="text-muted">Active Jobs</h6>
                    <div class="number"><?php echo $stats['active_jobs']; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h6 class="text-muted">Applications</h6>
                    <div class="number"><?php echo $stats['total_applications']; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h6 class="text-muted">Companies</h6>
                    <div class="number"><?php echo $stats['total_companies']; ?></div>
                </div>
            </div>
        </div>

        <!-- Add Job Form -->
        <div class="card">
            <div class="card-body">
                <h2 class="card-title mb-4">Add New Job</h2>
                
                <form id="jobForm" action="save_job.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <!-- Basic Job Information -->
                    <div class="form-section">
                        <h3>Basic Job Information</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="job_title" class="form-label">Job Title</label>
                                <input type="text" class="form-control" id="job_title" name="job_title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company_search" class="form-label">Search Company</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="company_search" placeholder="Search for company...">
                                    <input type="hidden" id="company_id" name="company_id" required>
                                    <div class="input-group-append">
                                        <a href="add_company.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-plus"></i> Add New Company
                                        </a>
                                    </div>
                                </div>
                                <div id="company_search_results" class="dropdown-menu w-100" style="display: none;">
                                </div>
                            </div>
                        </div>

                        <!-- Company details card -->
                        <div id="company_details" class="mt-3" style="display: none;">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <img id="selected_company_logo" src="" alt="Company Logo" class="img-fluid rounded">
                                        </div>
                                        <div class="col-md-9">
                                            <h4 id="selected_company_name" class="mb-2"></h4>
                                            <p id="selected_company_industry" class="text-muted mb-2"></p>
                                            <p class="mb-2"><i class="fas fa-globe"></i> <span id="selected_company_website"></span></p>
                                            <p class="mb-2"><i class="fas fa-envelope"></i> <span id="selected_company_email"></span></p>
                                            <div id="selected_company_description" class="mt-3"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="salary_range" class="form-label">Salary Range</label>
                                <input type="text" class="form-control" id="salary_range" name="salary_range" 
                                       placeholder="e.g., 50000-80000" pattern="^\d+\s*-\s*\d+$" required>
                                <small class="form-text text-muted">Format: min-max (e.g., 50000-80000)</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="job_link" class="form-label">Job Application Link (Optional)</label>
                                <input type="url" class="form-control" id="job_link" name="job_link" 
                                       placeholder="https://example.com/apply">
                            </div>
                        </div>
                    </div>

                    <!-- Job Description -->
                    <div class="form-section">
                        <h3>Job Description</h3>
                        <div class="mb-3">
                            <label for="job_description" class="form-label">Detailed Job Description</label>
                            <textarea class="editor" id="job_description" name="job_description" rows="5" required></textarea>
                        </div>
                    </div>

                    <!-- Required Skills -->
                    <div class="form-section">
                        <h3>Required Skills</h3>
                        <div class="mb-3">
                            <label for="required_skills" class="form-label">Required Knowledge, Skills, and Abilities</label>
                            <textarea class="editor" id="required_skills" name="required_skills" rows="5" required></textarea>
                            <small class="form-text text-muted">Use bullet points for better formatting</small>
                        </div>
                    </div>

                    <!-- Education and Experience -->
                    <div class="form-section">
                        <h3>Education and Experience</h3>
                        <div class="mb-3">
                            <label for="education_experience" class="form-label">Education and Experience Requirements</label>
                            <textarea class="editor" id="education_experience" name="education_experience" rows="5" required></textarea>
                            <small class="form-text text-muted">Use bullet points for better formatting</small>
                        </div>
                    </div>

                    <!-- Job Overview -->
                    <div class="form-section">
                        <h3>Job Overview</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="posting_date" class="form-label">Posting Date</label>
                                <input type="date" class="form-control" id="posting_date" name="posting_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="application_deadline" class="form-label">Application Deadline</label>
                                <input type="date" class="form-control" id="application_deadline" name="application_deadline" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vacancy_count" class="form-label">Number of Vacancies</label>
                                <input type="number" class="form-control" id="vacancy_count" name="vacancy_count" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="job_nature" class="form-label">Job Nature</label>
                                <select class="form-control" id="job_nature" name="job_nature" required>
                                    <option value="">Select Job Type</option>
                                    <option value="Full Time">Full Time</option>
                                    <option value="Part Time">Part Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Freelance">Freelance</option>
                                    <option value="Internship">Internship</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Job
                        </button>
                        <button type="reset" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-undo"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/s9bk4dpq8mjjkj5td3drb38fogaptj4rkbomq97vblbl0m9z/tinymce/5/tinymce.min.js"></script>
    
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: 'textarea.editor',
            height: 300,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
            setup: function(editor) {
                editor.on('change', function() {
                    // When editor content changes, update the original textarea
                    editor.save();
                });
            },
            init_instance_callback: function(editor) {
                // When editor is initialized, remove required attribute from textarea
                $('#' + editor.id).removeAttr('required');
                
                // Add validation to the editor instance
                editor.on('blur', function() {
                    if (editor.getContent().trim() === '') {
                        editor.getContainer().style.border = '1px solid red';
                    } else {
                        editor.getContainer().style.border = '';
                    }
                });
            }
        });

        // Company Search Functionality
        let searchTimeout;
        const companySearch = document.getElementById('company_search');
        const searchResults = document.getElementById('company_search_results');

        companySearch.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value;
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`search_companies_ajax.php?term=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(companies => {
                        searchResults.innerHTML = '';
                        if (companies.error) {
                            searchResults.innerHTML = `<div class="p-3 text-danger">${companies.error}</div>`;
                            searchResults.style.display = 'block';
                            return;
                        }
                        
                        if (companies.length === 0) {
                            searchResults.innerHTML = '<div class="p-3">No companies found</div>';
                            searchResults.style.display = 'block';
                            return;
                        }

                        companies.forEach(item => {
                            const company = item.company;
                            const element = document.createElement('a');
                            element.className = 'dropdown-item';
                            element.href = '#';
                            element.innerHTML = `
                                <div class="company-suggestion">
                                    <img src="../${company.company_logo || 'assets/img/company_logos/default.png'}" 
                                         alt="${company.company_name}" 
                                         class="company-logo-small"
                                         onerror="this.src='../assets/img/company_logos/default.png'">
                                    <div>
                                        <strong>${company.company_name}</strong><br>
                                        <small class="text-muted">${company.industry || 'Industry not specified'}</small>
                                    </div>
                                </div>
                            `;
                            element.addEventListener('click', (e) => {
                                e.preventDefault();
                                selectCompany(company);
                            });
                            searchResults.appendChild(element);
                        });
                        searchResults.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        searchResults.innerHTML = '<div class="p-3 text-danger">Error searching companies</div>';
                        searchResults.style.display = 'block';
                    });
            }, 300);
        });

        function selectCompany(company) {
            document.getElementById('company_id').value = company.id;
            companySearch.value = company.company_name;
            
            document.getElementById('selected_company_logo').src = '../' + (company.company_logo || 'assets/img/company_logos/default.png');
            document.getElementById('selected_company_name').textContent = company.company_name;
            document.getElementById('selected_company_industry').textContent = company.industry || 'Industry not specified';
            document.getElementById('selected_company_website').textContent = company.company_website || 'Not specified';
            document.getElementById('selected_company_email').textContent = company.company_email || 'Not specified';
            document.getElementById('selected_company_description').innerHTML = company.company_description || '';
            
            document.getElementById('company_details').style.display = 'block';
            searchResults.style.display = 'none';
        }

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!companySearch.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Form submission handling
        $(document).ready(function() {
            // Set today's date as default for posting date
            const today = new Date().toISOString().split('T')[0];
            $('#posting_date').val(today);
            $('#posting_date').attr('min', today);
            $('#application_deadline').attr('min', today);

            $('#jobForm').on('submit', function(e) {
                e.preventDefault();
                
                // Save TinyMCE content
                tinymce.triggerSave();

                // Validate form
                const requiredFields = {
                    'job_title': 'Job Title',
                    'company_id': 'Company',
                    'location': 'Location',
                    'salary_range': 'Salary Range',
                    'job_description': 'Job Description',
                    'required_skills': 'Required Skills',
                    'education_experience': 'Education and Experience',
                    'posting_date': 'Posting Date',
                    'application_deadline': 'Application Deadline',
                    'vacancy_count': 'Number of Vacancies',
                    'job_nature': 'Job Nature'
                };

                const missing = [];
                for (const [field, label] of Object.entries(requiredFields)) {
                    let value;
                    // Check if this is a TinyMCE editor field
                    if (tinymce.get(field)) {
                        value = tinymce.get(field).getContent().trim();
                    } else {
                        value = $(`#${field}`).val();
                    }
                    
                    if (!value || value.trim() === '') {
                        missing.push(label);
                        // Highlight the empty required field
                        if (tinymce.get(field)) {
                            tinymce.get(field).getContainer().style.border = '1px solid red';
                        } else {
                            $(`#${field}`).addClass('is-invalid');
                        }
                    } else {
                        // Remove error highlighting
                        if (tinymce.get(field)) {
                            tinymce.get(field).getContainer().style.border = '';
                        } else {
                            $(`#${field}`).removeClass('is-invalid');
                        }
                    }
                }

                if (missing.length > 0) {
                    showNotification('danger', 'Please fill in required fields: ' + missing.join(', '));
                    return;
                }

                // Validate salary range format
                const salaryRange = $('#salary_range').val();
                if (!/^\d+\s*-\s*\d+$/.test(salaryRange)) {
                    showNotification('danger', 'Please enter salary range in correct format (e.g., 50000-80000)');
                    return;
                }

                // Validate dates
                const postingDate = new Date($('#posting_date').val());
                const deadline = new Date($('#application_deadline').val());
                
                if (deadline < postingDate) {
                    showNotification('danger', 'Application deadline cannot be earlier than posting date');
                    return;
                }

                // Show loading state
                const submitBtn = $('button[type="submit"]');
                const originalBtnHtml = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                // Submit form
                const formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status === 'success') {
                            showNotification('success', response.message);
                            
                            // Reset form
                            $('#jobForm')[0].reset();
                            tinymce.get('job_description').setContent('');
                            tinymce.get('required_skills').setContent('');
                            tinymce.get('education_experience').setContent('');
                            
                            // Reset company details
                            $('#company_details').hide();
                            $('#company_id').val('');
                            
                            // Reset posting date to today
                            $('#posting_date').val(today);
                            
                            // Scroll to top
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        } else {
                            showNotification('danger', response.message);
                        }
                    },
                    error: function(xhr) {
                        showNotification('danger', 'An error occurred while saving the job');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalBtnHtml);
                    }
                });
            });
        });

        function showNotification(type, message) {
            const notification = $('#notification');
            notification.removeClass().addClass(`alert alert-${type}`);
            $('#notification-message').text(message);
            notification.show();
            
            setTimeout(() => {
                notification.hide();
            }, 5000);
        }
    </script>
</body>
</html>
