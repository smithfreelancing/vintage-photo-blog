<?php
/*
* File: /vintage-photo-blog/contact.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file displays and processes the contact form.
* It allows visitors to send messages to the site administrators.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "Contact Us";

// Initialize variables
$name = $email = $subject = $message = '';
$errors = [];
$success = false;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $subject = clean($_POST['subject']);
    $message = clean($_POST['message']);
    
    // Validate form data
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // In a real application, you would send an email here
        // For now, we'll just simulate success
        $success = true;
        
        // Clear form data after successful submission
        $name = $email = $subject = $message = '';
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Contact Header -->
<section class="contact-header bg-light py-5">
    <div class="container">
        <h1 class="text-center">Contact Us</h1>
        <p class="text-center mb-0">Have questions or suggestions? We'd love to hear from you.</p>
    </div>
</section>

<!-- Contact Form -->
<section class="contact-form py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Message Sent!</h4>
                        <p>Thank you for contacting us. We will get back to you as soon as possible.</p>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="contact.php" method="post">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                                                        <input type="text" class="form-control" id="subject" name="subject" value="<?php echo $subject; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?php echo $message; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-dark">Send Message</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information -->
<section class="contact-info bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="text-center">
                    <i class="fas fa-envelope fa-2x mb-3"></i>
                    <h4>Email</h4>
                    <p><a href="mailto:info@vintagephotoblog.com">info@vintagephotoblog.com</a></p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="text-center">
                    <i class="fas fa-map-marker-alt fa-2x mb-3"></i>
                    <h4>Location</h4>
                    <p>123 Camera Street<br>Photography City, PC 12345</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <i class="fas fa-share-alt fa-2x mb-3"></i>
                    <h4>Social Media</h4>
                    <div class="social-links">
                        <a href="#" class="mr-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="mr-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="mr-2"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>

