<?php
require_once __DIR__ . '../../includes/header.php';

if (!is_logged_in()) {
    redirect('/vehicle_marketplace/auth/login.php');
}

$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$vehicle = get_vehicle($vehicle_id);

// Check if vehicle exists and user has permission to edit
if (!$vehicle || ($vehicle['user_id'] !== $_SESSION['user_id'] && !has_role('admin') && !has_role('manager'))) {
    redirect('/vehicle_marketplace/vehicles/list.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = sanitize($_POST['title']);
    $type = sanitize($_POST['type']);
    $condition = sanitize($_POST['condition']);
    $make = sanitize($_POST['make']);
    $model = sanitize($_POST['model']);
    $year = sanitize($_POST['year']);
    $price = sanitize($_POST['price']);
    $transmission = sanitize($_POST['transmission']);
    $fuel_type = sanitize($_POST['fuel_type']);
    $mileage = sanitize($_POST['mileage']);
    $description = sanitize($_POST['description']);
    $location = sanitize($_POST['location']);
    $contact_name = sanitize($_POST['contact_name']);
    $contact_phone = sanitize($_POST['contact_phone']);
    $status = sanitize($_POST['status']);
    
    // Validate inputs
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($make)) $errors[] = 'Make is required';
    if (empty($model)) $errors[] = 'Model is required';
    if (empty($year) || !is_numeric($year)) $errors[] = 'Valid year is required';
    if (empty($price) || !is_numeric($price)) $errors[] = 'Valid price is required';
    if (empty($mileage) || !is_numeric($mileage)) $errors[] = 'Valid mileage is required';
    if (empty($contact_name)) $errors[] = 'Contact name is required';
    if (empty($contact_phone)) $errors[] = 'Contact phone is required';
    
    // Process new images
    $new_images = [];
    $primary_image_set = false;
    
    // Check if existing primary image is still selected
    if (isset($_POST['existing_primary'])) {
        $primary_image_set = true;
    }
    
    // Check for new primary image
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_FILES["new_image_$i"]['name'])) {
            $file = $_FILES["new_image_$i"];
            $is_primary = isset($_POST["primary_image"]) && $_POST["primary_image"] == "new_$i";
            
            // Validate image
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = "New image $i must be a JPEG, PNG, or GIF";
            } elseif ($file['size'] > $max_size) {
                $errors[] = "New image $i must be less than 5MB";
            } else {
                $new_images[] = [
                    'file' => $file,
                    'is_primary' => $is_primary
                ];
                
                if ($is_primary) {
                    $primary_image_set = true;
                }
            }
        }
    }
    
    if (!$primary_image_set) {
        $errors[] = 'Please select a primary image';
    }
    
    // If no errors, update vehicle
    if (empty($errors)) {
        $sql = "UPDATE vehicles SET 
                title = ?, type = ?, `condition` = ?, make = ?, model = ?, 
                year = ?, price = ?, transmission = ?, fuel_type = ?, 
                mileage = ?, description = ?, location = ?, 
                contact_name = ?, contact_phone = ?, status = ?, 
                updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssiisssssssi", 
            $title, $type, $condition, $make, $model, $year, $price,
            $transmission, $fuel_type, $mileage, $description, $location,
            $contact_name, $contact_phone, $status, $vehicle_id
        );
        
        if ($stmt->execute()) {
            $upload_dir = __DIR__ . '/../../assets/images/uploads/vehicles/';
            
            // Process image deletions
            if (isset($_POST['delete_images'])) {
                foreach ($_POST['delete_images'] as $image_id) {
                    $image_id = (int)$image_id;
                    $sql = "SELECT image_path FROM vehicle_images WHERE id = ? AND vehicle_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $image_id, $vehicle_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $image = $result->fetch_assoc();
                        $file_path = __DIR__ . '/../..' . $image['image_path'];
                        
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                        
                        $sql = "DELETE FROM vehicle_images WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $image_id);
                        $stmt->execute();
                    }
                }
            }
            
            // Update primary image if needed
            if (isset($_POST['existing_primary'])) {
                $primary_id = (int)$_POST['existing_primary'];
                
                // First, set all images for this vehicle to not primary
                $sql = "UPDATE vehicle_images SET is_primary = FALSE WHERE vehicle_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $vehicle_id);
                $stmt->execute();
                
                // Then set the selected one as primary
                $sql = "UPDATE vehicle_images SET is_primary = TRUE WHERE id = ? AND vehicle_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $primary_id, $vehicle_id);
                $stmt->execute();
            }
            
            // Process new images
            foreach ($new_images as $img) {
                $file = $img['file'];
                $is_primary = $img['is_primary'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = "vehicle_{$vehicle_id}_" . uniqid() . ".$ext";
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $image_path = "/vehicle_marketplace/assets/images/uploads/vehicles/" . $filename;
                    
                    $sql = "INSERT INTO vehicle_images (vehicle_id, image_path, is_primary) 
                            VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isi", $vehicle_id, $image_path, $is_primary);
                    $stmt->execute();
                    
                    // If this is the primary image, update all others to not primary
                    if ($is_primary) {
                        $new_primary_id = $stmt->insert_id;
                        
                        $sql = "UPDATE vehicle_images SET is_primary = FALSE 
                                WHERE vehicle_id = ? AND id != ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ii", $vehicle_id, $new_primary_id);
                        $stmt->execute();
                    }
                }
            }
            
            $success = true;
            // Refresh vehicle data
            $vehicle = get_vehicle($vehicle_id);
        } else {
            $errors[] = 'Failed to update vehicle. Please try again.';
        }
    }
}
?>

<div class="container">

<!-- Link external CSS files for styling -->
<link rel="stylesheet" href="../../vehicle_marketplace/assets/css/style.css">
<link rel="stylesheet" href="../../vehicle_marketplace/assets/css/responsive.css">

<!-- Link external JavaScript files -->
<!-- Main JavaScript file for site functionality -->
<script src="../../vehicle_marketplace/assets/js/main.js" defer></script>

    <h1>Edit Vehicle Listing</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p>Your vehicle listing has been updated successfully!</p>
            <a href="../../vehicle_marketplace/vehicles/view.php?id=<?php echo $vehicle_id; ?>" class="btn btn-primary">View Listing</a>
            <a href="../../vehicle_marketplace/vehicles/list.php" class="btn btn-secondary">Back to Listings</a>
        </div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" class="vehicle-form">
            <h2>Vehicle Information</h2>
            <div class="form-group">
                <label for="title">Title*</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($vehicle['title']); ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Vehicle Type*</label>
                    <select id="type" name="type" required>
                        <option value="car" <?php echo $vehicle['type'] === 'car' ? 'selected' : ''; ?>>Car</option>
                        <option value="truck" <?php echo $vehicle['type'] === 'truck' ? 'selected' : ''; ?>>Truck</option>
                        <option value="motorcycle" <?php echo $vehicle['type'] === 'motorcycle' ? 'selected' : ''; ?>>Motorcycle</option>
                        <option value="suv" <?php echo $vehicle['type'] === 'suv' ? 'selected' : ''; ?>>SUV</option>
                        <option value="van" <?php echo $vehicle['type'] === 'van' ? 'selected' : ''; ?>>Van</option>
                        <option value="other" <?php echo $vehicle['type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="condition">Condition*</label>
                    <select id="condition" name="condition" required>
                        <option value="new" <?php echo $vehicle['condition'] === 'new' ? 'selected' : ''; ?>>New</option>
                        <option value="used" <?php echo $vehicle['condition'] === 'used' ? 'selected' : ''; ?>>Used</option>
                        <option value="refurbished" <?php echo $vehicle['condition'] === 'refurbished' ? 'selected' : ''; ?>>Refurbished</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="make">Make*</label>
                    <input type="text" id="make" name="make" value="<?php echo htmlspecialchars($vehicle['make']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="model">Model*</label>
                    <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($vehicle['model']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="year">Year*</label>
                    <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" value="<?php echo $vehicle['year']; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price ($)*</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" value="<?php echo $vehicle['price']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="transmission">Transmission*</label>
                    <select id="transmission" name="transmission" required>
                        <option value="automatic" <?php echo $vehicle['transmission'] === 'automatic' ? 'selected' : ''; ?>>Automatic</option>
                        <option value="manual" <?php echo $vehicle['transmission'] === 'manual' ? 'selected' : ''; ?>>Manual</option>
                        <option value="semi-automatic" <?php echo $vehicle['transmission'] === 'semi-automatic' ? 'selected' : ''; ?>>Semi-Automatic</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fuel_type">Fuel Type*</label>
                    <select id="fuel_type" name="fuel_type" required>
                        <option value="petrol" <?php echo $vehicle['fuel_type'] === 'petrol' ? 'selected' : ''; ?>>Petrol</option>
                        <option value="diesel" <?php echo $vehicle['fuel_type'] === 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                        <option value="electric" <?php echo $vehicle['fuel_type'] === 'electric' ? 'selected' : ''; ?>>Electric</option>
                        <option value="hybrid" <?php echo $vehicle['fuel_type'] === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                        <option value="other" <?php echo $vehicle['fuel_type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="mileage">Mileage*</label>
                    <input type="number" id="mileage" name="mileage" min="0" value="<?php echo $vehicle['mileage']; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($vehicle['description']); ?></textarea>
            </div>
            
            <h2>Contact Information</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="contact_name">Contact Name*</label>
                    <input type="text" id="contact_name" name="contact_name" value="<?php echo htmlspecialchars($vehicle['contact_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_phone">Contact Phone*</label>
                    <input type="tel" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($vehicle['contact_phone']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="location">Location*</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($vehicle['location']); ?>" required>
                </div>
            </div>
            
            <?php if (has_role('admin') || has_role('manager')): ?>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="available" <?php echo $vehicle['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="sold" <?php echo $vehicle['status'] === 'sold' ? 'selected' : ''; ?>>Sold</option>
                        <option value="pending" <?php echo $vehicle['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>
            <?php endif; ?>
            
            <h2>Vehicle Images</h2>
            <div class="current-images">
                <h3>Current Images</h3>
                <?php if (!empty($vehicle['images'])): ?>
                    <div class="image-grid">
                        <?php foreach ($vehicle['images'] as $image): ?>
                            <div class="image-item">
                                <img src="<?php echo $image['image_path']; ?>" alt="Vehicle Image">
                                <div class="image-actions">
                                    <div class="primary-radio">
                                        <input type="radio" id="existing_primary_<?php echo $image['id']; ?>" 
                                               name="existing_primary" value="<?php echo $image['id']; ?>"
                                               <?php echo $image['is_primary'] ? 'checked' : ''; ?>>
                                        <label for="existing_primary_<?php echo $image['id']; ?>">Primary</label>
                                    </div>
                                    <div class="delete-checkbox">
                                        <input type="checkbox" id="delete_<?php echo $image['id']; ?>" 
                                               name="delete_images[]" value="<?php echo $image['id']; ?>">
                                        <label for="delete_<?php echo $image['id']; ?>">Delete</label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No images currently uploaded.</p>
                <?php endif; ?>
            </div>
            
            <div class="new-images">
                <h3>Add New Images (Up to 4)</h3>
                <div class="form-row">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="form-group image-upload">
                            <label for="new_image_<?php echo $i; ?>">New Image <?php echo $i; ?></label>
                            <input type="file" id="new_image_<?php echo $i; ?>" name="new_image_<?php echo $i; ?>" accept="image/*">
                            <div class="primary-radio">
                                <input type="radio" id="primary_new_<?php echo $i; ?>" name="primary_image" value="new_<?php echo $i; ?>">
                                <label for="primary_new_<?php echo $i; ?>">Set as Primary</label>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-submit">
                <button type="submit" class="btn btn-primary">Update Listing</button>
                <a href="/vehicle_marketplace/vehicles/view.php?id=<?php echo $vehicle_id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '../../includes/footer.php'; ?>