<?php
/*
* File: /vintage-photo-blog/test_db.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file tests the database connection and configuration.
* It verifies that the database is properly set up and accessible.
*/

require_once 'includes/db.php';
require_once 'includes/functions.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Create database instance
    $db = new Database();
    
    // Test query
    $db->query("SELECT * FROM users LIMIT 1");
    $result = $db->single();
    
    if ($result) {
        echo displaySuccess("Database connection successful!");
        echo "<h2>Database Configuration</h2>";
        echo "<p>Host: " . DB_HOST . "</p>";
        echo "<p>Database: " . DB_NAME . "</p>";
        
        echo "<h2>Test Query Result</h2>";
        echo "<p>Found user: " . $result['username'] . " (ID: " . $result['id'] . ")</p>";
        
        // Test categories
        $db->query("SELECT * FROM categories");
        $categories = $db->resultSet();
        
        echo "<h2>Categories</h2>";
        echo "<ul>";
        foreach ($categories as $category) {
            echo "<li>" . $category['name'] . " - " . $category['description'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo displayError("Connected to database but no users found.");
    }
} catch (Exception $e) {
    echo displayError("Database connection failed: " . $e->getMessage());
}
?>

<style>
body {
    font-family: 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}
h1, h2 {
    font-weight: 300;
}
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}
.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}
.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
</style>
