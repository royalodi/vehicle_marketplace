<?php
require_once __DIR__ . '/../../includes/header.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$vehicles = get_vehicles($limit, $offset);

// Get total count for pagination
$sql = "SELECT COUNT(*) as total FROM vehicles WHERE status = 'available'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$total_vehicles = $result->fetch_assoc()['total'];
$total_pages = ceil($total_vehicles / $limit);
?>

<div class="container">
    <h1>Available Vehicles</h1>
    <div class="vehicle-grid">
        <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
            <div class="vehicle-card">
                <a href="/vehicle_marketplace/vehicles/view.php?id=<?php echo $vehicle['id']; ?>">
                    <div class="vehicle-image">
                        <?php if ($vehicle['image_path']): ?>
                            <img src="/vehicle_marketplace/assets/images/uploads/vehicles/<?php echo basename($vehicle['image_path']); ?>" alt="<?php echo $vehicle['title']; ?>">
                        <?php else: ?>
                            <img src="/vehicle_marketplace/assets/images/no-image.jpg" alt="No image available">
                        <?php endif; ?>
                    </div>
                    <div class="vehicle-info">
                        <h3><?php echo $vehicle['title']; ?></h3>
                        <p class="price">$<?php echo number_format($vehicle['price'], 2); ?></p>
                        <p><?php echo $vehicle['year']; ?> <?php echo $vehicle['make']; ?> <?php echo $vehicle['model']; ?></p>
                        <p><?php echo number_format($vehicle['mileage']); ?> miles</p>
                        <p><?php echo ucfirst($vehicle['transmission']); ?> transmission</p>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
    
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