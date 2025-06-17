<?php
/*
* File: /vintage-photo-blog/test_comments.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file tests the comment system functionality.
* It verifies that comments can be created, displayed, and managed correctly.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "Comment System Test";

// Get a published post for testing
$db = new Database();
$db->query("SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 1");
$post = $db->single();

// If no published posts, create one for testing
if (!$post && isLoggedIn()) {
    $db->query("INSERT INTO posts (user_id, title, slug, content, status) 
                VALUES (:user_id, :title, :slug, :content, :status)");
    $db->bind(':user_id', $_SESSION['user_id']);
    $db->bind(':title', 'Test Post for Comments');
    $db->bind(':slug', 'test-post-for-comments');
    $db->bind(':content', '<p>This is a test post for testing the comment system.</p>');
    $db->bind(':status', 'published');
    $db->execute();
    
    $postId = $db->lastInsertId();
    
    // Get the newly created post
    $db->query("SELECT * FROM posts WHERE id = :id");
    $db->bind(':id', $postId);
    $post = $db->single();
}

// Get comments for the post
if ($post) {
    $db->query("SELECT c.*, u.username, u.first_name, u.last_name, u.profile_image 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.post_id = :post_id AND c.parent_id IS NULL
                ORDER BY c.created_at DESC");
    $db->bind(':post_id', $post['id']);
    $comments = $db->resultSet();
    
    // Get comment counts
    $db->query("SELECT status, COUNT(*) as count FROM comments WHERE post_id = :post_id GROUP BY status");
    $db->bind(':post_id', $post['id']);
    $commentCounts = $db->resultSet();
    
    $counts = [
        'total' => 0,
        'approved' => 0,
        'pending' => 0,
        'spam' => 0
    ];
    
    foreach ($commentCounts as $count) {
        $counts[$count['status']] = $count['count'];
        $counts['total'] += $count['count'];
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-5">Comment System Test</h1>
    
    <?php if (!$post): ?>
        <div class="alert alert-warning">
            No published posts found. Please <a href="create_post.php">create a post</a> first to test the comment system.
        </div>
    <?php else: ?>
        <section class="mb-5">
            <h2>Test Post</h2>
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title"><?php echo $post['title']; ?></h3>
                    <div class="card-text"><?php echo $post['content']; ?></div>
                    <a href="post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-dark mt-3">View Full Post</a>
                </div>
            </div>
        </section>
        
        <section class="mb-5">
            <h2>Comment Statistics</h2>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?php echo $counts['total']; ?></h3>
                            <p class="card-text">Total Comments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?php echo $counts['approved']; ?></h3>
                            <p class="card-text">Approved</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?php echo $counts['pending']; ?></h3>
                            <p class="card-text">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?php echo $counts['spam']; ?></h3>
                            <p class="card-text">Spam</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="mb-5">
            <h2>Test Comment Form</h2>
            <?php if (isLoggedIn()): ?>
                <form action="post.php?slug=<?php echo $post['slug']; ?>" method="post">
                    <div class="form-group">
                        <label for="comment_content">Add a Comment</label>
                        <textarea class="form-control" id="comment_content" name="comment_content" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-dark">Post Comment</button>
                </form>
                <div class="alert alert-info mt-3">
                    <p>This form will redirect you to the post page after submission.</p>
                    <p>If you're an admin, your comment will be automatically approved. Otherwise, it will be pending approval.</p>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    Please <a href="login.php">login</a> to test the comment system.
                </div>
            <?php endif; ?>
        </section>
        
        <?php if (isAdmin()): ?>
            <section class="mb-5">
                <h2>Admin Comment Management</h2>
                <p>As an admin, you can manage comments from the admin dashboard:</p>
                <a href="<?php echo ADMIN_URL; ?>/comments.php" class="btn btn-dark">Go to Comment Management</a>
            </section>
        <?php endif; ?>
        
        <section>
            <h2>Recent Comments</h2>
            <?php if (empty($comments)): ?>
                <div class="alert alert-info">
                    No comments found for this post. Add a comment to see it here.
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($comments as $comment): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">
                                    <?php echo !empty($comment['first_name']) ? $comment['first_name'] . ' ' . $comment['last_name'] : $comment['username']; ?>
                                </h5>
                                <small><?php echo formatDate($comment['created_at']); ?></small>
                            </div>
                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                            <small class="text-muted">
                                Status: 
                                <span class="badge <?php 
                                    if ($comment['status'] === 'approved') echo 'badge-success';
                                    elseif ($comment['status'] === 'pending') echo 'badge-warning';
                                    else echo 'badge-danger';
                                ?>">
                                    <?php echo $comment['status']; ?>
                                </span>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
