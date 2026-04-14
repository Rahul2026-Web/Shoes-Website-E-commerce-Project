<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

if (isset($_POST['update_status'])) {
    $messageId = (int)$_POST['message_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $updateQuery = "UPDATE contact_messages SET status = '$status' WHERE id = $messageId";
    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['success'] = "Message status updated successfully!";
    }
    redirect('admin/contact-messages.php');
}

if (isset($_GET['delete'])) {
    $messageId = (int)$_GET['delete'];
    $deleteQuery = "DELETE FROM contact_messages WHERE id = $messageId";
    if (mysqli_query($conn, $deleteQuery)) {
        $_SESSION['success'] = "Message deleted successfully!";
    }
    redirect('admin/contact-messages.php');
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$whereClause = '';
if ($filter != 'all') {
    $whereClause = "WHERE status = '$filter'";
}

$messagesQuery = "SELECT * FROM contact_messages $whereClause ORDER BY created_at DESC";
$messagesResult = mysqli_query($conn, $messagesQuery);

$unreadCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'"))['count'];
$readCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'read'"))['count'];
$repliedCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE status = 'replied'"))['count'];
$totalCount = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM contact_messages"));

$pageTitle = "Contact Messages";
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="categories.php">Categories</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="customers.php">Customers</a></li>
            <li><a href="reports.php">Revenue Reports</a></li>
            <li><a href="contact-messages.php" class="active">Contact Messages</a></li>
        </ul>
    </div>

    <div class="admin-content" style="padding-right: 2rem;">
        <h1><i class="fas fa-envelope"></i> Contact Messages</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="contact-messages.php?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
                All (<?php echo $totalCount; ?>)
            </a>
            <a href="contact-messages.php?filter=unread" class="filter-tab <?php echo $filter == 'unread' ? 'active' : ''; ?>">
                <i class="fas fa-circle" style="color: #e74c3c; font-size: 0.6rem;"></i> Unread (<?php echo $unreadCount; ?>)
            </a>
            <a href="contact-messages.php?filter=read" class="filter-tab <?php echo $filter == 'read' ? 'active' : ''; ?>">
                Read (<?php echo $readCount; ?>)
            </a>
            <a href="contact-messages.php?filter=replied" class="filter-tab <?php echo $filter == 'replied' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle" style="color: #27ae60; font-size: 0.8rem;"></i> Replied (<?php echo $repliedCount; ?>)
            </a>
        </div>

        <?php if (mysqli_num_rows($messagesResult) > 0): ?>
            <div class="messages-list">
                <?php while ($msg = mysqli_fetch_assoc($messagesResult)): ?>
                    <div class="message-card <?php echo $msg['status']; ?>">
                        <div class="message-header">
                            <div class="message-info">
                                <h3>
                                    <?php if ($msg['status'] == 'unread'): ?>
                                        <i class="fas fa-circle" style="color: #e74c3c; font-size: 0.6rem;"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($msg['name']); ?>
                                </h3>
                                <p class="message-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($msg['email']); ?></p>
                                <p class="message-date"><i class="fas fa-clock"></i> <?php echo date('F d, Y h:i A', strtotime($msg['created_at'])); ?></p>
                            </div>
                            <div class="message-actions">
                                <span class="status-badge status-<?php echo $msg['status']; ?>">
                                    <?php echo ucfirst($msg['status']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="message-body">
                            <h4><i class="fas fa-tag"></i> <?php echo htmlspecialchars($msg['subject']); ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                        </div>
                        <div class="message-footer">
                            <form method="POST" style="display: inline-block; margin-right: 10px;">
                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="status-select">
                                    <option value="unread" <?php echo $msg['status'] == 'unread' ? 'selected' : ''; ?>>Unread</option>
                                    <option value="read" <?php echo $msg['status'] == 'read' ? 'selected' : ''; ?>>Read</option>
                                    <option value="replied" <?php echo $msg['status'] == 'replied' ? 'selected' : ''; ?>>Replied</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-reply"></i> Reply via Email
                            </a>
                            <a href="contact-messages.php?delete=<?php echo $msg['id']; ?>" class="btn btn-delete btn-sm" onclick="return confirm('Are you sure you want to delete this message?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox" style="font-size: 4rem; color: #bdc3c7; margin-bottom: 1rem;"></i>
                <h3>No messages found</h3>
                <p>There are no <?php echo $filter != 'all' ? $filter : ''; ?> contact messages at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filter-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 0;
}

.filter-tab {
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: #7f8c8d;
    font-weight: 600;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.filter-tab:hover {
    color: #2c3e50;
}

.filter-tab.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.messages-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.message-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s;
    border-left: 4px solid #e0e0e0;
}

.message-card.unread {
    border-left-color: #e74c3c;
    background: #fff5f5;
}

.message-card.read {
    border-left-color: #3498db;
}

.message-card.replied {
    border-left-color: #27ae60;
}

.message-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.message-info h3 {
    margin-bottom: 0.5rem;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.message-email, .message-date {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin: 0.25rem 0;
}

.message-body {
    margin-bottom: 1rem;
}

.message-body h4 {
    color: #34495e;
    margin-bottom: 0.75rem;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.message-body p {
    color: #555;
    line-height: 1.6;
}

.message-footer {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e0e0e0;
}

.status-select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 0.9rem;
    cursor: pointer;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-unread {
    background: #ffe5e5;
    color: #e74c3c;
}

.status-read {
    background: #e3f2fd;
    color: #2196f3;
}

.status-replied {
    background: #e8f5e9;
    color: #4caf50;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 8px;
}

.empty-state h3 {
    color: #7f8c8d;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #95a5a6;
}

@media (max-width: 768px) {
    .message-header {
        flex-direction: column;
        gap: 1rem;
    }

    .message-footer {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-tabs {
        flex-wrap: wrap;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
