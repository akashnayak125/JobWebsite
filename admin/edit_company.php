<?php
require_once 'check_admin.php';
require_once '../config/db.php';

// Get company ID from URL
$companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get company details
    $stmt = $conn->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$companyId]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$company) {
        header('Location: company_list.php');
        exit;
    }

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
    <title>Edit Company - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome-all.min.css">
    <script src="https://cdn.tiny.cloud/1/YOUR-API-KEY/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
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
            margin-bottom: 30px;
        }

        .required:after {
            content: " *";
            color: red;
        }

        .current-logo {
            max-width: 150px;
            max-height: 150px;
            object-fit: contain;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .preview-image {
            max-width: 150px;
            max-height: 150px;
            object-fit: contain;
            border-radius: 4px;
            margin-top: 10px;
            display: none;
        }

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

    <!-- Toast Notifications -->
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
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
            <div class="content-card">
                <h2 class="mb-4">Edit Company</h2>
                <form id="editCompanyForm" action="save_company.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="company_id" value="<?php echo $companyId; ?>">

                    <div class="form-section mb-4">
                        <h4 class="mb-3">Company Information</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label required">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company['company_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company_website" class="form-label">Company Website</label>
                                <input type="url" class="form-control" id="company_website" name="company_website" value="<?php echo htmlspecialchars($company['company_website']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_email" class="form-label required">Company Email</label>
                                <input type="email" class="form-control" id="company_email" name="company_email" value="<?php echo htmlspecialchars($company['company_email']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="company_phone" class="form-label">Company Phone</label>
                                <input type="tel" class="form-control" id="company_phone" name="company_phone" value="<?php echo htmlspecialchars($company['company_phone']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="company_address" class="form-label">Company Address</label>
                                <textarea class="form-control" id="company_address" name="company_address" rows="2"><?php echo htmlspecialchars($company['company_address']); ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_size" class="form-label">Company Size</label>
                                <select class="form-control" id="company_size" name="company_size">
                                    <option value="1-10" <?php echo $company['company_size'] == '1-10' ? 'selected' : ''; ?>>1-10 employees</option>
                                    <option value="11-50" <?php echo $company['company_size'] == '11-50' ? 'selected' : ''; ?>>11-50 employees</option>
                                    <option value="51-200" <?php echo $company['company_size'] == '51-200' ? 'selected' : ''; ?>>51-200 employees</option>
                                    <option value="201-500" <?php echo $company['company_size'] == '201-500' ? 'selected' : ''; ?>>201-500 employees</option>
                                    <option value="501-1000" <?php echo $company['company_size'] == '501-1000' ? 'selected' : ''; ?>>501-1000 employees</option>
                                    <option value="1000+" <?php echo $company['company_size'] == '1000+' ? 'selected' : ''; ?>>1000+ employees</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="industry" class="form-label required">Industry</label>
                                <input type="text" class="form-control" id="industry" name="industry" value="<?php echo htmlspecialchars($company['industry']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-4">
                        <h4 class="mb-3">Company Logo</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <?php if ($company['company_logo']): ?>
                                    <img src="../<?php echo htmlspecialchars($company['company_logo']); ?>" alt="Current Logo" class="current-logo">
                                <?php endif; ?>
                                <input type="file" class="form-control" id="company_logo" name="company_logo" accept="image/*">
                                <img id="logo_preview" class="preview-image" src="#" alt="Logo preview">
                                <small class="form-text text-muted">Leave empty to keep the current logo</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-section mb-4">
                        <h4 class="mb-3">Company Description</h4>
                        <div class="mb-3">
                            <textarea class="editor" id="company_description" name="company_description" rows="5" required><?php echo htmlspecialchars($company['company_description']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="company_details.php?id=<?php echo $companyId; ?>" class="btn btn-secondary btn-lg ms-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
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
            toolbar: 'undo redo | formatselect | bold italic backcolor | \
                     alignleft aligncenter alignright alignjustify | \
                     bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
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

        // Show toast notification
        function showToast(type, message) {
            const toastEl = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
            toastEl.querySelector('.toast-body').textContent = message;
            const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
            toast.show();
        }

        // Handle form submission
        document.getElementById('editCompanyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Make sure TinyMCE updates the textarea
            tinymce.triggerSave();

            // Create FormData object
            const formData = new FormData(this);
            formData.append('action', 'update'); // Add action parameter

            // Submit form
            fetch('save_company.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('success', data.message);
                    setTimeout(() => {
                        window.location.href = 'company_details.php?id=<?php echo $companyId; ?>';
                    }, 3000);
                } else {
                    showToast('error', data.message);
                }
            })
            .catch(error => {
                showToast('error', 'An error occurred while saving changes');
            });
        });
    </script>
</body>
</html>
