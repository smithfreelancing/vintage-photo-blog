<?php
/*
* File: /vintage-photo-blog/maintenance.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file provides a maintenance mode for the site.
* It displays a maintenance message to visitors while allowing admins to still access the site.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if maintenance mode is enabled
$maintenanceMode = false;

// Check if there's a setting for maintenance mode
try {
    $db = new Database();
    $db->query("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
    $result = $db->single();
    
    if ($result && $result['setting_value'] == '1') {
        $maintenanceMode = true;
    }
} catch (Exception $e) {
    // If there's an error, default to not being in maintenance mode
    $maintenanceMode = false;
}

// Allow admins to bypass maintenance mode
if ($maintenanceMode && (!isLoggedIn() || !isAdmin())) {
    // Get maintenance message
    $db->query("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_message'");
    $messageResult = $db->single();
    $maintenanceMessage = $messageResult ? $messageResult['setting_value'] : 'We are currently performing maintenance. Please check back soon.';
    
    // Display maintenance page
    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 3600'); // 1 hour
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .maintenance-container {
            max-width: 600px;
            text-align: center;
            background-color: #fff;
            padding: 40px;
            border-radius: 4px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        h1 {
            font-weight: 300;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        .login-link {
            margin-top: 30px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <h1>Site Maintenance</h1>
        <p><?php echo $maintenanceMessage; ?></p>
        <div class="login-link">
            <a href="login.php">Admin Login</a>
        </div>
    </div>
</body>
</html>
<?php
    exit;
}

// If not in maintenance mode or user is an admin, include this file at the top of all pages
// to check for maintenance mode
?>
