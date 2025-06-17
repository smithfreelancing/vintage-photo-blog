<?php
/*
* File: /vintage-photo-blog/admin/backup.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file creates a backup of the database.
* It allows admins to download a SQL dump of the database.
*/

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = "Database Backup";

// Handle backup request
$backupCreated = false;
$backupFile = '';
$error = '';

if (isset($_POST['create_backup'])) {
    // Create backup directory if it doesn't exist
    $backupDir = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/admin/backups';
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    // Create .htaccess file to protect backups
    $htaccessFile = $backupDir . '/.htaccess';
    if (!file_exists($htaccessFile)) {
        file_put_contents($htaccessFile, "Order Deny,Allow\nDeny from all");
    }
    
    // Generate backup filename
    $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backupPath = $backupDir . '/' . $backupFile;
    
    // Get database credentials
    $host = DB_HOST;
    $user = DB_USER;
    $pass = DB_PASS;
    $name = DB_NAME;
    
    // Create backup command
    $command = "mysqldump --opt -h $host -u $user";
    if ($pass) {
        $command .= " -p'$pass'";
    }
    $command .= " $name > $backupPath";
    
    // Execute backup command
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0) {
        $backupCreated = true;
    } else {
        $error = "Failed to create backup. Error code: $returnVar";
    }
}

// Handle download request
if (isset($_GET['download']) && !empty($_GET['file'])) {
    $file = clean($_GET['file']);
    $backupDir = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/admin/backups';
    $filePath = $backupDir . '/' . $file;
    
    if (file_exists($filePath) && is_file($filePath)) {
        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        
        // Clear output buffer
        ob_clean();
        flush();
        
        // Read file and output to browser
        readfile($filePath);
        exit;
    }
}

// Get list of existing backups
$backups = [];
$backupDir = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/admin/backups';

if (file_exists($backupDir)) {
    $files = scandir($backupDir);
    
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && $file !== '.htaccess' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backupDir . '/' . $file),
                'date' => filemtime($backupDir . '/' . $file)
            ];
        }
    }
    
    // Sort backups by date (newest first)
    usort($backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
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
                    <a href="<?php echo ADMIN_URL; ?>/settings.php"><i class="fas fa-cog mr-2"></i> Settings</a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/backup.php" class="active"><i class="fas fa-database mr-2"></i> Backup</a>
                </li>
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="container-fluid py-4">
                <h1 class="h3 mb-4">Database Backup</h1>
                
                <?php if ($backupCreated): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Backup Created!</h4>
                        <p>Your database backup has been created successfully.</p>
                        <hr>
                        <p class="mb-0">
                            <a href="backup.php?download=1&file=<?php echo $backupFile; ?>" class="alert-link">Download Backup</a>
                        </p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Error</h4>
                        <p><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Create Backup</h5>
                            </div>
                            <div class="card-body">
                                <p>Create a backup of your database. This will export all tables and data to a SQL file that you can download.</p>
                                
                                <form action="backup.php" method="post">
                                    <button type="submit" name="create_backup" class="btn btn-dark">
                                        <i class="fas fa-download mr-2"></i> Create Backup
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Backup Tips</h5>
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li>Regular backups are essential for data safety.</li>
                                    <li>Store backups in multiple locations.</li>
                                    <li>Test your backups regularly to ensure they can be restored.</li>
                                    <li>Consider automating backups for consistency.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($backups)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Previous Backups</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Filename</th>
                                            <th>Size</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($backups as $backup): ?>
                                            <tr>
                                                <td><?php echo $backup['name']; ?></td>
                                                <td><?php echo formatFileSize($backup['size']); ?></td>
                                                <td><?php echo date('Y-m-d H:i:s', $backup['date']); ?></td>
                                                <td>
                                                    <a href="backup.php?download=1&file=<?php echo $backup['name']; ?>" class="btn btn-sm btn-outline-dark" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Helper function to format file size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>
