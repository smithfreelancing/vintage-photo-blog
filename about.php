<?php
/*
* File: /vintage-photo-blog/about.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file displays information about the vintage photography blog.
* It provides details about the blog's purpose and mission.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "About";

// Include header
include 'includes/header.php';
?>

<!-- About Header -->
<section class="about-header bg-light py-5">
    <div class="container">
        <h1 class="text-center">About Our Blog</h1>
    </div>
</section>

<!-- About Content -->
<section class="about-content py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <h2>Our Story</h2>
                <p>Welcome to <?php echo SITE_NAME; ?>, a space dedicated to the art and craft of vintage photography. In a world dominated by digital imagery and instant gratification, we believe in the timeless beauty of analog photography and the stories it tells.</p>
                
                <p>Our blog was founded in 2023 by a group of passionate photographers who share a love for film cameras, darkroom techniques, and the unique aesthetic that only vintage photography can provide. We believe that there's something special about the process of shooting on film—the anticipation, the careful consideration of each frame, and the tactile nature of the medium.</p>
                
                <h2>Our Mission</h2>
                <p>At <?php echo SITE_NAME; ?>, our mission is to:</p>
                <ul>
                    <li>Preserve and celebrate the art of analog photography</li>
                    <li>Share knowledge and techniques with both beginners and experienced photographers</li>
                    <li>Showcase the work of photographers who embrace vintage methods</li>
                    <li>Build a community of like-minded individuals who appreciate the beauty of film</li>
                </ul>
                
                <h2>What We Cover</h2>
                <p>Our blog features a variety of content related to vintage photography, including:</p>
                <ul>
                    <li>Film camera reviews and recommendations</li>
                    <li>Darkroom techniques and tutorials</li>
                    <li>Interviews with film photographers</li>
                    <li>Photo essays and galleries</li>
                    <li>Historical perspectives on photography</li>
                    <li>Tips for beginners looking to explore film photography</li>
                </ul>
                
                <h2>Join Our Community</h2>
                <p>We invite you to join our community of vintage photography enthusiasts. Whether you're a seasoned film photographer or someone who's curious about getting started, there's a place for you here. Register for an account to comment on posts, share your own experiences, and connect with other members.</p>
                
                <p>Thank you for visiting our blog. We hope you find inspiration and valuable information as you explore the world of vintage photography with us.</p>
                
                <div class="text-center mt-5">
                    <a href="register.php" class="btn btn-dark">Join Our Community</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
