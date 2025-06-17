<?php
/*
* File: /vintage-photo-blog/404.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file displays a custom 404 error page.
* It is shown when a requested page is not found.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "Page Not Found";

// Set 404 header
header("HTTP/1.0 404 Not Found");

// Include header
include 'includes/header.php';
?>

<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="display-1">404</h1>
            <h2 class="mb-4">Page Not Found</h2>
            <p class="lead mb-5">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
            
            <div class="mb-5">
                <a href="<?php echo SITE_URL; ?>" class="btn btn-dark">Go to Homepage</a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h3 class="h5 mb-3">You might want to try:</h3>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>">Homepage</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/categories.php">Browse Categories</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/search.php">Search</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
