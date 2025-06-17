<?php
/*
* File: /vintage-photo-blog/ajax_create_dirs.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file creates missing upload directories.
* It is called via AJAX from the test_all.php page.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'You must be an admin to perform this action.']);
    exit;
}

// Define directories to create
$directories = [
    'uploads',
    'uploads/photos',
    'uploads/posts',
    'uploads/profiles'
];

$created = [];
$failed = [];

// Create directories
foreach ($directories as $dir) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/' . $dir;
    
    if (!file_exists($fullPath)) {
        if (mkdir($fullPath, 0755, true)) {
            $created[] = $dir;
        } else {
            $failed[] = $dir;
        }
    }
}

// Set permissions
foreach ($directories as $dir) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/' . $dir;
    
    if (file_exists($fullPath) && !is_writable($fullPath)) {
        if (chmod($fullPath, 0755)) {
            // Directory permissions updated
        } else {
            $failed[] = $dir . ' (permissions)';
        }
    }
}

// Return result
if (empty($failed)) {
    if (empty($created)) {
        echo json_encode(['success' => true, 'message' => 'All directories already exist with correct permissions.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Created directories: ' . implode(', ', $created)]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to create or set permissions for: ' . implode(', ', $failed) . 
                    (empty($created) ? '' : '<br>Created directories: ' . implode(', ', $created))
    ]);
}
?>
