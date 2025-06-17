<?php
/*
* File: /vintage-photo-blog/index.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file is the homepage of the vintage photography blog.
* It displays featured posts and recent posts.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check for maintenance mode
require_once 'maintenance.php';

$pageTitle = "Home";

// Get featured posts
$db = new Database();
$db->query("SELECT p.*, u.username, u.first_name, u.last_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.status = 'published' 
            ORDER BY p.created_at DESC 
            LIMIT 6");
$featuredPosts = $db->resultSet();

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Vintage Photography</h1>
        <p>Exploring the beauty and nostalgia of film photography and vintage techniques.</p>
        <a href="about.php" class="btn btn-outline-dark">Learn More</a>
    </div>
</section>

<!-- Featured Posts -->
<section class="featured-posts">
    <div class="container">
        <h2 class="text-center mb-4">Latest Posts</h2>
        
        <div class="row">
            <?php if (!empty($featuredPosts)): ?>
                <?php foreach ($featuredPosts as $post): ?>
                    <div class="col-md-4">
                        <div class="post-card">
                            <div class="card">
                                <?php if (!empty($post['featured_image'])): ?>
                                    <img src="<?php echo UPLOAD_URL . $post['featured_image']; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 240px;">
                                        <i class="fas fa-camera fa-3x text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <div class="post-meta">
                                        <?php echo formatDate($post['created_at']); ?> by 
                                        <?php echo !empty($post['first_name']) ? $post['first_name'] . ' ' . $post['last_name'] : $post['username']; ?>
                                    </div>
                                    <h5 class="card-title"><?php echo $post['title']; ?></h5>
                                    <p class="card-text"><?php echo truncateText(strip_tags($post['content']), 120); ?></p>
                                    <a href="post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-dark">Read More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No posts found. Check back soon for new content!
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-4">Explore Categories</h2>
        
        <?php
        // Get categories
        $db->query("SELECT * FROM categories ORDER BY name");
        $categories = $db->resultSet();
        ?>
        
        <div class="row justify-content-center">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $category['name']; ?></h5>
                                <p class="card-text small"><?php echo truncateText($category['description'], 80); ?></p>
                                <a href="category.php?slug=<?php echo $category['slug']; ?>" class="btn btn-sm btn-outline-dark">View Posts</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No categories found.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h2 class="mb-4">Subscribe to Our Newsletter</h2>
                <p class="mb-4">Stay updated with our latest vintage photography posts and tips.</p>
                
                <form class="form-inline justify-content-center">
                    <div class="form-group mr-2 mb-2">
                        <label for="email" class="sr-only">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="Your Email Address">
                    </div>
                    <button type="submit" class="btn btn-dark mb-2">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
