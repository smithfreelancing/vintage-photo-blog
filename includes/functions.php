<?php
/*
* File: /vintage-photo-blog/includes/functions.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file contains helper functions used throughout the application.
* It provides utility functions for common tasks.
*/

/**
 * Clean and sanitize input data
 * @param string $data - The data to be sanitized
 * @return string - The sanitized data
 */
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Create URL friendly slug from a string
 * @param string $string - The string to convert to a slug
 * @return string - The slug
 */
function createSlug($string) {
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Check if user is logged in
 * @return boolean - True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is an admin
 * @return boolean - True if user is an admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to a specific page
 * @param string $location - The URL to redirect to
 */
function redirect($location) {
    header("Location: {$location}");
    exit;
}

/**
 * Display error message
 * @param string $message - The error message to display
 * @return string - HTML for the error message
 */
function displayError($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

/**
 * Display success message
 * @param string $message - The success message to display
 * @return string - HTML for the success message
 */
function displaySuccess($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}

/**
 * Format date in a readable format
 * @param string $date - The date to format
 * @return string - The formatted date
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Truncate text to a specific length
 * @param string $text - The text to truncate
 * @param int $length - The maximum length
 * @return string - The truncated text
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    return $text . '...';
}

/**
 * Upload an image file
 * @param array $file - The file from $_FILES
 * @param string $destination - The destination directory
 * @return string|bool - The file path if successful, false otherwise
 */
function uploadImage($file, $destination = 'photos/') {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check if file is an allowed type
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Create unique filename
    $fileName = uniqid() . '_' . basename($file['name']);
    $uploadPath = UPLOAD_PATH . $destination . $fileName;
    
    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $destination . $fileName;
    }
    
    return false;
}

/**
 * Get current page URL
 * @return string - The current page URL
 */
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
?>
