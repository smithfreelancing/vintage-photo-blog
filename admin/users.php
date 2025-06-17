<?php
/*
* File: /vintage-photo-blog/admin/users.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles user management for administrators.
* It allows admins to view, edit roles, and delete users.
*/

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = "Manage Users";

// Handle user deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    
    // Don't allow deleting your own account
    if ($userId === (int)$_SESSION['user_id']) {
        redirect('users.php?error=You cannot delete your own account');
    }
    
    $db = new Database();
    $db->query("DELETE FROM users WHERE id = :id");
    $db->bind(':id', $userId);
    
    if ($db->execute()) {
        redirect('users.php?message=User deleted successfully');
    } else {
        redirect('users.php?error=Failed to delete user');
    }
}

// Handle role change
if (isset($_GET['role']) && isset($_GET['id']) && in_array($_GET['role'], ['user', 'admin'])) {
    $userId = (int)$_GET['id'];
    $role = $_GET['role'];
    
    // Don't allow changing your own role
    if ($userId === (int)$_SESSION['user_id']) {
        redirect('users.php?error=You cannot change your own role');
    }
    
    $db = new Database();
    $db->query("UPDATE users SET role = :role WHERE id = :id");
    $db->bind(':role', $role);
    $db->bind(':id', $userId);
    
    if ($db->execute()) {
        redirect('users.php?message=User role updated');
    } else {
        redirect('users.php?error=Failed to update user role');
    }
}

// Get filter parameters
$roleFilter = isset($_GET['filter']) ? clean($_GET['filter']) : 'all';
$search = isset($_GET['search']) ? clean($_GET['search']) : '';

// Pagination setup
$usersPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $usersPerPage;

// Build the query based on filters
$db = new Database();

$query = "SELECT * FROM users";
$countQuery = "SELECT COUNT(*) as total FROM users";

$conditions = [];
$bindings = [];

// Role filter
if ($roleFilter !== 'all') {
    $conditions[] = "role = :role";
    $bindings[':role'] = $roleFilter;
}

// Search filter
if (!empty($search)) {
    $conditions[] = "(username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)";
    $bindings[':search'] = '%' . $search . '%';
}

// Add conditions to queries
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
    $countQuery .= " WHERE " . implode(" AND ", $conditions);
}

// Add order and limit
$query .= " ORDER BY created_at DESC LIMIT :offset, :limit";

// Get users
$db->query($query);

// Bind parameters
foreach ($bindings as $param => $value) {
    $db->bind($param, $value);
}

$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $usersPerPage, PDO::PARAM_INT);
$users = $db->resultSet();

// Get total users for pagination
$db->query($countQuery);

// Bind parameters for count query
foreach ($bindings as $param => $value) {
    $db->bind($param, $value);
}

$totalUsers = $db->single()['total'];
$totalPages = ceil($totalUsers / $usersPerPage);

// Include header
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
        
        /* Tables */
        .table th {
            border-top: none;
            background-color: #f8f9fa;
            font-weight: 500;
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
            </ul>
        </div>
        
        <div class="admin-content">
            <div class="container-fluid py-4">
                <h1 class="h3 mb-4">Manage Users</h1>
                
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_GET['message']; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_GET['error']; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="users.php" method="get" class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="filter">Role</label>
                                <select name="filter" id="filter" class="form-control">
                                    <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                                    <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="user" <?php echo $roleFilter === 'user' ? 'selected' : ''; ?>>User</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="search">Search</label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Search users..." value="<?php echo $search; ?>">
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-dark btn-block">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Users Table -->
                <?php if (empty($users)): ?>
                    <div class="alert alert-info">
                        No users found matching your criteria.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['username']; ?></td>
                                        <td>
                                            <?php echo !empty($user['first_name']) ? $user['first_name'] . ' ' . $user['last_name'] : '<em>Not provided</em>'; ?>
                                        </td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td>
                                            <?php if ($user['role'] === 'admin'): ?>
                                                <span class="badge badge-danger">Admin</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($user['id'] !== (int)$_SESSION['user_id']): ?>
                                                    <?php if ($user['role'] === 'user'): ?>
                                                        <a href="users.php?role=admin&id=<?php echo $user['id']; ?>" class="btn btn-outline-danger" title="Make Admin" onclick="return confirm('Make this user an admin?');">
                                                            <i class="fas fa-user-shield"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="users.php?role=user&id=<?php echo $user['id']; ?>" class="btn btn-outline-secondary" title="Make Regular User" onclick="return confirm('Remove admin privileges from this user?');">
                                                            <i class="fas fa-user"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="users.php?delete=1&id=<?php echo $user['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-secondary" disabled title="Current User">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="users.php?page=<?php echo $page - 1; ?>&filter=<?php echo $roleFilter; ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                            <span aria-hidden="true">«</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="users.php?page=<?php echo $i; ?>&filter=<?php echo $roleFilter; ?>&search=<?php echo urlencode($search); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="users.php?page=<?php echo $page + 1; ?>&filter=<?php echo $roleFilter; ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                            <span aria-hidden="true">»</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

