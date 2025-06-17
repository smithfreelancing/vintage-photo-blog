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
$db->query("SELECT p.*, u.username, u.first_name, u.last_name, u.profile_image, u.bio 
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

// Get related posts (posts in the same categories)
$categoryIds = array_map(function($category) {
    return $category['id'];
}, $categories);

if (!empty($categoryIds)) {
    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
    
    $db->query("SELECT DISTINCT p.*, u.username, u.first_name, u.last_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                JOIN post_categories pc ON p.id = pc.post_id 
                WHERE pc.category_id IN ($placeholders) 
                AND p.id != ? 
                AND p.status = 'published' 
                ORDER BY p.created_at DESC 
                LIMIT 3");
    
    // Bind category IDs
    $paramIndex = 1;
    foreach ($categoryIds as $categoryId) {
        $db->bind($paramIndex, $categoryId);
        $paramIndex++;
    }
    
    // Bind post ID
    $db->bind($paramIndex, $post['id']);
    
    $relatedPosts = $db->resultSet();
} else {
    $relatedPosts = [];
}

// Handle comment submission
$commentError = '';
$commentSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    // Check if user is logged in
    if (!isLoggedIn()) {
        $commentError = "You must be logged in to comment.";
    } else {
        $commentContent = clean($_POST['comment_content']);
        $parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        
        // Validate comment
        if (empty($commentContent)) {
            $commentError = "Comment cannot be empty.";
        } else {
            // Insert comment
            $db->query("INSERT INTO comments (post_id, user_id, parent_id, content, status) 
                        VALUES (:post_id, :user_id, :parent_id, :content, :status)");
            $db->bind(':post_id', $post['id']);
            $db->bind(':user_id', $_SESSION['user_id']);
            $db->bind(':parent_id', $parentId ?: null);
            $db->bind(':content', $commentContent);
            
            // If user is admin, auto-approve comment
            $status = isAdmin() ? 'approved' : 'pending';
            $db->bind(':status', $status);
            
            if ($db->execute()) {
                $commentSuccess = true;
            } else {
                $commentError = "Failed to post comment. Please try again.";
            }
        }
    }
}

// Get approved comments for this post
$db->query("SELECT c.*, u.username, u.first_name, u.last_name, u.profile_image 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = :post_id AND c.parent_id IS NULL AND c.status = 'approved' 
            ORDER BY c.created_at DESC");
$db->bind(':post_id', $post['id']);
$comments = $db->resultSet();

// Get replies for each comment
foreach ($comments as &$comment) {
    $db->query("SELECT c.*, u.username, u.first_name, u.last_name, u.profile_image 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.parent_id = :comment_id AND c.status = 'approved' 
                ORDER BY c.created_at ASC");
    $db->bind(':comment_id', $comment['id']);
    $comment['replies'] = $db->resultSet();
}

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
                                <?php echo !empty($post['bio']) ? $post['bio'] : 'This author has not provided a bio yet.'; ?>
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
                
                <!-- Related Posts -->
                <?php if (!empty($relatedPosts)): ?>
                    <div class="related-posts mt-5 pt-4 border-top">
                        <h3 class="mb-4">Related Posts</h3>
                        <div class="row">
                            <?php foreach ($relatedPosts as $relatedPost): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <?php if (!empty($relatedPost['featured_image'])): ?>
                                            <img src="<?php echo UPLOAD_URL . $relatedPost['featured_image']; ?>" class="card-img-top" alt="<?php echo $relatedPost['title']; ?>" style="height: 150px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                                <i class="fas fa-camera fa-2x text-secondary"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title" style="font-size: 1rem;"><?php echo truncateText($relatedPost['title'], 40); ?></h5>
                                            <a href="post.php?slug=<?php echo $relatedPost['slug']; ?>" class="btn btn-sm btn-outline-dark">Read More</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Comments Section -->
                <div class="comments-section mt-5 pt-4 border-top">
                    <h3 class="mb-4">Comments</h3>
                    
                    <?php if ($commentSuccess): ?>
                        <div class="alert alert-success">
                            <?php if (isAdmin()): ?>
                                Your comment has been posted successfully.
                            <?php else: ?>
                                Your comment has been submitted and is awaiting approval.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($commentError)): ?>
                        <div class="alert alert-danger">
                            <?php echo $commentError; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Comment Form -->
                    <div class="comment-form mb-5">
                        <?php if (isLoggedIn()): ?>
                            <form action="post.php?slug=<?php echo $postSlug; ?>" method="post" id="commentForm">
                                <div class="form-group">
                                    <label for="comment_content">Leave a Comment</label>
                                    <textarea class="form-control" id="comment_content" name="comment_content" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-dark">Post Comment</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Please <a href="login.php">login</a> or <a href="register.php">register</a> to leave a comment.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Comments List -->
                    <?php if (!empty($comments)): ?>
                        <div class="comments-list">
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment mb-4" id="comment-<?php echo $comment['id']; ?>">
                                    <div class="comment-header d-flex">
                                        <div class="comment-avatar mr-3">
                                            <?php if (!empty($comment['profile_image'])): ?>
                                                <img src="<?php echo UPLOAD_URL . $comment['profile_image']; ?>" alt="<?php echo $comment['username']; ?>" class="rounded-circle" width="50" height="50">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comment-meta">
                                            <h5 class="mb-1">
                                                <?php echo !empty($comment['first_name']) ? $comment['first_name'] . ' ' . $comment['last_name'] : $comment['username']; ?>
                                            </h5>
                                            <p class="text-muted small mb-0"><?php echo formatDate($comment['created_at']); ?></p>
                                        </div>
                                    </div>
                                    <div class="comment-body mt-3">
                                        <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                    </div>
                                    
                                    <?php if (isLoggedIn()): ?>
                                        <div class="comment-actions mt-2">
                                            <button class="btn btn-sm btn-link reply-btn" data-comment-id="<?php echo $comment['id']; ?>">
                                                <i class="fas fa-reply mr-1"></i> Reply
                                            </button>
                                        </div>
                                        
                                        <!-- Reply Form (hidden by default) -->
                                        <div class="reply-form mt-3" id="reply-form-<?php echo $comment['id']; ?>" style="display: none;">
                                            <form action="post.php?slug=<?php echo $postSlug; ?>" method="post">
                                                <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                                <div class="form-group">
                                                    <textarea class="form-control" name="comment_content" rows="3" required></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-dark">Submit Reply</button>
                                                <button type="button" class="btn btn-sm btn-light cancel-reply" data-comment-id="<?php echo $comment['id']; ?>">Cancel</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Replies -->
                                    <?php if (!empty($comment['replies'])): ?>
                                        <div class="comment-replies mt-3 ml-5">
                                            <?php foreach ($comment['replies'] as $reply): ?>
                                                <div class="comment reply mb-3" id="comment-<?php echo $reply['id']; ?>">
                                                    <div class="comment-header d-flex">
                                                        <div class="comment-avatar mr-3">
                                                            <?php if (!empty($reply['profile_image'])): ?>
                                                                <img src="<?php echo UPLOAD_URL . $reply['profile_image']; ?>" alt="<?php echo $reply['username']; ?>" class="rounded-circle" width="40" height="40">
                                                            <?php else: ?>
                                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-user text-white"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="comment-meta">
                                                            <h6 class="mb-1">
                                                                <?php echo !empty($reply['first_name']) ? $reply['first_name'] . ' ' . $reply['last_name'] : $reply['username']; ?>
                                                            </h6>
                                                            <p class="text-muted small mb-0"><?php echo formatDate($reply['created_at']); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="comment-body mt-2">
                                                        <p><?php echo nl2br(htmlspecialchars($reply['content'])); ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light">
                            No comments yet. Be the first to comment!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</article>

<?php
// Include footer
include 'includes/footer.php';
?>

<script>
    // Toggle reply form
    document.querySelectorAll('.reply-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var commentId = this.getAttribute('data-comment-id');
            document.getElementById('reply-form-' + commentId).style.display = 'block';
        });
    });
    
    // Cancel reply
    document.querySelectorAll('.cancel-reply').forEach(function(button) {
        button.addEventListener('click', function() {
            var commentId = this.getAttribute('data-comment-id');
            document.getElementById('reply-form-' + commentId).style.display = 'none';
        });
    });
</script>

