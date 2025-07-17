<?php
require_once __DIR__ . '/../../includes/header.php';

if (!has_role('admin')) {
    redirect('/vehicle_marketplace/index.php');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total vehicles
$sql = "SELECT COUNT(*) as total FROM vehicles";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$total_vehicles = $result->fetch_assoc()['total'];
$total_pages = ceil($total_vehicles / $limit);

// Get vehicles with pagination
$sql = "SELECT v.*, u.username 
        FROM vehicles v 
        JOIN users u ON v.user_id = u.id 
        ORDER BY v.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$vehicles = $stmt->get_result();

// Handle vehicle deletion
if (isset($_POST['delete_vehicle'])) {
    $vehicle_id = (int)$_POST['vehicle_id'];
    
    // First, delete images from server and database
    $sql = "SELECT image_path FROM vehicle_images WHERE vehicle_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($image = $result->fetch_assoc()) {
        $file_path = __DIR__ . '/../..' . $image['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Then delete the vehicle
    $sql = "DELETE FROM vehicles WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    
    // Redirect to refresh the page
    redirect('/vehicle_marketplace/admin/vehicles.php');
}
?>

<div class="admin-container">
    <h1>Manage Vehicles</h1>
    
    <div class="admin-actions">
        <a href="/vehicle_marketplace/admin/dashboard.php" class="btn btn-back">Back to Dashboard</a>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Seller</th>
                <th>Make</th>
                <th>Model</th>
                <th>Year</th>
                <th>Price</th>
                <th>Status</th>
                <th>Listed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $vehicle['id']; ?></td>
                    <td><?php echo htmlspecialchars($vehicle['title']); ?></td>
                    <td><?php echo htmlspecialchars($vehicle['username']); ?></td>
                    <td><?php echo htmlspecialchars($vehicle['make']); ?></td>
                    <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                    <td><?php echo $vehicle['year']; ?></td>
                    <td>$<?php echo number_format($vehicle['price'], 2); ?></td>
                    <td><span class="status-badge <?php echo strtolower($vehicle['status']); ?>"><?php echo ucfirst($vehicle['status']); ?></span></td>
                    <td><?php echo date('M j, Y', strtotime($vehicle['created_at'])); ?></td>
                    <td class="actions-cell">
                        <a href="/vehicle_marketplace/vehicles/view.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-view">View</a>
                        <a href="/vehicle_marketplace/vehicles/edit.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-edit">Edit</a>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this vehicle?');">
                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                            <button type="submit" name="delete_vehicle" class="btn btn-delete">Delete</button>
                        </form>
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