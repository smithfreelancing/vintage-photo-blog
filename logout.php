<?php
/*
* File: /vintage-photo-blog/logout.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles user logout.
* It destroys the session and redirects to the home page.
*/

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
redirect('index.php');
?>
