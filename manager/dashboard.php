<?php
require_once __DIR__ . '/../../includes/header.php';

if (!has_role('manager')) {
    redirect('/vehicle_marketplace/index.php');
}

// Get stats for dashboard
$sql = "SELECT COUNT(*) as total_vehicles FROM vehicles";
$total_vehicles = $conn->query($sql)->fetch_assoc()['total_vehicles'];

$sql = "SELECT COUNT(*) as available_vehicles FROM vehicles WHERE status = 'available'";
$available_vehicles = $conn->query($sql)->fetch_assoc()['available_vehicles'];

$sql = "SELECT COUNT(*) as sold_vehicles FROM vehicles WHERE status = 'sold'";
$sold_vehicles = $conn->query($sql)->fetch_assoc()['sold_vehicles'];

// Get recent vehicles
$sql = "SELECT v.*, u.username 
        FROM vehicles v 
        JOIN users u ON v.user_id = u.id 
        ORDER BY v.created_at DESC LIMIT 5";
$recent_vehicles = $conn->query($sql);
?>

<div class="admin-dashboard">
    <h1>Manager Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Vehicles</h3>
            <p><?php echo $total_vehicles; ?></p>
        </div>
        <div class="stat-card">
            <h3>Available Vehicles</h3>
            <p><?php echo $available_vehicles; ?></p>
        </div>
        <div class="stat-card">
            <h3>Sold Vehicles</h3>
            <p><?php echo $sold_vehicles; ?></p>
        </div>
    </div>
    
    <div class="recent-activity">
        <div class="recent-section">
            <h2>Recent Vehicles</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Seller</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Listed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($vehicle = $recent_vehicles->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $vehicle['id']; ?></td>
                            <td><?php echo htmlspecialchars($vehicle['title']); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['username']); ?></td>
                            <td>$<?php echo number_format($vehicle['price'], 2); ?></td>
                            <td><span class="status-badge <?php echo strtolower($vehicle['status']); ?>"><?php echo ucfirst($vehicle['status']); ?></span></td>
                            <td><?php echo date('M j, Y', strtotime($vehicle['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="/vehicle_marketplace/manager/vehicles.php" class="btn btn-view-all">View All Vehicles</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>