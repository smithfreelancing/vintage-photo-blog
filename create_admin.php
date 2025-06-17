<?php
/*
* File: /vintage-photo-blog/create_admin.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file creates an admin user for testing purposes.
* Delete this file after creating the admin account.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Set admin details
$username = 'admin';
$email = 'admin@iamblogging.com';
$password = 'admin123';
$firstName = 'Admin';
$lastName = 'User';

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if admin already exists
$db = new Database();
$db->query("SELECT id FROM users WHERE username = :username OR email = :email");
$db->bind(':username', $username);
$db->bind(':email', $email);
$existingUser = $db->single();

if ($existingUser) {
    echo "<p>Admin user already exists.</p>";
} else {
    // Create admin user
    $db->query("INSERT INTO users (username, email, password, first_name, last_name, role) 
                VALUES (:username, :email, :password, :first_name, :last_name, 'admin')");
    $db->bind(':username', $username);
    $db->bind(':email', $email);
    $db->bind(':password', $hashedPassword);
    $db->bind(':first_name', $firstName);
    $db->bind(':last_name', $lastName);
    
    if ($db->execute()) {
        echo "<p>Admin user created successfully!</p>";
        echo "<p>Username: {$username}</p>";
        echo "<p>Password: {$password}</p>";
        echo "<p><a href='login.php'>Click here to login</a></p>";
    } else {
        echo "<p>Failed to create admin user.</p>";
    }
}

echo "<p style='color:red;'><strong>Important:</strong> Delete this file after use for security reasons.</p>";
?>
