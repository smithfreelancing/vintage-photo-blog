<?php
/*
* File: /vintage-photo-blog/includes/config.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file contains the main configuration settings for the application.
* It defines constants and settings used throughout the application.
*/

// Define site constants
define('SITE_NAME', 'Vintage Photography');
define('SITE_URL', 'http://iamblogging.com/vintage-photo-blog');
define('ADMIN_URL', SITE_URL . '/admin');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'smithfre_admin');
define('DB_PASS', 'NewSecurePassword123!');
define('DB_NAME', 'smithfre_vintage_photo_blog');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
session_start();

// Time zone
date_default_timezone_set('UTC');

// Define upload paths
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/vintage-photo-blog/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Define allowed file types for uploads
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
// No closing PHP tag

