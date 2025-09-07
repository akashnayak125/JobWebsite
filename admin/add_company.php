<?php
session_start();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Company - Admin Panel</title>
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

        /* Form Styling */
        .form-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            display: none;
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
            <a class="nav-link active" href="add_company.php">
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

    <!-- Topbar -->
    <div class="topbar">
        <div class="brand">Job Portal Admin</div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Toast Notifications -->
        <div class="toast-container position-fixed top-50 start-50 translate-middle">
            <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fs-5"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
            <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fs-5"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="card-title mb-4">Add New Company</h2>
                <form id="addCompanyForm" action="save_company.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <div class="form-section">
                        <h3>Company Information</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company_website" class="form-label">Company Website</label>
                                <input type="url" class="form-control" id="company_website" name="company_website">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_email" class="form-label">Company Email</label>
                                <input type="email" class="form-control" id="company_email" name="company_email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company_phone" class="form-label">Company Phone</label>
                                <input type="tel" class="form-control" id="company_phone" name="company_phone">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="company_address" class="form-label">Company Address</label>
                                <textarea class="form-control" id="company_address" name="company_address" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_size" class="form-label">Company Size</label>
                                <select class="form-control" id="company_size" name="company_size">
                                    <option value="1-10">1-10 employees</option>
                                    <option value="11-50">11-50 employees</option>
                                    <option value="51-200">51-200 employees</option>
                                    <option value="201-500">201-500 employees</option>
                                    <option value="501-1000">501-1000 employees</option>
                                    <option value="1000+">1000+ employees</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="industry" class="form-label">Industry</label>
                                <input type="text" class="form-control" id="industry" name="industry" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Company Logo</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_logo" class="form-label">Upload Logo</label>
                                <input type="file" class="form-control" id="company_logo" name="company_logo" accept="image/*" required>
                                <img id="logo_preview" class="preview-image" src="#" alt="Logo preview">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Company Description</h3>
                        <div class="mb-3">
                            <label for="company_description" class="form-label">About the Company</label>
                            <textarea class="editor" id="company_description" name="company_description" rows="5" required></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Company
                        </button>
                        <button type="reset" class="btn btn-secondary btn-lg ml-2">
                            <i class="fas fa-undo"></i> Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/s9bk4dpq8mjjkj5td3drb38fogaptj4rkbomq97vblbl0m9z/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
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
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save(); // This syncs TinyMCE content back to the original textarea
                });
            }
        });

        let successToast, errorToast;

        // Initialize toasts after DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the toasts
            successToast = new bootstrap.Toast(document.getElementById('successToast'), {
                animation: true,
                autohide: true,
                delay: 3000
            });
            
            errorToast = new bootstrap.Toast(document.getElementById('errorToast'), {
                animation: true,
                autohide: true,
                delay: 5000
            });
        });
        
        // Function to show toast notification
        function showToast(type, message) {
            const toastEl = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
            const toast = type === 'success' ? successToast : errorToast;
            
            if (toastEl && toast) {
                toastEl.querySelector('.toast-body').textContent = message;
                toast.show();
            } else {
                console.error('Toast elements not properly initialized');
                alert(message); // Fallback if toast isn't working
            }
        }

        // Handle form submission
        document.getElementById('addCompanyForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Disable submit button and show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            try {
                // Make sure TinyMCE updates the textarea before form submission
                tinymce.triggerSave();

                // Create FormData object
                const formData = new FormData(this);

                // Submit form using fetch
                const response = await fetch('save_company.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Get the response text
                const text = await response.text();
                console.log('Server response:', text);

                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        showToast('success', data.message);
                        // Reset form
                        this.reset();
                        tinymce.get('company_description').setContent('');
                        document.getElementById('logo_preview').style.display = 'none';
                        // Redirect after delay
                        setTimeout(() => {
                            window.location.href = 'company_list.php';
                        }, 3000);
                    } else {
                        showToast('error', data.message || 'Unknown error occurred');
                    }
                } catch (parseError) {
                    console.error('JSON parsing error:', parseError);
                    showToast('error', 'Invalid response from server');
                }
            } catch (error) {
                console.error('Submission error:', error);
                showToast('error', 'An error occurred while saving the company');
            } finally {
                // Re-enable submit button and restore original text
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });

        // Preview uploaded image
        document.getElementById('company_logo').onchange = function(evt) {
            const [file] = this.files;
            if (file) {
                const preview = document.getElementById('logo_preview');
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        };
    </script>
</body>
</html>
