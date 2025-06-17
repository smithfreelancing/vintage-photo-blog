<?php
/*
* File: /vintage-photo-blog/test_auth.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file tests the authentication system.
* It verifies that user registration, login, and profile management work correctly.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "Authentication Test";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Authentication System Test</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <h2>Current Session Status</h2>
                <?php if (isLoggedIn()): ?>
                    <div class="alert alert-success">
                        <p><strong>Logged In:</strong> Yes</p>
                        <p><strong>User ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
                        <p><strong>Username:</strong> <?php echo $_SESSION['username']; ?></p>
                        <p><strong>Role:</strong> <?php echo $_SESSION['user_role']; ?></p>
                    </div>
                    
                    <?php
                    // Get user details
                    $db = new Database();
                    $db->query("SELECT * FROM users WHERE id = :id");
                    $db->bind(':id', $_SESSION['user_id']);
                    $user = $db->single();
                    ?>
                    
                    <h3>User Details</h3>
                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td><?php echo $user['id']; ?></td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td><?php echo $user['username']; ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo $user['email']; ?></td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td><?php echo $user['role']; ?></td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <p><strong>Logged In:</strong> No</p>
                        <p>You are not currently logged in.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h2>Authentication Links</h2>
                <div class="list-group">
                    <?php if (isLoggedIn()): ?>
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user mr-2"></i> View/Edit Profile
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </a>
                        <a href="register.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-plus mr-2"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h2>Database User Count</h2>
                <?php
                $db = new Database();
                $db->query("SELECT COUNT(*) as user_count FROM users");
                $result = $db->single();
                ?>
                <p>There are currently <strong><?php echo $result['user_count']; ?></strong> users registered in the database.</p>
                
                <h3>Recent Users</h3>
                <?php
                $db->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
                $users = $db->resultSet();
                ?>
                
                <?php if (!empty($users)): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['role']; ?></td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
