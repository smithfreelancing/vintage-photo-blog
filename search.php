<?php
/*
* File: /vintage-photo-blog/search.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles the search functionality.
* It displays search results based on user queries.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if search query is provided
$query = isset($_GET['q']) ? clean($_GET['q']) : '';

$pageTitle = "Search Results";

// Pagination setup
$postsPerPage = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $postsPerPage;

// Get search results
$db = new Database();

if (!empty($query)) {
    // Search in posts
    $searchTerm = '%' . $query . '%';
    
    $db->query("SELECT p.*, u.username, u.first_name, u.last_name 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE (p.title LIKE :search_title OR p.content LIKE :search_content) 
                AND p.status = 'published' 
                ORDER BY p.created_at DESC 
                LIMIT :offset, :limit");
    $db->bind(':search_title', $searchTerm);
    $db->bind(':search_content', $searchTerm);
    $db->bind(':offset', $offset, PDO::PARAM_INT);
    $db->bind(':limit', $postsPerPage, PDO::PARAM_INT);
    $posts = $db->resultSet();
    
    // Get total number of results for pagination
    $db->query("SELECT COUNT(*) as total 
                FROM posts 
                WHERE (title LIKE :search_title OR content LIKE :search_content) 
                AND status = 'published'");
    $db->bind(':search_title', $searchTerm);
    $db->bind(':search_content', $searchTerm);
    $totalPosts = $db->single()['total'];
    $totalPages = ceil($totalPosts / $postsPerPage);
} else {
    $posts = [];
    $totalPosts = 0;
    $totalPages = 0;
}

// Include header
include 'includes/header.php';
?>

<!-- Search Header -->
<section class="search-header bg-light py-5">
    <div class="container">
        <h1 class="text-center">Search Results</h1>
        
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <form action="search.php" method="get" class="search-form">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" name="q" placeholder="Search for..." value="<?php echo $query; ?>" required>
                        <div class="input-group-append">
                            <button class="btn btn-dark" type="submit">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Search Results -->
<section class="search-results py-5">
    <div class="container">
        <?php if (!empty($query)): ?>
            <div class="mb-4">
                <h2>Results for "<?php echo $query; ?>"</h2>
                <p><?php echo $totalPosts; ?> result<?php echo $totalPosts !== 1 ? 's' : ''; ?> found</p>
            </div>
            
            <?php if (!empty($posts)): ?>
                <div class="row">
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
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">«</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">»</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    No results found for "<?php echo $query; ?>". Please try a different search term.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center">
                <p>Please enter a search term to find posts.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>

