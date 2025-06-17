<?php
/*
* File: /vintage-photo-blog/includes/admin_header.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file contains the header for the admin area.
* It is included at the top of admin pages.
*/

// Make sure config is loaded
if (!defined('SITE_NAME')) {
    require_once 'config.php';
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin' : 'Admin Dashboard'; ?> | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin-style.css">
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
                    <a href="<?php echo ADMIN_URL; ?>/settings.php"><i class="fas fa-cog mr-2"></i> Settings</a>
                </li>
            </ul>
        </div>
        
        <div class="admin-content">
