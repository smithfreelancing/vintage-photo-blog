<?php
/*
* File: /vintage-photo-blog/create_settings_table.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file creates the settings table in the database.
* It also inserts default settings.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    echo "You must be logged in as an admin to run this script.";
    exit;
}

// Create settings table
$db = new Database();

try {
    // Create settings table
    $db->query("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $db->execute();
    
    echo "<p>Settings table created successfully.</p>";
    
    // Insert default settings
    $defaultSettings = [
        ['site_name', SITE_NAME],
        ['site_description', 'Exploring the beauty and nostalgia of film photography and vintage techniques.'],
        ['posts_per_page', '6'],
        ['allow_comments', '1'],
        ['auto_approve_comments', '0'],
        ['notify_on_comment', '1'],
        ['admin_email', 'admin@example.com']
    ];
    
    foreach ($defaultSettings as $setting) {
        // Check if setting already exists
        $db->query("SELECT COUNT(*) as count FROM settings WHERE setting_key = :key");
        $db->bind(':key', $setting[0]);
        $exists = $db->single()['count'] > 0;
        
        if (!$exists) {
            $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)");
            $db->bind(':key', $setting[0]);
            $db->bind(':value', $setting[1]);
            $db->execute();
            
            echo "<p>Added setting: {$setting[0]}</p>";
        } else {
            echo "<p>Setting already exists: {$setting[0]}</p>";
        }
    }
    
    echo "<p>Default settings have been added.</p>";
    echo "<p><a href='admin/settings.php'>Go to Settings Page</a></p>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<p style='color:red;'><strong>Important:</strong> Delete this file after use for security reasons.</p>";
?>
