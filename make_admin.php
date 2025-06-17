<?php
/*
* File: /vintage-photo-blog/make_admin.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file updates an existing user to admin role.
* Delete this file after use.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Set the username of the user you want to make admin
$username = 'jsmith'; // Change this to your existing username

// Update user role
$db = new Database();
$db->query("UPDATE users SET role = 'admin' WHERE username = :username");
$db->bind(':username', $username);

if ($db->execute()) {
    echo "<p>User '{$username}' has been updated to admin role successfully!</p>";
    echo "<p><a href='login.php'>Click here to login</a></p>";
} else {
    echo "<p>Failed to update user role.</p>";
}

echo "<p style='color:red;'><strong>Important:</strong> Delete this file after use for security reasons.</p>";
?>
