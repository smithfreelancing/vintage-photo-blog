<?php
/*
* File: /vintage-photo-blog/create_post.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles the creation of new blog posts.
* It displays a form for users to create posts and processes form submissions.
*/

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = "Create Post";

// Initialize variables
$title = $content = '';
$selectedCategories = [];
$errors = [];
$success = false;

// Get all categories
$db = new Database();
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
    $featuredImage = '';
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadedImage = uploadImage($_FILES['featured_image'], 'posts/');
        if ($uploadedImage) {
            $featuredImage = $uploadedImage;
        } else {
            $errors[] = "Failed to upload featured image. Please ensure it's a valid image file (JPG, PNG, GIF) and under 5MB.";
        }
    }
    
    // If no errors, create the post
    if (empty($errors)) {
        // Generate slug from title
        $slug = createSlug($title);
        
        // Check if slug already exists
        $db->query("SELECT id FROM posts WHERE slug = :slug");
        $db->bind(':slug', $slug);
        $existingPost = $db->single();
        
        if ($existingPost) {
            // Append a unique identifier to make the slug unique
            $slug = $slug . '-' . uniqid();
        }
        
        // Begin transaction
        $db->beginTransaction();
        
        try {
            // Insert post
            $db->query("INSERT INTO posts (user_id, title, slug, content, featured_image, status) 
                        VALUES (:user_id, :title, :slug, :content, :featured_image, :status)");
            $db->bind(':user_id', $_SESSION['user_id']);
            $db->bind(':title', $title);
            $db->bind(':slug', $slug);
            $db->bind(':content', $content);
            $db->bind(':featured_image', $featuredImage);
            $db->bind(':status', $status);
            $db->execute();
            
            // Get the post ID
            $postId = $db->lastInsertId();
            
            // Insert post categories
            foreach ($selectedCategories as $categoryId) {
                $db->query("INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)");
                $db->bind(':post_id', $postId);
                $db->bind(':category_id', $categoryId);
                $db->execute();
            }
            
            // Commit transaction
            $db->endTransaction();
            
            $success = true;
            
            // Clear form data after successful submission
            $title = $content = '';
            $selectedCategories = [];
            
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

<!-- Create Post Header -->
<section class="create-post-header bg-light py-5">
    <div class="container">
        <h1 class="text-center">Create New Post</h1>
    </div>
</section>

<!-- Create Post Form -->
<section class="create-post-form py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Post Created!</h4>
                        <p>Your post has been created successfully.</p>
                        <hr>
                        <p class="mb-0">
                            <a href="index.php" class="alert-link">Go to homepage</a> or 
                            <a href="create_post.php" class="alert-link">create another post</a>.
                        </p>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form action="create_post.php" method="post" enctype="multipart/form-data" id="postForm">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $title; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="15"><?php echo $content; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="featured_image">Featured Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="featured_image" name="featured_image" accept="image/*">
                                <label class="custom-file-label" for="featured_image">Choose file</label>
                            </div>
                            <small class="form-text text-muted">Recommended size: 1200x800 pixels. Max file size: 5MB.</small>
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
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-dark px-5" id="submitBtn">Create Post</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include 'includes/footer.php';
?>

<script src="https://cdn.tiny.cloud/1/nqs96jzocf12z8akkj9ddk3lxuk6nfvexdv11mxn8wilgre9/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    // Initialize TinyMCE
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
        // Ensure content is properly saved before form submission
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        }
    });
    
    // Show filename when file is selected
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    // Form submission handling
    document.getElementById('postForm').addEventListener('submit', function(e) {
        // Make sure TinyMCE content is updated
        if (tinymce.get('content')) {
            tinymce.get('content').save();
        }
        
        // Validate categories
        var categories = document.querySelectorAll('input[name="categories[]"]:checked');
        if (categories.length === 0) {
            e.preventDefault();
            alert('Please select at least one category.');
            return false;
        }
        
        // Disable submit button to prevent double submission
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = 'Creating Post...';
    });
</script>

