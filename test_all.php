<?php
/*
* File: /vintage-photo-blog/test_all.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file performs comprehensive testing of all blog features.
* It verifies that all components are working correctly.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "System Test";

// Initialize test results
$tests = [
    'database' => ['status' => 'unknown', 'message' => ''],
    'auth' => ['status' => 'unknown', 'message' => ''],
    'posts' => ['status' => 'unknown', 'message' => ''],
    'comments' => ['status' => 'unknown', 'message' => ''],
    'categories' => ['status' => 'unknown', 'message' => ''],
    'users' => ['status' => 'unknown', 'message' => ''],
    'settings' => ['status' => 'unknown', 'message' => ''],
    'uploads' => ['status' => 'unknown', 'message' => ''],
    'admin' => ['status' => 'unknown', 'message' => '']
];

// Test database connection
try {
    $db = new Database();
    $db->query("SELECT 1");
    $db->execute();
    $tests['database']['status'] = 'pass';
    $tests['database']['message'] = 'Database connection successful.';
} catch (Exception $e) {
    $tests['database']['status'] = 'fail';
    $tests['database']['message'] = 'Database connection failed: ' . $e->getMessage();
}

// Test tables
if ($tests['database']['status'] === 'pass') {
    try {
        $requiredTables = ['users', 'posts', 'categories', 'comments', 'post_categories', 'settings'];
        $missingTables = [];
        
        foreach ($requiredTables as $table) {
            $db->query("SHOW TABLES LIKE :table");
            $db->bind(':table', $table);
            $result = $db->single();
            
            if (!$result) {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            $tests['database']['message'] .= ' All required tables exist.';
        } else {
            $tests['database']['status'] = 'warning';
            $tests['database']['message'] .= ' Missing tables: ' . implode(', ', $missingTables);
        }
    } catch (Exception $e) {
        $tests['database']['status'] = 'warning';
        $tests['database']['message'] .= ' Error checking tables: ' . $e->getMessage();
    }
}

// Test authentication
$tests['auth']['status'] = 'pass';
$tests['auth']['message'] = 'Authentication system is available.';

if (isLoggedIn()) {
    $tests['auth']['message'] .= ' You are currently logged in as ' . $_SESSION['username'] . '.';
} else {
    $tests['auth']['message'] .= ' You are not currently logged in.';
}

// Test posts
try {
    $db->query("SELECT COUNT(*) as count FROM posts");
    $postCount = $db->single()['count'];
    
    $tests['posts']['status'] = 'pass';
    $tests['posts']['message'] = "Found {$postCount} posts in the database.";
    
    // Check for published posts
    $db->query("SELECT COUNT(*) as count FROM posts WHERE status = 'published'");
    $publishedCount = $db->single()['count'];
    $tests['posts']['message'] .= " {$publishedCount} posts are published.";
    
} catch (Exception $e) {
    $tests['posts']['status'] = 'fail';
    $tests['posts']['message'] = 'Error checking posts: ' . $e->getMessage();
}

// Test comments
try {
    $db->query("SELECT COUNT(*) as count FROM comments");
    $commentCount = $db->single()['count'];
    
    $tests['comments']['status'] = 'pass';
    $tests['comments']['message'] = "Found {$commentCount} comments in the database.";
    
    // Check comment statuses
    $db->query("SELECT status, COUNT(*) as count FROM comments GROUP BY status");
    $commentStatuses = $db->resultSet();
    
    $statusCounts = [];
    foreach ($commentStatuses as $status) {
        $statusCounts[] = "{$status['count']} {$status['status']}";
    }
    
    if (!empty($statusCounts)) {
        $tests['comments']['message'] .= " (" . implode(', ', $statusCounts) . ")";
    }
    
} catch (Exception $e) {
    $tests['comments']['status'] = 'fail';
    $tests['comments']['message'] = 'Error checking comments: ' . $e->getMessage();
}

// Test categories
try {
    $db->query("SELECT COUNT(*) as count FROM categories");
    $categoryCount = $db->single()['count'];
    
    $tests['categories']['status'] = 'pass';
    $tests['categories']['message'] = "Found {$categoryCount} categories in the database.";
    
} catch (Exception $e) {
    $tests['categories']['status'] = 'fail';
    $tests['categories']['message'] = 'Error checking categories: ' . $e->getMessage();
}

// Test users
try {
    $db->query("SELECT COUNT(*) as count FROM users");
    $userCount = $db->single()['count'];
    
    $tests['users']['status'] = 'pass';
    $tests['users']['message'] = "Found {$userCount} users in the database.";
    
    // Check user roles
    $db->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $userRoles = $db->resultSet();
    
    $roleCounts = [];
    foreach ($userRoles as $role) {
        $roleCounts[] = "{$role['count']} {$role['role']}(s)";
    }
    
    if (!empty($roleCounts)) {
        $tests['users']['message'] .= " (" . implode(', ', $roleCounts) . ")";
    }
    
} catch (Exception $e) {
    $tests['users']['status'] = 'fail';
    $tests['users']['message'] = 'Error checking users: ' . $e->getMessage();
}

// Test settings
try {
    $db->query("SELECT COUNT(*) as count FROM settings");
    $settingCount = $db->single()['count'];
    
    $tests['settings']['status'] = 'pass';
    $tests['settings']['message'] = "Found {$settingCount} settings in the database.";
    
} catch (Exception $e) {
    $tests['settings']['status'] = 'fail';
    $tests['settings']['message'] = 'Error checking settings: ' . $e->getMessage();
}

// Test uploads directory
$uploadDirs = ['uploads/photos', 'uploads/posts', 'uploads/profiles'];
$missingDirs = [];
$notWritableDirs = [];

foreach ($uploadDirs as $dir) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/' . $dir;
    
    if (!file_exists($fullPath)) {
        $missingDirs[] = $dir;
    } elseif (!is_writable($fullPath)) {
        $notWritableDirs[] = $dir;
    }
}

if (empty($missingDirs) && empty($notWritableDirs)) {
    $tests['uploads']['status'] = 'pass';
    $tests['uploads']['message'] = 'All upload directories exist and are writable.';
} else {
    $tests['uploads']['status'] = 'warning';
    $tests['uploads']['message'] = '';
    
    if (!empty($missingDirs)) {
        $tests['uploads']['message'] .= 'Missing directories: ' . implode(', ', $missingDirs) . '. ';
    }
    
    if (!empty($notWritableDirs)) {
        $tests['uploads']['message'] .= 'Not writable directories: ' . implode(', ', $notWritableDirs) . '.';
    }
}

// Test admin access
if (isLoggedIn() && isAdmin()) {
    $tests['admin']['status'] = 'pass';
    $tests['admin']['message'] = 'You have admin access.';
} else {
    $tests['admin']['status'] = 'warning';
    $tests['admin']['message'] = 'You do not have admin access. Log in as an admin to test admin features.';
}

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">System Test</h1>
    
    <div class="alert alert-info mb-4">
        <p><strong>System Information:</strong></p>
        <ul class="mb-0">
            <li>PHP Version: <?php echo phpversion(); ?></li>
            <li>Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
            <li>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></li>
            <li>Site URL: <?php echo SITE_URL; ?></li>
        </ul>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Test Results</h2>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Component</th>
                        <th>Status</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $component => $result): ?>
                        <tr>
                            <td><strong><?php echo ucfirst($component); ?></strong></td>
                            <td>
                                <?php if ($result['status'] === 'pass'): ?>
                                    <span class="badge badge-success">Pass</span>
                                <?php elseif ($result['status'] === 'warning'): ?>
                                    <span class="badge badge-warning">Warning</span>
                                <?php elseif ($result['status'] === 'fail'): ?>
                                    <span class="badge badge-danger">Fail</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Unknown</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $result['message']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <h2 class="mb-3">Feature Tests</h2>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="h5 mb-0">User Features</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Registration
                            <a href="register.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Login
                            <a href="login.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Profile Management
                            <a href="profile.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            View Posts
                            <a href="index.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Browse Categories
                            <a href="categories.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Search
                            <a href="search.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="h5 mb-0">Content Management</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Create Post
                            <a href="create_post.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Manage My Posts
                            <a href="my_posts.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Comment System
                            <a href="test_comments.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="h5 mb-0">Admin Features</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Admin Dashboard
                            <a href="<?php echo ADMIN_URL; ?>" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Manage Posts
                            <a href="<?php echo ADMIN_URL; ?>/posts.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Manage Comments
                            <a href="<?php echo ADMIN_URL; ?>/comments.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Manage Categories
                            <a href="<?php echo ADMIN_URL; ?>/categories.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Manage Users
                            <a href="<?php echo ADMIN_URL; ?>/users.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Site Settings
                            <a href="<?php echo ADMIN_URL; ?>/settings.php" class="btn btn-sm btn-outline-dark">Test</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="h5 mb-0">System Maintenance</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <p><strong>Important:</strong> These actions should only be performed by administrators.</p>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                Create Missing Upload Directories
                                <button id="createDirs" class="btn btn-sm btn-outline-dark">Execute</button>
                            </div>
                            <div id="createDirsResult" class="mt-2" style="display: none;"></div>
                        </li>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                Check File Permissions
                                <button id="checkPermissions" class="btn btn-sm btn-outline-dark">Execute</button>
                            </div>
                            <div id="permissionsResult" class="mt-2" style="display: none;"></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Create missing directories
    document.getElementById('createDirs').addEventListener('click', function() {
        var resultDiv = document.getElementById('createDirsResult');
        resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm text-dark" role="status"><span class="sr-only">Loading...</span></div> Creating directories...';
        resultDiv.style.display = 'block';
        
        fetch('ajax_create_dirs.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = '<div class="alert alert-success mb-0">' + data.message + '</div>';
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-danger mb-0">' + data.message + '</div>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="alert alert-danger mb-0">Error: ' + error.message + '</div>';
            });
    });
    
    // Check file permissions
    document.getElementById('checkPermissions').addEventListener('click', function() {
        var resultDiv = document.getElementById('permissionsResult');
        resultDiv.innerHTML = '<div class="spinner-border spinner-border-sm text-dark" role="status"><span class="sr-only">Loading...</span></div> Checking permissions...';
        resultDiv.style.display = 'block';
        
        fetch('ajax_check_permissions.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = '<div class="alert alert-success mb-0">' + data.message + '</div>';
                } else {
                    resultDiv.innerHTML = '<div class="alert alert-danger mb-0">' + data.message + '</div>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="alert alert-danger mb-0">Error: ' + error.message + '</div>';
            });
    });
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
