<?php
/*
* File: /vintage-photo-blog/admin/comments.php
* Date: 2023-11-09
* Name: Programmed by Jaime C Smith
* 
* This file handles comment management for administrators.
* It allows admins to approve, reject, and delete comments.
*/

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

$pageTitle = "Comment Management";

// Handle comment actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $commentId = (int)$_GET['id'];
    $action = $_GET['action'];
    
    $db = new Database();
    
    switch ($action) {
        case 'approve':
            $db->query("UPDATE comments SET status = 'approved' WHERE id = :id");
            $db->bind(':id', $commentId);
            $db->execute();
            redirect('comments.php?status=pending&message=Comment approved');
            break;
            
        case 'reject':
            $db->query("UPDATE comments SET status = 'spam' WHERE id = :id");
            $db->bind(':id', $commentId);
            $db->execute();
            redirect('comments.php?status=pending&message=Comment rejected');
            break;
            
        case 'delete':
            $db->query("DELETE FROM comments WHERE id = :id");
            $db->bind(':id', $commentId);
            $db->execute();
            redirect('comments.php?status=' . ($_GET['status'] ?? 'all') . '&message=Comment deleted');
            break;
    }
}

// Get filter status
$status = isset($_GET['status']) ? clean($_GET['status']) : 'all';

// Pagination setup
$commentsPerPage = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $commentsPerPage;

// Get comments based on status filter
$db = new Database();

if ($status === 'all') {
    $db->query("SELECT c.*, p.title as post_title, p.slug as post_slug, u.username, u.first_name, u.last_name 
                FROM comments c 
                JOIN posts p ON c.post_id = p.id 
                JOIN users u ON c.user_id = u.id 
                ORDER BY c.created_at DESC 
                LIMIT :offset, :limit");
} else {
    $db->query("SELECT c.*, p.title as post_title, p.slug as post_slug, u.username, u.first_name, u.last_name 
                FROM comments c 
                JOIN posts p ON c.post_id = p.id 
                JOIN users u ON c.user_id = u.id 
                WHERE c.status = :status 
                ORDER BY c.created_at DESC 
                LIMIT :offset, :limit");
    $db->bind(':status', $status);
}

$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $commentsPerPage, PDO::PARAM_INT);
$comments = $db->resultSet();

// Get total number of comments for pagination
if ($status === 'all') {
    $db->query("SELECT COUNT(*) as total FROM comments");
} else {
    $db->query("SELECT COUNT(*) as total FROM comments WHERE status = :status");
    $db->bind(':status', $status);
}
$totalComments = $db->single()['total'];
$totalPages = ceil($totalComments / $commentsPerPage);

// Include header
include '../includes/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Comment Management</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <div class="btn-group" role="group">
                <a href="comments.php?status=all" class="btn btn-sm <?php echo $status === 'all' ? 'btn-dark' : 'btn-outline-dark'; ?>">
                    All Comments
                </a>
                <a href="comments.php?status=pending" class="btn btn-sm <?php echo $status === 'pending' ? 'btn-dark' : 'btn-outline-dark'; ?>">
                    Pending
                </a>
                <a href="comments.php?status=approved" class="btn btn-sm <?php echo $status === 'approved' ? 'btn-dark' : 'btn-outline-dark'; ?>">
                    Approved
                </a>
                <a href="comments.php?status=spam" class="btn btn-sm <?php echo $status === 'spam' ? 'btn-dark' : 'btn-outline-dark'; ?>">
                    Spam
                </a>
            </div>
        </div>
    </div>
    
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success">
            <?php echo $_GET['message']; ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($comments)): ?>
        <div class="alert alert-info">
            No <?php echo $status !== 'all' ? $status : ''; ?> comments found.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Author</th>
                        <th>Comment</th>
                        <th>Post</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td>
                                <?php echo !empty($comment['first_name']) ? $comment['first_name'] . ' ' . $comment['last_name'] : $comment['username']; ?>
                            </td>
                            <td>
                                <?php echo truncateText(htmlspecialchars($comment['content']), 100); ?>
                                <?php if ($comment['parent_id']): ?>
                                    <span class="badge badge-light">Reply</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/post.php?slug=<?php echo $comment['post_slug']; ?>#comment-<?php echo $comment['id']; ?>" target="_blank">
                                    <?php echo truncateText($comment['post_title'], 40); ?>
                                </a>
                            </td>
                            <td><?php echo formatDate($comment['created_at']); ?></td>
                            <td>
                                <?php if ($comment['status'] === 'approved'): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php elseif ($comment['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php elseif ($comment['status'] === 'spam'): ?>
                                    <span class="badge badge-danger">Spam</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($comment['status'] === 'pending'): ?>
                                    <a href="comments.php?action=approve&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="comments.php?action=reject&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-warning" title="Mark as Spam">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($comment['status'] === 'spam'): ?>
                                    <a href="comments.php?action=approve&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($comment['status'] === 'approved'): ?>
                                    <a href="comments.php?action=reject&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-warning" title="Mark as Spam">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="comments.php?action=delete&id=<?php echo $comment['id']; ?>&status=<?php echo $status; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this comment?');">
                                    <i class="fas fa-trash"></i>
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
                            <a class="page-link" href="comments.php?status=<?php echo $status; ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">«</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="comments.php?status=<?php echo $status; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="comments.php?status=<?php echo $status; ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
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
