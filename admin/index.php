<?php
/*
* File: /vintage-photo-blog/admin/index.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file is the main admin dashboard.
* It displays statistics and quick links for site management.
*/

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = "Admin Dashboard";

// Get statistics
$db = new Database();

// Total posts
$db->query("SELECT COUNT(*) as total FROM posts");
$totalPosts = $db->single()['total'];

// Published posts
$db->query("SELECT COUNT(*) as total FROM posts WHERE status = 'published'");
$publishedPosts = $db->single()['total'];

// Draft posts
$db->query("SELECT COUNT(*) as total FROM posts WHERE status = 'draft'");
$draftPosts = $db->single()['total'];

// Total users
$db->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $db->single()['total'];

// Total comments
$db->query("SELECT COUNT(*) as total FROM comments");
$totalComments = $db->single()['total'];

// Pending comments
$db->query("SELECT COUNT(*) as total FROM comments WHERE status = 'pending'");
$pendingComments = $db->single()['total'];

// Total categories
$db->query("SELECT COUNT(*) as total FROM categories");
$totalCategories = $db->single()['total'];

// Recent posts
$db->query("SELECT p.*, u.username, u.first_name, u.last_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            ORDER BY p.created_at DESC 
            LIMIT 5");
$recentPosts = $db->resultSet();

// Recent comments
$db->query("SELECT c.*, p.title as post_title, p.slug as post_slug, u.username, u.first_name, u.last_name 
            FROM comments c 
            JOIN posts p ON c.post_id = p.id 
            JOIN users u ON c.user_id = u.id 
            ORDER BY c.created_at DESC 
            LIMIT 5");
$recentComments = $db->resultSet();

// Include header
include '../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Dashboard</h1>
    
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card card-primary h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Posts</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $totalPosts; ?></div>
                            <div class="small text-muted"><?php echo $publishedPosts; ?> published, <?php echo $draftPosts; ?> drafts</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt stats-icon text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card card-success h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Comments</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $totalComments; ?></div>
                            <div class="small text-muted"><?php echo $pendingComments; ?> pending approval</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments stats-icon text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card card-warning h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Users</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $totalUsers; ?></div>
                            <div class="small text-muted">Registered users</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users stats-icon text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card card-danger h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Categories</div>
                            <div class="h5 mb-0 font-weight-bold"><?php echo $totalCategories; ?></div>
                            <div class="small text-muted">Content categories</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags stats-icon text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="<?php echo SITE_URL; ?>/create_post.php" class="btn btn-primary btn-block">
                                <i class="fas fa-plus mr-2"></i> Create Post
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="categories.php" class="btn btn-success btn-block">
                                <i class="fas fa-tag mr-2"></i> Manage Categories
                            </a>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <a href="comments.php?status=pending" class="btn btn-warning btn-block">
                                <i class="fas fa-comment mr-2"></i> Moderate Comments
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="users.php" class="btn btn-danger btn-block">
                                <i class="fas fa-user mr-2"></i> Manage Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Posts -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Posts</h5>
                    <a href="posts.php" class="btn btn-sm btn-outline-dark">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentPosts)): ?>
                        <div class="p-3">No posts found.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentPosts as $post): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>" target="_blank">
                                                <?php echo $post['title']; ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted"><?php echo formatDate($post['created_at']); ?></small>
                                    </div>
                                    <p class="mb-1 small text-muted">
                                        By <?php echo !empty($post['first_name']) ? $post['first_name'] . ' ' . $post['last_name'] : $post['username']; ?>
                                        <span class="badge <?php echo $post['status'] === 'published' ? 'badge-success' : 'badge-secondary'; ?>">
                                            <?php echo $post['status']; ?>
                                        </span>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Comments -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Comments</h5>
                    <a href="comments.php" class="btn btn-sm btn-outline-dark">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentComments)): ?>
                        <div class="p-3">No comments found.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentComments as $comment): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <?php echo !empty($comment['first_name']) ? $comment['first_name'] . ' ' . $comment['last_name'] : $comment['username']; ?>
                                            <span class="badge <?php 
                                                if ($comment['status'] === 'approved') echo 'badge-success';
                                                elseif ($comment['status'] === 'pending') echo 'badge-warning';
                                                else echo 'badge-danger';
                                            ?>">
                                                <?php echo $comment['status']; ?>
                                            </span>
                                        </h6>
                                        <small class="text-muted"><?php echo formatDate($comment['created_at']); ?></small>
                                    </div>
                                    <p class="mb-1 small"><?php echo truncateText(htmlspecialchars($comment['content']), 80); ?></p>
                                    <small class="text-muted">
                                        On: <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo $comment['post_slug']; ?>#comment-<?php echo $comment['id']; ?>" target="_blank">
                                            <?php echo truncateText($comment['post_title'], 40); ?>
                                        </a>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/admin_footer.php';
?>
