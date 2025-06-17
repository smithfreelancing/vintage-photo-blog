<?php
/*
* File: /vintage-photo-blog/profile.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles user profile viewing and editing.
* It displays user information and allows users to update their profiles.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Initialize variables
$errors = [];
$success = false;
$user = [];

// Get user data
$db = new Database();
$db->query("SELECT * FROM users WHERE id = :id");
$db->bind(':id', $_SESSION['user_id']);
$user = $db->single();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $firstName = clean($_POST['first_name']);
    $lastName = clean($_POST['last_name']);
    $email = clean($_POST['email']);
    $bio = clean($_POST['bio']);
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } elseif ($email !== $user['email']) {
        // Check if email already exists
        $db->query("SELECT id FROM users WHERE email = :email AND id != :id");
        $db->bind(':email', $email);
        $db->bind(':id', $_SESSION['user_id']);
        $existingUser = $db->single();
        
        if ($existingUser) {
            $errors[] = "Email already exists";
        }
    }
    
    // Handle password change if requested
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        // Validate new password
        if (empty($newPassword)) {
            $errors[] = "New password is required";
        } elseif (strlen($newPassword) < 6) {
            $errors[] = "New password must be at least 6 characters";
        }
        
        // Confirm new password
        if ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match";
        }
    }
    
    // Handle profile image upload
    $profileImage = $user['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadedImage = uploadImage($_FILES['profile_image'], 'profiles/');
        if ($uploadedImage) {
            $profileImage = $uploadedImage;
        } else {
            $errors[] = "Failed to upload profile image. Please ensure it's a valid image file (JPG, PNG, GIF) and under 5MB.";
        }
    }
    
    // If no errors, update user profile
    if (empty($errors)) {
        $query = "UPDATE users SET 
                  first_name = :first_name, 
                  last_name = :last_name, 
                  email = :email, 
                  bio = :bio";
        
        // Add profile image to query if it was uploaded
        if ($profileImage !== $user['profile_image']) {
            $query .= ", profile_image = :profile_image";
        }
        
        // Add password to query if it was changed
        if (!empty($newPassword)) {
            $query .= ", password = :password";
        }
        
        $query .= " WHERE id = :id";
        
        $db->query($query);
        $db->bind(':first_name', $firstName);
        $db->bind(':last_name', $lastName);
        $db->bind(':email', $email);
        $db->bind(':bio', $bio);
        
        // Bind profile image if it was uploaded
        if ($profileImage !== $user['profile_image']) {
            $db->bind(':profile_image', $profileImage);
        }
        
        // Bind password if it was changed
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->bind(':password', $hashedPassword);
        }
        
        $db->bind(':id', $_SESSION['user_id']);
        
        if ($db->execute()) {
            $success = true;
            
            // Update user data
            $db->query("SELECT * FROM users WHERE id = :id");
            $db->bind(':id', $_SESSION['user_id']);
            $user = $db->single();
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="text-center mb-4">Your Profile</h1>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Profile updated successfully!
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form action="profile.php" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    <div class="profile-image-container">
                                        <?php if (!empty($user['profile_image'])): ?>
                                            <img src="<?php echo UPLOAD_URL . $user['profile_image']; ?>" alt="Profile Image" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="default-profile-image rounded-circle mb-3 d-flex align-items-center justify-content-center" style="width: 150px; height: 150px; background-color: #e9ecef;">
                                                <i class="fas fa-user fa-4x text-secondary"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="profile_image" name="profile_image">
                                            <label class="custom-file-label" for="profile_image">Choose file</label>
                                        </div>
                                        <small class="form-text text-muted">Max file size: 5MB</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="first_name">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>">
                                        </div>
                                        
                                        <div class="form-group col-md-6">
                                            <label for="last_name">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="username">Username</label>
                                        <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                                        <small class="form-text text-muted">Username cannot be changed</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="bio">Bio</label>
                                        <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo $user['bio']; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h4>Change Password</h4>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                                
                                <div class="form-group col-md-4">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                                
                                <div class="form-group col-md-4">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-dark px-5">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="index.php" class="btn btn-link">Back to Home</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Show filename when file is selected
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    </script>
</body>
</html>
