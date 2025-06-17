<?php
/*
* File: /vintage-photo-blog/edit_post.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles the editing of existing blog posts.
* It displays a form with the post's current data and processes form submissions.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if post ID is provided
if (!isset($_GET['id'])) {
    redirect('my_posts.php');
}

$postId = (int)$_GET['id'];

// Get post information
$db = new Database();
$db->query("SELECT * FROM posts WHERE id = :id");
$db->bind(':id', $postId);
$post = $db->single();

// If post doesn't exist, redirect
if (!$post) {
    redirect('my_posts.php');
}

// Check if user is the author or an admin
if ($post['user_id'] != $_SESSION['user_id'] && !isAdmin()) {
    redirect('my_posts.php');
}

$pageTitle = "Edit Post";

// Initialize variables
$title = $post['title'];
$content = $post['content'];
$status = $post['status'];
$featuredImage = $post['featured_image'];
$errors = [];
$success = false;

// Get post categories
$db->query("SELECT category_id FROM post_categories WHERE post_id = :post_id");
$db->bind(':post_id', $postId);
$postCategories = $db->resultSet();
$selectedCategories = array_map(function($item) {
    return $item['category_id'];
}, $postCategories);

// Get all categories
$db->query("SELECT * FROM categories ORDER BY name");
$categories = $db->resultSet();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = clean($_POST['title']);
    $content = $_POST['content']; // Don't clean HTML content
    $selectedCategories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $status = clean($_POST['status']);
    
    // Validate form data
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($content)) {
        $errors[] = "Content is required";
    }
    
    if (empty($selectedCategories)) {
        $errors[] = "Please select at least one category";
    }
    
    // Handle featured image upload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadedImage = uploadImage($_FILES['featured_image'], 'posts/');
        if ($uploadedImage) {
            $featuredImage = $uploadedImage;
        } else {
            $errors[] = "Failed to upload featured image. Please ensure it's a valid image file (JPG, PNG, GIF) and under 5MB.";
        }
    }
    
    // If no errors, update the post
    if (empty($errors)) {
        // Generate slug from title if title has changed
        if ($title !== $post['title']) {
            $slug = createSlug($title);
            
            // Check if slug already exists for other posts
            $db->query("SELECT id FROM posts WHERE slug = :slug AND id != :id");
            $db->bind(':slug', $slug);
            $db->bind(':id', $postId);
            $existingPost = $db->single();
            
            if ($existingPost) {
                // Append a unique identifier to make the slug unique
                $slug = $slug . '-' . uniqid();
            }
        } else {
            $slug = $post['slug'];
        }
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Update post
            $db->query("UPDATE posts SET title = :title, slug = :slug, content = :content, 
                        featured_image = :featured_image, status = :status, updated_at = NOW() 
                        WHERE id = :id");
            $db->bind(':title', $title);
            $db->bind(':slug', $slug);
            $db->bind(':content', $content);
            $db->bind(':featured_image', $featuredImage);
            $db->bind(':status', $status);
            $db->bind(':id', $postId);
            $db->execute();
            
            // Delete existing post categories
            $db->query("DELETE FROM post_categories WHERE post_id = :post_id");
            $db->bind(':post_id', $postId);
            $db->execute();
            
            // Insert new post categories
            foreach ($selectedCategories as $categoryId) {
                $db->query("INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)");
                $db->bind(':post_id', $postId);
                $db->bind(':category_id', $categoryId);
                $db->execute();
            }
            
            // Commit transaction
            $db->endTransaction();
            
            $success = true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->cancelTransaction();
            $errors[] = "An error occurred: " . $e->getMessage();
        }
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Edit Post Header -->
<section class="edit-post-header bg-light py-5">
    <div class="container">
        <h1 class="text-center">Edit Post</h1>
    </div>
</section>

<!-- Edit Post Form -->
<section class="edit-post-form py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Post Updated!</h4>
                        <p>Your post has been updated successfully.</p>
                        <hr>
                        <p class="mb-0">
                            <a href="post.php?slug=<?php echo $slug; ?>" class="alert-link">View post</a> or 
                            <a href="my_posts.php" class="alert-link">go to my posts</a>.
                        </p>
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
                
                <form action="edit_post.php?id=<?php echo $postId; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $title; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="15" required><?php echo $content; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="featured_image">Featured Image</label>
                        <?php if (!empty($featuredImage)): ?>
                            <div class="mb-2">
                                <img src="<?php echo UPLOAD_URL . $featuredImage; ?>" alt="Featured Image" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        <?php endif; ?>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="featured_image" name="featured_image">
                            <label class="custom-file-label" for="featured_image">Choose new file</label>
                        </div>
                        <small class="form-text text-muted">Leave empty to keep the current image. Recommended size: 1200x800 pixels. Max file size: 5MB.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Categories</label>
                        <div class="row">
                            <?php foreach ($categories as $category): ?>
                                <div class="col-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="category-<?php echo $category['id']; ?>" name="categories[]" value="<?php echo $category['id']; ?>" <?php echo in_array($category['id'], $selectedCategories) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="category-<?php echo $category['id']; ?>"><?php echo $category['name']; ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="my_posts.php" class="btn btn-light mr-2">Cancel</a>
                        <button type="submit" class="btn btn-dark px-5">Update Post</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>



<?php
// Rest of the edit_post.php file remains the same
?>

<script src="https://cdn.tiny.cloud/1/nqs96jzocf12z8akkj9ddk3lxuk6nfvexdv11mxn8wilgre9/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#content',
        height: 500,
        menubar: true,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | link image | help',
        content_style: 'body { font-family: "Helvetica Neue", Arial, sans-serif; font-size: 16px; line-height: 1.6; }',
        image_advtab: true,
        image_caption: true,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true
    });
    
    // Show filename when file is selected
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });
</script>


