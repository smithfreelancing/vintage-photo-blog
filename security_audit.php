<?php
/*
* File: /vintage-photo-blog/security_audit.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file performs a security audit of the application.
* It checks for common security issues and provides recommendations.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    echo "You must be logged in as an admin to run the security audit.";
    exit;
}

$issues = [];

// Function to add an issue
function addIssue($category, $severity, $description, $recommendation) {
    global $issues;
    $issues[] = [
        'category' => $category,
        'severity' => $severity,
        'description' => $description,
        'recommendation' => $recommendation
    ];
}

// Check file permissions
$criticalFiles = [
    'includes/config.php',
    'includes/db.php',
    '.htaccess'
];

foreach ($criticalFiles as $file) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/' . $file;
    
    if (file_exists($fullPath)) {
        $perms = fileperms($fullPath);
        $worldWritable = $perms & 0x0002;
        
        if ($worldWritable) {
            addIssue(
                'File Permissions',
                'high',
                "The file '$file' is world-writable (permissions: " . substr(sprintf('%o', $perms), -4) . ").",
                "Change permissions to 644 or more restrictive: <code>chmod 644 $file</code>"
            );
        }
    } else {
        addIssue(
            'Missing Files',
            'high',
            "Critical file '$file' is missing.",
            "Restore this file from a backup or reinstall the application."
        );
    }
}

// Check directory permissions
$directories = [
    'uploads',
    'uploads/photos',
    'uploads/posts',
    'uploads/profiles',
    'admin'
];

foreach ($directories as $dir) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/' . $dir;
    
    if (file_exists($fullPath)) {
        $perms = fileperms($fullPath);
        $worldWritable = $perms & 0x0002;
        
        if ($worldWritable) {
            addIssue(
                'Directory Permissions',
                'medium',
                "The directory '$dir' is world-writable (permissions: " . substr(sprintf('%o', $perms), -4) . ").",
                "Change permissions to 755 or more restrictive: <code>chmod 755 $dir</code>"
            );
        }
    }
}

// Check for .htaccess in uploads directory
$uploadsHtaccess = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/uploads/.htaccess';
if (!file_exists($uploadsHtaccess)) {
    addIssue(
        'Upload Security',
        'high',
        "No .htaccess file found in uploads directory to prevent execution of uploaded files.",
        "Create an .htaccess file in the uploads directory with content:<br><pre>
<FilesMatch \"\.(?i:php|phtml|php3|php4|php5|php7|phps|pht|phar|htaccess|htpasswd)$\">
    Order Allow,Deny
    Deny from all
</FilesMatch></pre>"
    );
}

// Check for admin directory protection
$adminHtaccess = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/admin/.htaccess';
if (!file_exists($adminHtaccess)) {
    addIssue(
        'Admin Security',
        'medium',
        "No .htaccess file found in admin directory for additional protection.",
        "Consider adding an .htaccess file in the admin directory for IP restriction or additional authentication."
    );
}

// Check for error reporting in production
if (ini_get('display_errors')) {
    addIssue(
        'Error Reporting',
        'medium',
        "PHP error reporting is enabled, which can expose sensitive information in production.",
        "Disable error reporting in production by setting <code>display_errors = Off</code> in php.ini or in config.php."
    );
}

// Check for secure passwords
try {
    $db = new Database();
    $db->query("SELECT id, username, password FROM users");
    $users = $db->resultSet();
    
    $weakPasswords = 0;
    
    foreach ($users as $user) {
        // Check if password uses bcrypt (starts with $2y$)
        if (strpos($user['password'], '$2y$') !== 0) {
            $weakPasswords++;
        }
    }
    
    if ($weakPasswords > 0) {
        addIssue(
            'Password Security',
            'high',
            "Found $weakPasswords user(s) with potentially insecure password hashing.",
            "Update the password hashing mechanism to use PHP's password_hash() function with bcrypt."
        );
    }
} catch (Exception $e) {
    addIssue(
        'Database Access',
        'medium',
        "Could not check password security: " . $e->getMessage(),
        "Ensure the database is accessible and the users table exists."
    );
}

// Check for HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    addIssue(
        'Transport Security',
        'high',
        "The site is not using HTTPS, which puts user data at risk.",
        "Install an SSL certificate and configure your server to use HTTPS."
    );
}

// Display results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Audit - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Security Audit Results</h1>
        
        <?php if (empty($issues)): ?>
            <div class="alert alert-success">
                <h4 class="alert-heading">No security issues found!</h4>
                <p>Your application passed all the basic security checks.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <h4 class="alert-heading">Security issues found</h4>
                <p>The audit found <?php echo count($issues); ?> potential security issues that should be addressed.</p>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h4 mb-3">Security Issues</h2>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Severity</th>
                                    <th>Description</th>
                                    <th>Recommendation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($issues as $issue): ?>
                                    <tr>
                                        <td><?php echo $issue['category']; ?></td>
                                        <td>
                                            <?php if ($issue['severity'] === 'high'): ?>
                                                <span class="badge badge-danger">High</span>
                                            <?php elseif ($issue['severity'] === 'medium'): ?>
                                                <span class="badge badge-warning">Medium</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">Low</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $issue['description']; ?></td>
                                        <td><?php echo $issue['recommendation']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h4 mb-3">Security Recommendations</h2>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Regular Updates:</strong> Keep PHP, MySQL, and all libraries up to date.
                    </li>
                    <li class="list-group-item">
                        <strong>Backups:</strong> Regularly backup your database and files.
                    </li>
                    <li class="list-group-item">
                        <strong>Input Validation:</strong> Always validate and sanitize user input.
                    </li>
                    <li class="list-group-item">
                        <strong>Error Handling:</strong> Use custom error handlers to avoid exposing sensitive information.
                    </li>
                    <li class="list-group-item">
                        <strong>File Uploads:</strong> Restrict file types and scan uploads for malware.
                    </li>
                    <li class="list-group-item">
                        <strong>Session Security:</strong> Use secure cookies and regenerate session IDs after login.
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="text-center">
            <a href="admin/index.php" class="btn btn-dark">Return to Admin Dashboard</a>
        </div>
    </div>
</body>
</html>
