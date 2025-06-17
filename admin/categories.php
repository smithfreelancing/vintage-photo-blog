<?php
/*
* File: /vintage-photo-blog/admin/categories.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles category management for administrators.
* It allows admins to create, edit, and delete categories.
*/

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = "Manage Categories";

// Initialize variables
$name = $description = '';
$errors = [];
$success = false;
$editMode = false;
$categoryId = 0;

$db = new Database();

// Handle category deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $categoryId = (int)$_GET['id'];
    
    // Check if category has posts
    $db->query("SELECT COUNT(*) as count FROM post_categories WHERE category_id = :id");
    $db->bind(':id', $categoryId);
    $postCount = $db->single()['count'];
    
    if ($postCount > 0) {
        redirect('categories.php?error=Cannot delete category with associated posts');
    } else {
        $db->query("DELETE FROM categories WHERE id = :id");
        $db->bind(':id', $categoryId);
        
        if ($db->execute()) {
            redirect('categories.php?message=Category deleted successfully');
        } else {
            redirect('categories.php?error=Failed to delete category');
        }
    }
}

// Handle edit mode
if (isset($_GET['edit']) && isset($_GET['id'])) {
    $categoryId = (int)$_GET['id'];
    
    $db->query("SELECT * FROM categories WHERE id = :id");
    $db->bind(':id', $categoryId);
    $category = $db->single();
    
    if ($category) {
        $editMode = true;
        $name = $category['name'];
        $description = $category['description'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    // Validate form data
    if (empty($name)) {
        $errors[] = "Category name is required";
    }
    
    // If no errors, create or update category
    if (empty($errors)) {
        $slug = createSlug($name);
        
        if ($categoryId > 0) {
            // Update existing category
            $db->query("UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id");
            $db->bind(':name', $name);
            $db->bind(':slug', $slug);
            $db->bind(':description', $description);
            $db->bind(':id', $categoryId);
            
            if ($db->execute()) {
                $success = true;
                $message = "Category updated successfully";
            } else {
                $errors[] = "Failed to update category";
            }
        } else {
            // Check if category name already exists
            $db->query("SELECT id FROM categories WHERE name = :name");
            $db->bind(':name', $name);
            $existingCategory = $db->single();
            
            if ($existingCategory) {
                $errors[] = "Category name already exists";
            } else {
                // Create new category
                $db->query("INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)");
                $db->bind(':name', $name);
                $db->bind(':slug', $slug);
                $db->bind(':description', $description);
                
                if ($db->execute()) {
                    $success = true;
                    $message = "Category created successfully";
                    $name = $description = '';
                } else {
                    $errors[] = "Failed to create category";
                }
            }
        }
    }
}

// Get all categories
$db->query("SELECT c.*, COUNT(pc.post_id) as post_count 
            FROM categories c 
            LEFT JOIN post_categories pc ON c.id = pc.category_id 
            GROUP BY c.id 
            ORDER BY c.name");
$categories = $db->resultSet();

// Include header
include '../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Manage Categories</h1>
    
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
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $editMode ? 'Edit Category' : 'Add New Category'; ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="categories.php<?php echo $editMode ? '?edit=1&id=' . $categoryId : ''; ?>" method="post">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo $description; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-dark"><?php echo $editMode ? 'Update Category' : 'Add Category'; ?></button>
                            <?php if ($editMode): ?>
                                <a href="categories.php" class="btn btn-light">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Categories</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($categories)): ?>
                        <div class="p-3">No categories found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Posts</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['name']; ?></td>
                                            <td><?php echo $category['slug']; ?></td>
                                            <td><?php echo truncateText($category['description'], 50); ?></td>
                                            <td><?php echo $category['post_count']; ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="categories.php?edit=1&id=<?php echo $category['id']; ?>" class="btn btn-outline-dark" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($category['post_count'] == 0): ?>
                                                        <a href="categories.php?delete=1&id=<?php echo $category['id']; ?>" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this category?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-outline-danger" title="Cannot delete category with posts" disabled>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
