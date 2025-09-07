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
            c.id as company_id
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
    $skillsString = implode(', ', $skills);

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
    <title>Edit Job - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="css/company-autocomplete.css">
    <!-- Include TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/s9bk4dpq8mjjkj5td3drb38fogaptj4rkbomq97vblbl0m9z/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
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

        .content-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .required:after {
            content: " *";
            color: red;
        }

        /* TinyMCE customization */
        .tox-tinymce {
            border-radius: 4px !important;
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
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <div class="content-card">
                <h2 class="mb-4">Edit Job</h2>
                <form id="editJobForm" method="POST" action="save_job.php">
                    <input type="hidden" name="job_id" value="<?php echo $jobId; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label required">Job Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($job['title']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="company" class="form-label required">Company</label>
                            <select class="form-control" id="company" name="company_id" required>
                                <option value="<?php echo $job['company_id']; ?>"><?php echo htmlspecialchars($job['company_name']); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label required">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($job['location']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="job_type" class="form-label required">Job Type</label>
                            <select class="form-control" id="job_type" name="job_type" required>
                                <option value="Full Time" <?php echo $job['job_type'] == 'Full Time' ? 'selected' : ''; ?>>Full Time</option>
                                <option value="Part Time" <?php echo $job['job_type'] == 'Part Time' ? 'selected' : ''; ?>>Part Time</option>
                                <option value="Contract" <?php echo $job['job_type'] == 'Contract' ? 'selected' : ''; ?>>Contract</option>
                                <option value="Freelance" <?php echo $job['job_type'] == 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="salary_min" class="form-label required">Minimum Salary</label>
                            <input type="number" class="form-control" id="salary_min" name="salary_min" value="<?php echo $job['salary_min']; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="salary_max" class="form-label required">Maximum Salary</label>
                            <input type="number" class="form-control" id="salary_max" name="salary_max" value="<?php echo $job['salary_max']; ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="deadline" class="form-label required">Application Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline" value="<?php echo $job['deadline']; ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="vacancy" class="form-label required">Number of Vacancies</label>
                            <input type="number" class="form-control" id="vacancy" name="vacancy" value="<?php echo $job['vacancy']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label required">Job Description</label>
                        <textarea class="form-control" id="description" name="description"><?php echo $job['description']; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="requirements" class="form-label required">Requirements</label>
                        <textarea class="form-control" id="requirements" name="requirements"><?php echo $job['requirements']; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="education_experience" class="form-label required">Education & Experience</label>
                        <textarea class="form-control" id="education_experience" name="education_experience"><?php echo $job['education_experience']; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="skills" class="form-label">Required Skills (comma-separated)</label>
                        <input type="text" class="form-control" id="skills" name="skills" value="<?php echo htmlspecialchars($skillsString); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label required">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="draft" <?php echo $job['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $job['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="expired" <?php echo $job['status'] == 'expired' ? 'selected' : ''; ?>>Expired</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="job_list.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/company-autocomplete.js"></script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#description, #requirements, #education_experience',
            height: 300,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | \
                     alignleft aligncenter alignright alignjustify | \
                     bullist numlist outdent indent | removeformat | help',
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });

        // Initialize Select2 for company selection with search
        $('#company').select2({
            theme: 'bootstrap',
            ajax: {
                url: 'search_companies_ajax.php',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            placeholder: 'Select a company'
        });

        // Form validation
        document.getElementById('editJobForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Update TinyMCE instances
            tinymce.triggerSave();

            // Get form data
            const formData = new FormData(this);

            // Submit form
            fetch('save_job.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Job updated successfully!');
                    window.location.href = 'job_list.php';
                } else {
                    alert('Error updating job: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the job');
            });
        });
    </script>
</body>
</html>
