<?php
require_once __DIR__ . '/../../includes/header.php';

if (!has_role('admin')) {
    redirect('/vehicle_marketplace/index.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total users
$sql = "SELECT COUNT(*) as total FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$total_users = $result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Get users with pagination
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$users = $stmt->get_result();

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Don't allow deleting yourself
    if ($user_id !== $_SESSION['user_id']) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Redirect to refresh the page
        redirect('/vehicle_marketplace/admin/users.php');
    }
}

// Handle role update
if (isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = sanitize($_POST['new_role']);
    
    $sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_role, $user_id);
    $stmt->execute();
    
    // Redirect to refresh the page
    redirect('/vehicle_marketplace/admin/users.php');
}
?>

<div class="admin-container">
    <h1>Manage Users</h1>
    
    <div class="admin-actions">
        <a href="/vehicle_marketplace/admin/dashboard.php" class="btn btn-back">Back to Dashboard</a>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Location</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                    <td><?php echo htmlspecialchars($user['location']); ?></td>
                    <td>
                        <form method="POST" class="role-form">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <select name="new_role" onchange="this.form.submit()">
                                <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <input type="hidden" name="update_role" value="1">
                        </form>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-delete">Delete</button>
                            </form>
                        <?php else: ?>
                            <span class="current-user">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="page-link">&laquo; Previous</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="page-link">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>