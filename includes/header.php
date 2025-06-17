<?php
/*
* File: /vintage-photo-blog/includes/header.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file contains the header and navigation for the site.
* It is included at the top of most pages.
*/

// Make sure config is loaded
if (!defined('SITE_NAME')) {
    require_once 'config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600&family=Helvetica+Neue:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="container">
                <a class="navbar-brand" href="<?php echo SITE_URL; ?>"><?php echo SITE_NAME; ?></a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/categories.php">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/about.php">About</a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/my_posts.php">My Posts</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- Search Form -->
                    <form class="form-inline my-2 my-lg-0 mr-3" action="<?php echo SITE_URL; ?>/search.php" method="get">
                        <div class="input-group">
                            <input class="form-control form-control-sm" type="search" name="q" placeholder="Search..." aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-outline-dark" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <ul class="navbar-nav">
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?php echo $_SESSION['username']; ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <?php if (isAdmin()): ?>
                                        <a class="dropdown-item" href="<?php echo ADMIN_URL; ?>">Admin Dashboard</a>
                                        <div class="dropdown-divider"></div>
                                    <?php endif; ?>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/create_post.php">Create Post</a>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/my_posts.php">My Posts</a>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php">Profile</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Logout</a>
                                </div>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="site-content">

