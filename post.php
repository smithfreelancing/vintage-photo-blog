<?php
/*
* File: /vintage-photo-blog/post.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file displays a single blog post.
* It shows the post content and related information.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if post slug is provided
if (!isset($_GET['slug'])) {
    redirect('index.php');
}

$postSlug = clean($_GET['slug']);

// Get post information
$db = new Database();
$db->query("SELECT p.*, u.username, u.first_name, u.last_name, u.profile_image 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.slug = :slug AND p.status = 'published'");
$db->bind(':slug', $postSlug);
$post = $db->single();

// If post doesn't exist or is not published, redirect to homepage
if (!$post) {
    redirect('index.php');
}

// Get post categories
$db->query("SELECT c.* 
            FROM categories c 
            JOIN post_categories pc ON c.id = pc.category_id 
            WHERE pc.post_id = :post_id");
$db->bind(':post_id', $post['id']);
$categories = $db->resultSet();

$pageTitle = $post['title'];

// Include header
include 'includes/header.php';
?>

<!-- Post Content -->
<article class="post-single py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Post Header -->
                <header class="post-header">
                    <h1 class="post-title"><?php echo $post['title']; ?></h1>
                    
                    <div class="post-meta mb-3">
                        <span class="post-date"><?php echo formatDate($post['created_at']); ?></span>
                        <span class="post-author">by 
                            <?php echo !empty($post['first_name']) ? $post['first_name'] . ' ' . $post['last_name'] : $post['username']; ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($categories)): ?>
                        <div class="post-categories mb-3">
                            <?php foreach ($categories as $category): ?>
                                <a href="category.php?slug=<?php echo $category['slug']; ?>" class="category-pill"><?php echo $category['name']; ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </header>
                
                <!-- Featured Image -->
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="post-featured-image">
                        <img src="<?php echo UPLOAD_URL . $post['featured_image']; ?>" class="img-fluid" alt="<?php echo $post['title']; ?>">
                    </div>
                <?php endif; ?>
                
                <!-- Post Content -->
                <div class="post-content">
                    <?php echo $post['content']; ?>
                </div>
                
                <!-- Author Bio -->
                <div class="author-bio mt-5 p-4 bg-light">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <?php if (!empty($post['profile_image'])): ?>
                                <img src="<?php echo UPLOAD_URL . $post['profile_image']; ?>" alt="Author" class="rounded-circle img-fluid" style="max-width: 80px;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                                    <i class="fas fa-user fa-2x text-white"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-10">
                            <h5 class="mb-1">
                                <?php echo !empty($post['first_name']) ? $post['first_name'] . ' ' . $post['last_name'] : $post['username']; ?>
                            </h5>
                            <p class="small text-muted mb-2">Author</p>
                            <p class="mb-0">
                                <?php
                                // Get author bio from database
                                $db->query("SELECT bio FROM users WHERE id = :user_id");
                                $db->bind(':user_id', $post['user_id']);
                                $author = $db->single();
                                echo !empty($author['bio']) ? $author['bio'] : 'This author has not provided a bio yet.';
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Post Navigation -->
                <div class="post-navigation mt-5 pt-4 border-top">
                    <div class="row">
                        <?php
                        // Get previous post
                        $db->query("SELECT id, title, slug FROM posts 
                                    WHERE id < :current_id AND status = 'published' 
                                    ORDER BY id DESC LIMIT 1");
                        $db->bind(':current_id', $post['id']);
                        $prevPost = $db->single();
                        
                        // Get next post
                        $db->query("SELECT id, title, slug FROM posts 
                                    WHERE id > :current_id AND status = 'published' 
                                    ORDER BY id ASC LIMIT 1");
                        $db->bind(':current_id', $post['id']);
                        $nextPost = $db->single();
                        ?>
                        
                        <div class="col-6">
                            <?php if ($prevPost): ?>
                                <a href="post.php?slug=<?php echo $prevPost['slug']; ?>" class="post-nav-link prev">
                                    <span class="small text-muted d-block">Previous Post</span>
                                    <span class="font-weight-bold"><?php echo truncateText($prevPost['title'], 40); ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-6 text-right">
                            <?php if ($nextPost): ?>
                                <a href="post.php?slug=<?php echo $nextPost['slug']; ?>" class="post-nav-link next">
                                    <span class="small text-muted d-block">Next Post</span>
                                    <span class="font-weight-bold"><?php echo truncateText($nextPost['title'], 40); ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Comments section will be added in the next phase -->
                <div class="comments-section mt-5 pt-4 border-top">
                    <h3>Comments</h3>
                    <p>Comments will be implemented in the next phase.</p>
                </div>
            </div>
        </div>
    </div>
</article>

<?php
// Include footer
include 'includes/footer.php';
?>
