<?php
/*
* File: /vintage-photo-blog/category.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file displays posts from a specific category.
* It shows a list of posts filtered by the selected category.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if category slug is provided
if (!isset($_GET['slug'])) {
    redirect('index.php');
}

$categorySlug = clean($_GET['slug']);

// Get category information
$db = new Database();
$db->query("SELECT * FROM categories WHERE slug = :slug");
$db->bind(':slug', $categorySlug);
$category = $db->single();

// If category doesn't exist, redirect to homepage
if (!$category) {
    redirect('index.php');
}

$pageTitle = $category['name'];

// Pagination setup
$postsPerPage = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

// Get posts from this category
$db->query("SELECT p.*, u.username, u.first_name, u.last_name 
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            JOIN post_categories pc ON p.id = pc.post_id 
            WHERE pc.category_id = :category_id AND p.status = 'published' 
            ORDER BY p.created_at DESC 
            LIMIT :offset, :limit");
$db->bind(':category_id', $category['id']);
$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $postsPerPage, PDO::PARAM_INT);
$posts = $db->resultSet();

// Get total number of posts for pagination
$db->query("SELECT COUNT(*) as total 
            FROM posts p 
            JOIN post_categories pc ON p.id = pc.post_id 
            WHERE pc.category_id = :category_id AND p.status = 'published'");
$db->bind(':category_id', $category['id']);
$totalPosts = $db->single()['total'];
$totalPages = ceil($totalPosts / $postsPerPage);

// Include header
include 'includes/header.php';
?>

<!-- Category Header -->
<section class="category-header bg-light py-5">
    <div class="container">
        <h1 class="text-center"><?php echo $category['name']; ?></h1>
        <?php if (!empty($category['description'])): ?>
            <p class="text-center mb-0"><?php echo $category['description']; ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- Category Posts -->
<section class="category-posts py-5">
    <div class="container">
        <div class="row">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-4 mb-4">
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
                        No posts found in this category. Check back soon for new content!
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="category.php?slug=<?php echo $categorySlug; ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">«</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="category.php?slug=<?php echo $categorySlug; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="category.php?slug=<?php echo $categorySlug; ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">»</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>
