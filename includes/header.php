<?php
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>
<!-- Preloader Start -->
<div id="preloader-active">
    <div class="preloader d-flex align-items-center justify-content-center">
        <div class="preloader-inner position-relative">
            <div class="preloader-circle"></div>
            <div class="preloader-img pere-text">
                <img src="assets/img/logo/logo.png" alt="">
            </div>
        </div>
    </div>
</div>
<!-- Preloader Start -->
<header>
    <!-- Header Start -->
    <div class="header-area header-transparrent">
        <div class="headder-top header-sticky">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-2">
                        <!-- Logo -->
                        <div class="logo">
                            <a href="index.php"><img src="assets/img/logo/logo.png" alt=""></a>
                        </div>  
                    </div>
                    <div class="col-lg-9 col-md-9">
                        <div class="menu-wrapper">
                            <!-- Main-menu -->
                            <div class="main-menu">
                                <nav class="d-none d-lg-block">
                                    <ul id="navigation">
                                        <li><a href="index.php">Home</a></li>
                                        <li><a href="job_listing.php">Find Jobs</a></li>
                                        <li><a href="about.php">About</a></li>
                                        <?php if ($isLoggedIn): ?>
                                            <li><a href="#">Dashboard</a>
                                                <ul class="submenu">
                                                    <?php if ($isAdmin): ?>
                                                        <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
                                                        <li><a href="admin/job_list.php">Manage Jobs</a></li>
                                                        <li><a href="admin/company_list.php">Manage Companies</a></li>
                                                        <li><a href="admin/applications.php">View Applications</a></li>
                                                        <li><a href="admin/settings.php">Settings</a></li>
                                                    <?php else: ?>
                                                        <li><a href="user/dashboard.php">My Dashboard</a></li>
                                                        <li><a href="user/applications.php">My Applications</a></li>
                                                        <li><a href="user/saved_jobs.php">Saved Jobs</a></li>
                                                        <li><a href="user/profile.php">My Profile</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <li><a href="blog.php">Blog</a>
                                            <ul class="submenu">
                                                <li><a href="blog.php">Blog</a></li>
                                                <li><a href="blog_categories.php">Categories</a></li>
                                                <?php if ($isAdmin): ?>
                                                    <li><a href="admin/add_post.php">Add Post</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </li>
                                        <li><a href="contact.php">Contact</a></li>
                                    </ul>
                                </nav>
                            </div>          
                            <!-- Header-btn -->
                            <div class="header-btn d-none f-right d-lg-block">
                                <?php if ($isLoggedIn): ?>
                                    <div class="dropdown">
                                        <button class="btn head-btn1 dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                            <?php if ($isAdmin): ?>
                                                <li><a class="dropdown-item" href="admin/dashboard.php">Admin Dashboard</a></li>
                                            <?php else: ?>
                                                <li><a class="dropdown-item" href="user/dashboard.php">My Dashboard</a></li>
                                            <?php endif; ?>
                                            <li><a class="dropdown-item" href="user/profile.php">Profile</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <a href="register.php" class="btn head-btn1">Register</a>
                                    <a href="login.php" class="btn head-btn2">Login</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Mobile Menu -->
                    <div class="col-12">
                        <div class="mobile_menu d-block d-lg-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->
</header>

<!-- Add this script for the dropdown functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dropdowns
    var dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(function(dropdown) {
        dropdown.addEventListener('click', function(event) {
            event.preventDefault();
            var dropdownMenu = this.nextElementSibling;
            dropdownMenu.classList.toggle('show');
        });
    });

    // Close dropdowns when clicking outside
    window.addEventListener('click', function(event) {
        if (!event.target.matches('.dropdown-toggle')) {
            var dropdowns = document.getElementsByClassName('dropdown-menu');
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    });
});

// Add this to handle the mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    var mobileMenuBtn = document.querySelector('.slicknav_btn');
    var mobileMenu = document.querySelector('.mobile_menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('slicknav_collapsed');
            mobileMenu.classList.toggle('slicknav_open');
        });
    }
});
</script>

<style>
/* Additional styles for the dropdown menu */
.dropdown-menu {
    display: none;
    position: absolute;
    background-color: #fff;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1000;
    border-radius: 4px;
    padding: 0.5rem 0;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    text-decoration: none;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
}

.dropdown-item:hover, .dropdown-item:focus {
    color: #1e2125;
    background-color: #f8f9fa;
}

.dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    overflow: hidden;
    border-top: 1px solid #e9ecef;
}

/* User icon styling */
.fa-user-circle {
    margin-right: 5px;
}

/* Mobile menu adjustments */
@media (max-width: 991px) {
    .header-btn {
        margin-top: 15px;
        text-align: center;
    }
    
    .dropdown {
        display: inline-block;
    }
}
</style>
