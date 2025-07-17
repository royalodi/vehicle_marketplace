<?php
require_once __DIR__ . '../includes/header.php';

// Get featured vehicles (most recent 6 available vehicles)
$vehicles = get_vehicles(6);
?>

<div class="hero">
    <div class="hero-content">
        <h1>Find Your Perfect Vehicle</h1>
        <p>Browse thousands of vehicles from trusted sellers across the country</p>
        <div class="hero-buttons">
            <a href="../vehicle_marketplace/pages/buy.php" class="btn btn-primary">Browse Vehicles</a>
            <?php if (is_logged_in()): ?>
                <a href="../vehicle_marketplace/vehicles/add.php" class="btn btn-secondary">Sell Your Vehicle</a>
            <?php else: ?>
                <a href="../vehicle_marketplace/auth/register.php" class="btn btn-secondary">Join Now</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <section class="featured-vehicles">
        <h2>Featured Vehicles</h2>
        <div class="vehicle-grid">
            <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                <div class="vehicle-card">
                    <a href="../vehicle_marketplace/vehicles/view.php?id=<?php echo $vehicle['id']; ?>">
                        <div class="vehicle-image">
                            <?php if ($vehicle['image_path']): ?>
                                <img src="<?php echo $vehicle['image_path']; ?>" alt="<?php echo htmlspecialchars($vehicle['title']); ?>">
                            <?php else: ?>
                                <img src="../vehicle_marketplace/assets/images/no-image.jpg" alt="No image available">
                            <?php endif; ?>
                        </div>
                        <div class="vehicle-info">
                            <h3><?php echo htmlspecialchars($vehicle['title']); ?></h3>
                            <p class="price">$<?php echo number_format($vehicle['price'], 2); ?></p>
                            <p><?php echo $vehicle['year']; ?> <?php echo htmlspecialchars($vehicle['make']); ?> <?php echo htmlspecialchars($vehicle['model']); ?></p>
                            <p><?php echo number_format($vehicle['mileage']); ?> miles</p>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="view-all">
            <a href="../vehicle_marketplace/pages/buy.php" class="btn btn-primary">View All Vehicles</a>
        </div>
    </section>
    
    <section class="why-choose-us">
        <h2>Why Choose Vehicle Marketplace?</h2>
        <div class="features-grid">
            <div class="feature">
                <div class="feature-icon">
                    <img src="../vehicle_marketplace/assets/images/icons/trust.png" alt="Trusted Sellers">
                </div>
                <h3>Trusted Sellers</h3>
                <p>All sellers are verified to ensure you're dealing with real people and legitimate vehicles.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <img src="../vehicle_marketplace/assets/images/icons/selection.png" alt="Wide Selection">
                </div>
                <h3>Wide Selection</h3>
                <p>Find everything from economy cars to luxury vehicles, trucks, and motorcycles.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <img src="../vehicle_marketplace/assets/images/icons/secure.png" alt="Secure Transactions">
                </div>
                <h3>Secure Transactions</h3>
                <p>Our platform ensures safe and secure transactions between buyers and sellers.</p>
            </div>
        </div>
    </section>
    
    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        <div class="testimonial-grid">
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"Found my dream car at a great price! The seller was very professional and the process was smooth."</p>
                </div>
                <div class="testimonial-author">
                    <p>- Michael R.</p>
                </div>
            </div>
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"Sold my truck in just 3 days. The listing process was easy and I got multiple offers quickly."</p>
                </div>
                <div class="testimonial-author">
                    <p>- Sarah K.</p>
                </div>
            </div>
            <div class="testimonial">
                <div class="testimonial-content">
                    <p>"Great platform for both buyers and sellers. I've used it twice now with excellent results."</p>
                </div>
                <div class="testimonial-author">
                    <p>- David L.</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '../includes/footer.php'; ?>