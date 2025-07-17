<?php
require_once __DIR__ . '../../includes/header.php';

$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$vehicle = get_vehicle($vehicle_id);

if (!$vehicle) {
    redirect('/vehicle_marketplace/vehicles/list.php');
}

// Check if current user is the owner or admin/manager
$is_owner = is_logged_in() && ($_SESSION['user_id'] == $vehicle['user_id']);
$is_admin_or_manager = has_role('admin') || has_role('manager');
?>

<div class="container vehicle-details">

<!-- Link external CSS files for styling -->
<link rel="stylesheet" href="../../vehicle_marketplace/assets/css/style.css">
<link rel="stylesheet" href="../../vehicle_marketplace/assets/css/responsive.css">

<!-- Link external JavaScript files -->
<!-- Main JavaScript file for site functionality -->
<script src="../../vehicle_marketplace/assets/js/main.js" defer></script>

    <div class="vehicle-header">
        <h1><?php echo htmlspecialchars($vehicle['title']); ?></h1>
        <p class="price">$<?php echo number_format($vehicle['price'], 2); ?></p>
    </div>
    
    <div class="vehicle-gallery">
        <?php if (!empty($vehicle['images'])): ?>
            <div class="primary-image">
                <?php 
                $primary_image = array_filter($vehicle['images'], function($img) {
                    return $img['is_primary'];
                });
                $primary_image = reset($primary_image);
                ?>
                <img src="<?php echo $primary_image ? $primary_image['image_path'] : $vehicle['images'][0]['image_path']; ?>" alt="<?php echo htmlspecialchars($vehicle['title']); ?>">
            </div>
            <div class="thumbnail-container">
                <?php foreach ($vehicle['images'] as $image): ?>
                    <div class="thumbnail <?php echo $image['is_primary'] ? 'active' : ''; ?>">
                        <img src="<?php echo $image['image_path']; ?>" alt="Vehicle Thumbnail">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-image">
                <img src="/vehicle_marketplace/assets/images/no-image.jpg" alt="No image available">
            </div>
        <?php endif; ?>
    </div>
    
    <div class="vehicle-info">
        <div class="vehicle-specs">
            <h2>Vehicle Specifications</h2>
            <div class="specs-grid">
                <div class="spec-item">
                    <span class="spec-label">Make:</span>
                    <span class="spec-value"><?php echo htmlspecialchars($vehicle['make']); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Model:</span>
                    <span class="spec-value"><?php echo htmlspecialchars($vehicle['model']); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Year:</span>
                    <span class="spec-value"><?php echo $vehicle['year']; ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Type:</span>
                    <span class="spec-value"><?php echo ucfirst($vehicle['type']); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Condition:</span>
                    <span class="spec-value"><?php echo ucfirst($vehicle['conditions']); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Mileage:</span>
                    <span class="spec-value"><?php echo number_format($vehicle['mileage']); ?> miles</span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Transmission:</span>
                    <span class="spec-value"><?php echo ucfirst($vehicle['transmission']); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Fuel Type:</span>
                    <span class="spec-value"><?php echo ucfirst($vehicle['fuel_type']); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Location:</span>
                    <span class="spec-value"><?php echo htmlspecialchars($vehicle['location']); ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Status:</span>
                    <span class="spec-value <?php echo strtolower($vehicle['status']); ?>"><?php echo ucfirst($vehicle['status']); ?></span>
                </div>
            </div>
        </div>
        
        <div class="vehicle-description">
            <h2>Description</h2>
            <p><?php echo nl2br(htmlspecialchars($vehicle['description'] ?: 'No description provided.')); ?></p>
        </div>
        
        <div class="seller-info">
            <h2>Seller Information</h2>
            <div class="seller-details">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($vehicle['contact_name']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($vehicle['contact_phone']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($vehicle['location']); ?></p>
            </div>
            <button class="btn btn-contact">Contact Seller</button>
        </div>
    </div>
    
    <div class="vehicle-actions">
        <?php if ($is_owner || $is_admin_or_manager): ?>
            <a href="/vehicle_marketplace/vehicles/edit.php?id=<?php echo $vehicle_id; ?>" class="btn btn-edit">Edit Listing</a>
        <?php endif; ?>
        <?php if ($is_admin_or_manager): ?>
            <form method="POST" action="/vehicle_marketplace/vehicles/delete.php" class="delete-form">
                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">
                <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this vehicle?')">Delete Listing</button>
            </form>
        <?php endif; ?>
        <a href="/vehicle_marketplace/vehicles/list.php" class="btn btn-back">Back to Listings</a>
    </div>
</div>

<script>
// Image gallery functionality
document.addEventListener('DOMContentLoaded', function() {
    const thumbnails = document.querySelectorAll('.thumbnail');
    const primaryImage = document.querySelector('.primary-image img');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Update active thumbnail
            document.querySelector('.thumbnail.active')?.classList.remove('active');
            this.classList.add('active');
            
            // Update primary image
            const imgSrc = this.querySelector('img').src;
            primaryImage.src = imgSrc;
        });
    });
});
</script>

<?php require_once __DIR__ . '../../includes/footer.php'; ?>