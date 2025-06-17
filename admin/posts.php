<?php
/*
* File: /vintage-photo-blog/admin/posts.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles post management for administrators.
* It allows admins to view, edit, and delete posts.
*/

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = "Manage Posts";

// Handle post deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $postId = (int)$_GET['id'];
    
    $db = new Database();
    $db->query("DELETE FROM posts WHERE id = :id");
    $db->bind(':id', $postId);
    
    if ($db->execute()) {
        redirect('posts.php?message=Post deleted successfully');
    } else {
        redirect('posts.php?error=Failed to delete post');
    }
}

// Handle post status change
if (isset($_GET['status']) && isset($_GET['id']) && in_array($_GET['status'], ['published', 'draft'])) {
    $postId = (int)$_GET['id'];
    $status = $_GET['status'];
    
    $db = new Database();
    $db->query("UPDATE posts SET status = :status WHERE id = :id");
    $db->bind(':status', $status);
    $db->bind(':id', $postId);
    
    if ($db->execute()) {
        redirect('posts.php?message=Post status updated');
    } else {
        redirect('posts.php?error=Failed to update post status');
    }
}

// Get filter parameters
$statusFilter = isset($_GET['filter']) ? clean($_GET['filter']) : 'all';
$search = isset($_GET['search']) ? clean($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Pagination setup
$postsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

// Build the query based on filters
$db = new Database();

$query = "SELECT p.*, u.username, u.first_name, u.last_name 
          FROM posts p 
          JOIN users u ON p.user_id = u.id";

$countQuery = "SELECT COUNT(*) as total FROM posts p";

$conditions = [];
$bindings = [];

// Status filter
if ($statusFilter !== 'all') {
    $conditions[] = "p.status = :status";
    $bindings[':status'] = $statusFilter;
}

// Search filter
if (!empty($search)) {
    $conditions[] = "(p.title LIKE :search OR p.content LIKE :search)";
    $bindings[':search'] = '%' . $search . '%';
}

// Category filter
if ($categoryId > 0) {
    $query .= " JOIN post_categories pc ON p.id = pc.post_id";
    $countQuery .= " JOIN post_categories pc ON p.id = pc.post_id";
    $conditions[] = "pc.category_id = :category_id";
    $bindings[':category_id'] = $categoryId;
}

// Add conditions to queries
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
    $countQuery .= " WHERE " . implode(" AND ", $conditions);
}

// Add order and limit
$query .= " ORDER BY p.created_at DESC LIMIT :offset, :limit";

// Get posts
$db->query($query);

// Bind parameters
foreach ($bindings as $param => $value) {
    $db->bind($param, $value);
}

$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $postsPerPage, PDO::PARAM_INT);
$posts = $db->resultSet();

// Get total posts for pagination
$db->query($countQuery);

// Bind parameters for count query
foreach ($bindings as $param => $value) {
    $db->bind($param, $value);
}

$totalPosts = $db->single()['total'];
$totalPages = ceil($totalPosts / $postsPerPage);

// Get categories for filter dropdown
$db->query("SELECT * FROM categories ORDER BY name");
$categories = $db->resultSet();

// Include header
include '../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Manage Posts</h1>
        <a href="<?php echo SITE_URL; ?>/create_post.php" class="btn btn-dark">
            <i class="fas fa-plus mr-2"></i> Create New Post
        </a>
    </div>
    
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success">
            <?php echo $_GET['message']; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_GET['error']; ?>
        </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="posts.php" method="get" class="row">
                <div class="col-md-3 mb-3 mb-md-0">
                    <label for="filter">Status</label>
                    <select name="filter" id="filter" class="form-control">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="published" <?php echo $statusFilter === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3 mb-md-0">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="form-control">
                        <option value="0">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $categoryId === (int)$category['id'] ? 'selected' : ''; ?>>
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3 mb-md-0">
                    <label for="search">Search</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search posts..." value="<?php echo $search; ?>">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark btn-block">Filter</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Posts Table -->
    <?php if (empty($posts)): ?>
        <div class="alert alert-info">
            No posts found matching your criteria.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>" target="_blank">
                                    <?php echo $post['title']; ?>
                                </a>
                            </td>
                            <td>
                                <?php echo !empty($post['first_name']) ? $post['first_name'] . ' ' . $post['last_name'] : $post['username']; ?>
                            </td>
                            <td>
                                <?php if ($post['status'] === 'published'): ?>
                                    <span class="badge badge-success">Published</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($post['created_at']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-outline-dark" target="_blank" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-dark" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($post['status'] === 'draft'): ?>
                                        <a href="posts.php?status=published&id=<?php echo $post['id']; ?>" class="btn btn-outline-success" title="Publish" onclick="return confirm('Publish this post?');">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="posts.php?status=draft&id=<?php echo $post['id']; ?>" class="btn btn-outline-secondary" title="Unpublish" onclick="return confirm('Change this post to draft?');">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="posts.php?delete=1&id=<?php echo $post['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
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
                            <a class="page-link" href="posts.php?page=<?php echo $page - 1; ?>&filter=<?php echo $statusFilter; ?>&category=<?php echo $categoryId; ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                <span aria-hidden="true">«</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="posts.php?page=<?php echo $i; ?>&filter=<?php echo $statusFilter; ?>&category=<?php echo $categoryId; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="posts.php?page=<?php echo $page + 1; ?>&filter=<?php echo $statusFilter; ?>&category=<?php echo $categoryId; ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                <span aria-hidden="true">»</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
// Include footer
include '../includes/admin_footer.php';
?>
