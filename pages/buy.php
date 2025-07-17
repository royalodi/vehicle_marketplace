<?php
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">

<!-- Link external CSS files for styling -->
<link rel="stylesheet" href="../../vehicle_marketplace/assets/css/style.css">
<link rel="stylesheet" href="../../vehicle_marketplace/assets/css/responsive.css">

<!-- Link external JavaScript files -->
<!-- Main JavaScript file for site functionality -->
<script src="../../vehicle_marketplace/assets/js/main.js" defer></script>

    <h1>Buy Vehicles</h1>
    
    <div class="search-filters">
        <form method="GET" action="">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="make">Make</label>
                    <select id="make" name="make">
                        <option value="">All Makes</option>
                        <?php
                        $sql = "SELECT DISTINCT make FROM vehicles ORDER BY make";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['make']); ?>" 
                                <?php echo isset($_GET['make']) && $_GET['make'] === $row['make'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['make']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="model">Model</label>
                    <select id="model" name="model">
                        <option value="">All Models</option>
                        <?php
                        if (isset($_GET['make'])) {
                            $make = sanitize($_GET['make']);
                            $sql = "SELECT DISTINCT model FROM vehicles WHERE make = ? ORDER BY model";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $make);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while ($row = $result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($row['model']); ?>" 
                                    <?php echo isset($_GET['model']) && $_GET['model'] === $row['model'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['model']); ?>
                                </option>
                            <?php endwhile;
                        }
                        ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="">All Types</option>
                        <option value="car" <?php echo isset($_GET['type']) && $_GET['type'] === 'car' ? 'selected' : ''; ?>>Car</option>
                        <option value="truck" <?php echo isset($_GET['type']) && $_GET['type'] === 'truck' ? 'selected' : ''; ?>>Truck</option>
                        <option value="motorcycle" <?php echo isset($_GET['type']) && $_GET['type'] === 'motorcycle' ? 'selected' : ''; ?>>Motorcycle</option>
                        <option value="suv" <?php echo isset($_GET['type']) && $_GET['type'] === 'suv' ? 'selected' : ''; ?>>SUV</option>
                        <option value="van" <?php echo isset($_GET['type']) && $_GET['type'] === 'van' ? 'selected' : ''; ?>>Van</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-row">
                <div class="filter-group range-group">
                    <label for="min_price">Price Range</label>
                    <div class="range-inputs">
                        <input type="number" id="min_price" name="min_price" placeholder="Min" 
                               value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                        <span>to</span>
                        <input type="number" id="max_price" name="max_price" placeholder="Max" 
                               value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                    </div>
                </div>
                
                <div class="filter-group range-group">
                    <label for="min_year">Year Range</label>
                    <div class="range-inputs">
                        <input type="number" id="min_year" name="min_year" placeholder="Min" min="1900" 
                               value="<?php echo isset($_GET['min_year']) ? htmlspecialchars($_GET['min_year']) : ''; ?>">
                        <span>to</span>
                        <input type="number" id="max_year" name="max_year" placeholder="Max" max="<?php echo date('Y') + 1; ?>" 
                               value="<?php echo isset($_GET['max_year']) ? htmlspecialchars($_GET['max_year']) : ''; ?>">
                    </div>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="../../vehicle_marketplace/pages/buy.php" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
    
    <?php
    // Build the query based on filters
    $sql = "SELECT v.*, vi.image_path 
            FROM vehicles v 
            LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = TRUE
            WHERE v.status = 'available'";
    
    $params = [];
    $types = '';
    
    if (isset($_GET['make']) && !empty($_GET['make'])) {
        $sql .= " AND v.make = ?";
        $params[] = sanitize($_GET['make']);
        $types .= 's';
    }
    
    if (isset($_GET['model']) && !empty($_GET['model'])) {
        $sql .= " AND v.model = ?";
        $params[] = sanitize($_GET['model']);
        $types .= 's';
    }
    
    if (isset($_GET['type']) && !empty($_GET['type'])) {
        $sql .= " AND v.type = ?";
        $params[] = sanitize($_GET['type']);
        $types .= 's';
    }
    
    if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
        $sql .= " AND v.price >= ?";
        $params[] = (float)$_GET['min_price'];
        $types .= 'd';
    }
    
    if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $sql .= " AND v.price <= ?";
        $params[] = (float)$_GET['max_price'];
        $types .= 'd';
    }
    
    if (isset($_GET['min_year']) && is_numeric($_GET['min_year'])) {
        $sql .= " AND v.year >= ?";
        $params[] = (int)$_GET['min_year'];
        $types .= 'i';
    }
    
    if (isset($_GET['max_year']) && is_numeric($_GET['max_year'])) {
        $sql .= " AND v.year <= ?";
        $params[] = (int)$_GET['max_year'];
        $types .= 'i';
    }
    
    $sql .= " ORDER BY v.created_at DESC";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $vehicles = $stmt->get_result();
    ?>
    
    <div class="vehicle-grid">
        <?php if ($vehicles->num_rows > 0): ?>
            <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                <div class="vehicle-card">
                    <a href="/vehicle_marketplace/vehicles/view.php?id=<?php echo $vehicle['id']; ?>">
                        <div class="vehicle-image">
                            <?php if ($vehicle['image_path']): ?>
                                <img src="<?php echo $vehicle['image_path']; ?>" alt="<?php echo htmlspecialchars($vehicle['title']); ?>">
                            <?php else: ?>
                                <img src="../../vehicle_marketplace/assets/images/no-image.jpg" alt="No image available">
                            <?php endif; ?>
                        </div>
                        <div class="vehicle-info">
                            <h3><?php echo htmlspecialchars($vehicle['title']); ?></h3>
                            <p class="price">$<?php echo number_format($vehicle['price'], 2); ?></p>
                            <p><?php echo $vehicle['year']; ?> <?php echo htmlspecialchars($vehicle['make']); ?> <?php echo htmlspecialchars($vehicle['model']); ?></p>
                            <p><?php echo number_format($vehicle['mileage']); ?> miles</p>
                            <p><?php echo ucfirst($vehicle['transmission']); ?> transmission</p>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results">
                <p>No vehicles found matching your criteria.</p>
                <a href="../../vehicle_marketplace/pages/buy.php" class="btn btn-primary">Reset Filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Dynamic model dropdown based on make selection
document.getElementById('make').addEventListener('change', function() {
    const make = this.value;
    const modelSelect = document.getElementById('model');
    
    if (make) {
        // Fetch models for the selected make via AJAX
        fetch(`/vehicle_marketplace/includes/get_models.php?make=${encodeURIComponent(make)}`)
            .then(response => response.json())
            .then(models => {
                modelSelect.innerHTML = '<option value="">All Models</option>';
                models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model;
                    option.textContent = model;
                    modelSelect.appendChild(option);
                });
            });
    } else {
        modelSelect.innerHTML = '<option value="">All Models</option>';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>