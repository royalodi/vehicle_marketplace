<?php
require_once __DIR__ . '../../includes/header.php';

if (!is_logged_in()) {
    redirect('/vehicle_marketplace/auth/login.php');
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
    
    // Validate inputs
    if (empty($title)) $errors[] = 'Title is required';
    if (empty($make)) $errors[] = 'Make is required';
    if (empty($model)) $errors[] = 'Model is required';
    if (empty($year) || !is_numeric($year)) $errors[] = 'Valid year is required';
    if (empty($price) || !is_numeric($price)) $errors[] = 'Valid price is required';
    if (empty($mileage) || !is_numeric($mileage)) $errors[] = 'Valid mileage is required';
    if (empty($contact_name)) $errors[] = 'Contact name is required';
    if (empty($contact_phone)) $errors[] = 'Contact phone is required';
    
    // Validate images
    $images = [];
    $primary_image_set = false;
    
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_FILES["image_$i"]['name'])) {
            $file = $_FILES["image_$i"];
            $is_primary = isset($_POST["primary_image"]) && $_POST["primary_image"] == $i;
            
            // Validate image
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = "Image $i must be a JPEG, PNG, or GIF";
            } elseif ($file['size'] > $max_size) {
                $errors[] = "Image $i must be less than 5MB";
            } else {
                $images[] = [
                    'file' => $file,
                    'is_primary' => $is_primary
                ];
                
                if ($is_primary) {
                    $primary_image_set = true;
                }
            }
        }
    }
    
    if (empty($images)) {
        $errors[] = 'At least one image is required';
    } elseif (!$primary_image_set) {
        $errors[] = 'Please select a primary image';
    }
    
    // If no errors, save vehicle and images
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        
        $sql = "INSERT INTO vehicles (
            user_id, title, type, `conditions`, make, model, year, price, 
            transmission, fuel_type, mileage, description, location, 
            contact_name, contact_phone
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssiisssssss", 
            $user_id, $title, $type, $condition, $make, $model, $year, $price,
            $transmission, $fuel_type, $mileage, $description, $location,
            $contact_name, $contact_phone
        );
        
        if ($stmt->execute()) {
            $vehicle_id = $stmt->insert_id;
            $upload_dir = __DIR__ . '/../../assets/images/uploads/vehicles/';
            
            // Create upload directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Process images
            foreach ($images as $img) {
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
                }
            }
            
            $success = true;
        } else {
            $errors[] = 'Failed to save vehicle. Please try again.';
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

    <h1>Sell Your Vehicle</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p>Your vehicle has been listed successfully!</p>
            <a href="../../vehicle_marketplace/vehicles/view.php?id=<?php echo $vehicle_id; ?>" class="btn btn-primary">View Listing</a>
            <a href="../../vehicle_marketplace/pages/sell.php" class="btn btn-secondary">List Another Vehicle</a>
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
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Vehicle Type*</label>
                    <select id="type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="car">Car</option>
                        <option value="truck">Truck</option>
                        <option value="motorcycle">Motorcycle</option>
                        <option value="suv">SUV</option>
                        <option value="van">Van</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="condition">Condition*</label>
                    <select id="condition" name="condition" required>
                        <option value="">Select Condition</option>
                        <option value="new">New</option>
                        <option value="used">Used</option>
                        <option value="refurbished">Refurbished</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="make">Make*</label>
                    <input type="text" id="make" name="make" required>
                </div>
                
                <div class="form-group">
                    <label for="model">Model*</label>
                    <input type="text" id="model" name="model" required>
                </div>
                
                <div class="form-group">
                    <label for="year">Year*</label>
                    <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price ($)*</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="transmission">Transmission*</label>
                    <select id="transmission" name="transmission" required>
                        <option value="">Select Transmission</option>
                        <option value="automatic">Automatic</option>
                        <option value="manual">Manual</option>
                        <option value="semi-automatic">Semi-Automatic</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="fuel_type">Fuel Type*</label>
                    <select id="fuel_type" name="fuel_type" required>
                        <option value="">Select Fuel Type</option>
                        <option value="petrol">Petrol</option>
                        <option value="diesel">Diesel</option>
                        <option value="electric">Electric</option>
                        <option value="hybrid">Hybrid</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="mileage">Mileage*</label>
                    <input type="number" id="mileage" name="mileage" min="0" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"></textarea>
            </div>
            
            <h2>Contact Information</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="contact_name">Contact Name*</label>
                    <input type="text" id="contact_name" name="contact_name" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_phone">Contact Phone*</label>
                    <input type="tel" id="contact_phone" name="contact_phone" required>
                </div>
                
                <div class="form-group">
                    <label for="location">Location*</label>
                    <input type="text" id="location" name="location" required>
                </div>
            </div>
            
            <h2>Vehicle Images (Upload up to 4 images)</h2>
            <div class="form-row">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="form-group image-upload">
                        <label for="image_<?php echo $i; ?>">Image <?php echo $i; ?></label>
                        <input type="file" id="image_<?php echo $i; ?>" name="image_<?php echo $i; ?>" accept="image/*">
                        <div class="primary-radio">
                            <input type="radio" id="primary_<?php echo $i; ?>" name="primary_image" value="<?php echo $i; ?>">
                            <label for="primary_<?php echo $i; ?>">Primary Image</label>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
            
            <div class="form-submit">
                <button type="submit" class="btn btn-primary">List Vehicle</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '../../includes/footer.php'; ?>