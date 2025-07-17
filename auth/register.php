<?php
require_once __DIR__ . '../../includes/header.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '../../config/database.php';
    
    // Get form data
    $username = sanitize($_POST['username']);
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $location = sanitize($_POST['location']);
    $password = sanitize($_POST['password']);
    $confirm_password = sanitize($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($phone)) $errors[] = 'Phone number is required';
    if (empty($location)) $errors[] = 'Location is required';
    if (empty($password)) $errors[] = 'Password is required';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
    
    // Check if username or email already exists
    $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = 'Username or email already exists';
    }
    
    // If no errors, register user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, name, email, phone, location, password) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $username, $name, $email, $phone, $location, $hashed_password);
        
        if ($stmt->execute()) {
            // Auto-login after registration
            $user_id = $stmt->insert_id;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'customer';
            
            redirect('/vehicle_marketplace/index.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>

<div class="auth-container">

<!-- Link external CSS files for styling -->
<link rel="stylesheet" href="../../vehicle_marketplace/assets/css/style.css">
<link rel="stylesheet" href="../../vehicle_marketplace/assets/css/responsive.css">

<!-- Link external JavaScript files -->
<!-- Main JavaScript file for site functionality -->
<script src="../../vehicle_marketplace/assets/js/main.js" defer></script>


    <h1>Register</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" required>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <p>Already have an account? <a href="../../vehicle_marketplace/auth/login.php">Login here</a></p>
</div>

<?php require_once __DIR__ . '../../includes/footer.php'; ?>