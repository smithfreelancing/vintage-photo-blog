<?php
/*
* File: /vintage-photo-blog/categories.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file displays all available categories.
* It shows a grid of categories with descriptions and post counts.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "Categories";

// Get all categories with post counts
$db = new Database();
$db->query("SELECT c.*, COUNT(pc.post_id) as post_count 
            FROM categories c 
            LEFT JOIN post_categories pc ON c.id = pc.category_id 
            LEFT JOIN posts p ON pc.post_id = p.id AND p.status = 'published' 
            GROUP BY c.id 
            ORDER BY c.name");
$categories = $db->resultSet();

// Include header
include 'includes/header.php';
?>

<!-- Categories Header -->
<section class="categories-header bg-light py-5">
    <div class="container">
        <h1 class="text-center">Photography Categories</h1>
        <p class="text-center mb-0">Explore our collection of vintage photography by category.</p>
    </div>
</section>

<!-- Categories Grid -->
<section class="categories-grid py-5">
    <div class="container">
        <div class="row">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h3 class="card-title"><?php echo $category['name']; ?></h3>
                                <p class="card-text"><?php echo $category['description']; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge badge-light"><?php echo $category['post_count']; ?> posts</span>
                                    <a href="category.php?slug=<?php echo $category['slug']; ?>" class="btn btn-sm btn-outline-dark">View Category</a>
                                </div>
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

<?php
// Include footer
include 'includes/footer.php';
?>
