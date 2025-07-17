<?php
require_once __DIR__ . '/../../includes/header.php';

if (!has_role('admin')) {
    redirect('/vehicle_marketplace/index.php');
}

$success = false;
$errors = [];

// Get current settings
$settings = [
    'site_name' => 'Vehicle Marketplace',
    'site_email' => 'info@vehiclemarketplace.com',
    'items_per_page' => 10,
    'currency' => 'USD',
    'allow_registrations' => true
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_name = sanitize($_POST['site_name']);
    $site_email = sanitize($_POST['site_email']);
    $items_per_page = (int)$_POST['items_per_page'];
    $currency = sanitize($_POST['currency']);
    $allow_registrations = isset($_POST['allow_registrations']) ? 1 : 0;
    
    // Validate inputs
    if (empty($site_name)) $errors[] = 'Site name is required';
    if (empty($site_email)) {
        $errors[] = 'Site email is required';
    } elseif (!filter_var($site_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    if ($items_per_page < 5 || $items_per_page > 50) {
        $errors[] = 'Items per page must be between 5 and 50';
    }
    
    if (empty($errors)) {
        // In a real application, you would save these to a database
        $settings['site_name'] = $site_name;
        $settings['site_email'] = $site_email;
        $settings['items_per_page'] = $items_per_page;
        $settings['currency'] = $currency;
        $settings['allow_registrations'] = $allow_registrations;
        
        $success = true;
    }
}
?>

<div class="admin-container">
    <h1>Site Settings</h1>
    
    <div class="admin-actions">
        <a href="/vehicle_marketplace/admin/dashboard.php" class="btn btn-back">Back to Dashboard</a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p>Settings have been updated successfully!</p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="settings-form">
        <div class="form-group">
            <label for="site_name">Site Name*</label>
            <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="site_email">Site Email*</label>
            <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="items_per_page">Items Per Page*</label>
            <input type="number" id="items_per_page" name="items_per_page" min="5" max="50" value="<?php echo $settings['items_per_page']; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="currency">Currency</label>
            <select id="currency" name="currency">
                <option value="USD" <?php echo $settings['currency'] === 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                <option value="EUR" <?php echo $settings['currency'] === 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                <option value="GBP" <?php echo $settings['currency'] === 'GBP' ? 'selected' : ''; ?>>British Pound (GBP)</option>
                <option value="JPY" <?php echo $settings['currency'] === 'JPY' ? 'selected' : ''; ?>>Japanese Yen (JPY)</option>
            </select>
        </div>
        
        <div class="form-group checkbox-group">
            <input type="checkbox" id="allow_registrations" name="allow_registrations" <?php echo $settings['allow_registrations'] ? 'checked' : ''; ?>>
            <label for="allow_registrations">Allow New User Registrations</label>
        </div>
        
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>