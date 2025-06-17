<?php
/*
* File: /vintage-photo-blog/optimize.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file performs various optimization tasks.
* It optimizes the database, cleans up unused files, and more.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    echo "You must be logged in as an admin to run optimizations.";
    exit;
}

$results = [];

// Function to add a result
function addResult($task, $status, $message) {
    global $results;
    $results[] = [
        'task' => $task,
        'status' => $status,
        'message' => $message
    ];
}

// Optimize database tables
try {
    $db = new Database();
    $db->query("SHOW TABLES");
    $tables = $db->resultSet();
    
    $optimizedTables = 0;
    
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        $db->query("OPTIMIZE TABLE `$tableName`");
        $db->execute();
        $optimizedTables++;
    }
    
    addResult('Database Optimization', 'success', "Optimized $optimizedTables database tables.");
} catch (Exception $e) {
    addResult('Database Optimization', 'error', "Error: " . $e->getMessage());
}

// Clean up unused uploads
try {
    $db = new Database();
    
    // Get all used images from posts
    $db->query("SELECT featured_image FROM posts WHERE featured_image IS NOT NULL AND featured_image != ''");
    $postImages = $db->resultSet();
    $usedImages = [];
    
    foreach ($postImages as $image) {
        $usedImages[] = $image['featured_image'];
    }
    
    // Get all used profile images
    $db->query("SELECT profile_image FROM users WHERE profile_image IS NOT NULL AND profile_image != ''");
    $profileImages = $db->resultSet();
    
    foreach ($profileImages as $image) {
        $usedImages[] = $image['profile_image'];
    }
    
    // Check uploads directory
    $uploadDirs = ['posts', 'profiles'];
    $unusedFiles = [];
    $deletedFiles = 0;
    $errorFiles = [];
    
    foreach ($uploadDirs as $dir) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/uploads/' . $dir;
        
        if (file_exists($fullPath) && is_dir($fullPath)) {
            $files = scandir($fullPath);
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                
                $relativePath = $dir . '/' . $file;
                
                if (!in_array($relativePath, $usedImages)) {
                    $unusedFiles[] = $relativePath;
                    
                    // Delete the file
                    if (unlink($fullPath . '/' . $file)) {
                        $deletedFiles++;
                    } else {
                        $errorFiles[] = $relativePath;
                    }
                }
            }
        }
    }
    
    if ($deletedFiles > 0) {
        addResult('Clean Unused Uploads', 'success', "Deleted $deletedFiles unused files.");
    } else {
        addResult('Clean Unused Uploads', 'success', "No unused files found.");
    }
    
    if (!empty($errorFiles)) {
        addResult('Clean Unused Uploads', 'warning', "Could not delete " . count($errorFiles) . " files due to permissions.");
    }
} catch (Exception $e) {
    addResult('Clean Unused Uploads', 'error', "Error: " . $e->getMessage());
}

// Clean old draft posts
try {
    $db = new Database();
    $db->query("DELETE FROM posts WHERE status = 'draft' AND updated_at < DATE_SUB(NOW(), INTERVAL 6 MONTH)");
    $deletedDrafts = $db->rowCount();
    
    addResult('Clean Old Drafts', 'success', "Deleted $deletedDrafts old draft posts.");
} catch (Exception $e) {
    addResult('Clean Old Drafts', 'error', "Error: " . $e->getMessage());
}

// Clean spam comments
try {
    $db = new Database();
    $db->query("DELETE FROM comments WHERE status = 'spam' AND created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $deletedComments = $db->rowCount();
    
    addResult('Clean Spam Comments', 'success', "Deleted $deletedComments old spam comments.");
} catch (Exception $e) {
    addResult('Clean Spam Comments', 'error', "Error: " . $e->getMessage());
}

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Optimization Results - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Optimization Results</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h4 mb-3">Tasks Completed</h2>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Status</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo $result['task']; ?></td>
                                <td>
                                    <?php if ($result['status'] === 'success'): ?>
                                        <span class="badge badge-success">Success</span>
                                    <?php elseif ($result['status'] === 'warning'): ?>
                                        <span class="badge badge-warning">Warning</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Error</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $result['message']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-center">
            <a href="admin/index.php" class="btn btn-dark">Return to Admin Dashboard</a>
        </div>
    </div>
</body>
</html>
