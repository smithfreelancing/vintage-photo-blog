<?php
/*
* File: /vintage-photo-blog/my_posts.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file displays a list of the current user's posts.
* It allows users to manage their own posts.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = "My Posts";

// Handle post deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $postId = (int)$_GET['id'];
    
    // Get post information
    $db = new Database();
    $db->query("SELECT * FROM posts WHERE id = :id");
    $db->bind(':id', $postId);
    $post = $db->single();
    
    // Check if post exists and user is the author or an admin
    if ($post && ($post['user_id'] == $_SESSION['user_id'] || isAdmin())) {
        // Delete post
        $db->query("DELETE FROM posts WHERE id = :id");
        $db->bind(':id', $postId);
        $db->execute();
        
        // Redirect to refresh the page
        redirect('my_posts.php?deleted=1');
    }
}

// Pagination setup
$postsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

// Get user's posts
$db = new Database();

// If user is admin, get all posts
if (isAdmin()) {
    $db->query("SELECT p.*, u.username, u.first_name, u.last_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT :offset, :limit");
} else {
    // Otherwise, get only the user's posts
    $db->query("SELECT p.*, u.username, u.first_name, u.last_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.user_id = :user_id 
                ORDER BY p.created_at DESC 
                LIMIT :offset, :limit");
    $db->bind(':user_id', $_SESSION['user_id']);
}

$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $postsPerPage, PDO::PARAM_INT);
$posts = $db->resultSet();

// Get total number of posts for pagination
if (isAdmin()) {
    $db->query("SELECT COUNT(*) as total FROM posts");
} else {
    $db->query("SELECT COUNT(*) as total FROM posts WHERE user_id = :user_id");
    $db->bind(':user_id', $_SESSION['user_id']);
}
$totalPosts = $db->single()['total'];
$totalPages = ceil($totalPosts / $postsPerPage);

// Include header
include 'includes/header.php';
?>

<!-- My Posts Header -->
<section class="my-posts-header bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0"><?php echo isAdmin() ? 'All Posts' : 'My Posts'; ?></h1>
            </div>
            <div class="col-md-4 text-md-right">
                <a href="create_post.php" class="btn btn-dark">
                    <i class="fas fa-plus mr-2"></i> Create New Post
                </a>
            </div>
        </div>
    </div>
</section>

<!-- My Posts List -->
<section class="my-posts-list py-5">
    <div class="container">
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">
                Post deleted successfully.
            </div>
        <?php endif; ?>
        
        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                <?php echo isAdmin() ? 'No posts found.' : 'You haven\'t created any posts yet.'; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Title</th>
                            <?php if (isAdmin()): ?>
                                <th>Author</th>
                            <?php endif; ?>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <a href="post.php?slug=<?php echo $post['slug']; ?>" class="font-weight-bold">
                                        <?php echo $post['title']; ?>
                                    </a>
                                </td>
                                <?php if (isAdmin()): ?>
                                    <td>
                                        <?php echo !empty($post['first_name']) ? $post['first_name'] . ' ' . $post['last_name'] : $post['username']; ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($post['status'] === 'published'): ?>
                                        <span class="badge badge-success">Published</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($post['created_at']); ?></td>
                                <td>
                                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-dark mr-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="my_posts.php?delete=1&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this post?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="my_posts.php?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">«</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="my_posts.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="my_posts.php?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">»</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
