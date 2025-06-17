<?php
/*
* File: /vintage-photo-blog/admin/settings.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles site settings for administrators.
* It allows admins to configure various aspects of the site.
*/

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = "Site Settings";

// Initialize variables
$errors = [];
$success = false;

// Define settings with default values
$settings = [
    'site_name' => SITE_NAME,
    'site_description' => 'Exploring the beauty and nostalgia of film photography and vintage techniques.',
    'posts_per_page' => 6,
    'allow_comments' => 1,
    'auto_approve_comments' => 0,
    'notify_on_comment' => 1,
    'admin_email' => 'admin@example.com'
];

// Get settings from database
$db = new Database();
$db->query("SELECT * FROM settings");
$dbSettings = $db->resultSet();

// Update settings array with values from database
foreach ($dbSettings as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $newSettings = [
        'site_name' => clean($_POST['site_name']),
        'site_description' => clean($_POST['site_description']),
        'posts_per_page' => (int)$_POST['posts_per_page'],
        'allow_comments' => isset($_POST['allow_comments']) ? 1 : 0,
        'auto_approve_comments' => isset($_POST['auto_approve_comments']) ? 1 : 0,
        'notify_on_comment' => isset($_POST['notify_on_comment']) ? 1 : 0,
        'admin_email' => clean($_POST['admin_email'])
    ];
    
    // Validate form data
    if (empty($newSettings['site_name'])) {
        $errors[] = "Site name is required";
    }
    
    if ($newSettings['posts_per_page'] < 1) {
        $errors[] = "Posts per page must be at least 1";
    }
    
    if (!filter_var($newSettings['admin_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid admin email format";
    }
    
    // If no errors, update settings
    if (empty($errors)) {
        // Begin transaction
        $db->beginTransaction();
        
        try {
            foreach ($newSettings as $key => $value) {
                // Check if setting exists
                $db->query("SELECT COUNT(*) as count FROM settings WHERE setting_key = :key");
                $db->bind(':key', $key);
                $exists = $db->single()['count'] > 0;
                
                if ($exists) {
                    // Update existing setting
                    $db->query("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
                } else {
                    // Insert new setting
                    $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)");
                }
                
                $db->bind(':key', $key);
                $db->bind(':value', $value);
                $db->execute();
            }
            
            // Commit transaction
            $db->endTransaction();
            
            $success = true;
            $settings = $newSettings;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->cancelTransaction();
            $errors[] = "An error occurred: " . $e->getMessage();
        }
    }
}

// Start of admin header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin' : 'Admin Dashboard'; ?> | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        /* Admin styles */
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        /* Admin Layout */
        .admin-container {
            display: flex;
            min-height: calc(100vh - 56px);
        }
        
        .admin-sidebar {
            width: 250px;
            background-color: #343a40;
            color: #fff;
            padding: 1rem 0;
            flex-shrink: 0;
        }
        
        .admin-content {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-x: auto;
        }
        
        /* Sidebar Styles */
        .sidebar-header {
            padding: 0 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }
        
        .admin-sidebar ul li a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .admin-sidebar ul li a:hover,
        .admin-sidebar ul li a.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Forms */
        .form-control {
            border-radius: 0;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: #80bdff;
        }
        
        /* Buttons */
        .btn {
            border-radius: 0;
        }
        
        /* Settings Card */
        .settings-card {
            margin-bottom: 2rem;
        }
        
        .settings-card .card-header {
            background-color: #f8f9fa;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo ADMIN_URL; ?>"><?php echo SITE_NAME; ?> Admin</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/posts.php">Posts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/comments.php">Comments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo ADMIN_URL; ?>/users.php">Users</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>" target="_blank">View Site</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo $_SESSION['username']; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php">Profile</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h5>Admin Menu</h5>
            </div>
            <ul class="list-unstyled">
                <li>
                    <a href="<?php echo ADMIN_URL; ?>"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/posts.php"><i class="fas fa-file-alt mr-2"></i> Posts</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/comments.php"><i class="fas fa-comments mr-2"></i> Comments</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/categories.php"><i class="fas fa-tags mr-2"></i> Categories</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/users.php"><i class="fas fa-users mr-2"></i> Users</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/settings.php" class="active"><i class="fas fa-cog mr-2"></i> Settings</a>
                </li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="container-fluid py-4">
                <h1 class="h3 mb-4">Site Settings</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Settings updated successfully.
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="settings.php" method="post">
                    <!-- General Settings -->
                    <div class="card settings-card">
                        <div class="card-header">
                            <h5 class="mb-0">General Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="site_name">Site Name</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo $settings['site_name']; ?>" required>
                                <small class="form-text text-muted">The name of your website.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_description">Site Description</label>
                                <textarea class="form-control" id="site_description" name="site_description" rows="2"><?php echo $settings['site_description']; ?></textarea>
                                <small class="form-text text-muted">A brief description of your website.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="posts_per_page">Posts Per Page</label>
                                                                <input type="number" class="form-control" id="posts_per_page" name="posts_per_page" value="<?php echo $settings['posts_per_page']; ?>" min="1" required>
                                <small class="form-text text-muted">Number of posts to display per page.</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Comment Settings -->
                    <div class="card settings-card">
                        <div class="card-header">
                            <h5 class="mb-0">Comment Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="allow_comments" name="allow_comments" <?php echo $settings['allow_comments'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="allow_comments">Allow Comments</label>
                                </div>
                                <small class="form-text text-muted">Enable or disable comments on posts.</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="auto_approve_comments" name="auto_approve_comments" <?php echo $settings['auto_approve_comments'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="auto_approve_comments">Auto-Approve Comments</label>
                                </div>
                                <small class="form-text text-muted">Automatically approve comments without moderation.</small>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="notify_on_comment" name="notify_on_comment" <?php echo $settings['notify_on_comment'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="notify_on_comment">Email Notification on New Comments</label>
                                </div>
                                <small class="form-text text-muted">Receive email notifications when new comments are posted.</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Email Settings -->
                    <div class="card settings-card">
                        <div class="card-header">
                            <h5 class="mb-0">Email Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="admin_email">Admin Email</label>
                                <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo $settings['admin_email']; ?>" required>
                                <small class="form-text text-muted">Email address for admin notifications.</small>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-dark px-4">Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

