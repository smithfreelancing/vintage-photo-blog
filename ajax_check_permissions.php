<?php
/*
* File: /vintage-photo-blog/ajax_check_permissions.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file checks file and directory permissions.
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

// Define directories to check
$directories = [
    'uploads',
    'uploads/photos',
    'uploads/posts',
    'uploads/profiles'
];

$notWritable = [];

// Check directory permissions
foreach ($directories as $dir) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/' . $dir;
    
    if (file_exists($fullPath)) {
        if (!is_writable($fullPath)) {
            $notWritable[] = $dir;
        }
    } else {
        $notWritable[] = $dir . ' (does not exist)';
    }
}

// Return result
if (empty($notWritable)) {
    echo json_encode(['success' => true, 'message' => 'All directories have correct permissions.']);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'The following directories are not writable: ' . implode(', ', $notWritable)
    ]);
}
?>
