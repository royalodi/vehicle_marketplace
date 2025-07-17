<?php
require_once __DIR__ . '../../includes/header.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Validate inputs
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (empty($errors)) {
        // In a real application, you would send an email here
        // For this example, we'll just simulate success
        $success = true;
    }
}
?>

<div class="container contact-page">

<!-- Link external CSS files for styling -->
<link rel="stylesheet" href="../../vehicle_marketplace/assets/css/style.css">
<link rel="stylesheet" href="../../vehicle_marketplace/assets/css/responsive.css">

<!-- Link external JavaScript files -->
<!-- Main JavaScript file for site functionality -->
<script src="../../vehicle_marketplace/assets/js/main.js" defer></script>

    <h1>Contact Us</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <p>Thank you for contacting us! We'll get back to you soon.</p>
            <a href="/vehicle_marketplace/index.php" class="btn btn-primary">Back to Home</a>
        </div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="contact-container">
            <div class="contact-form">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Name*</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email*</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject*</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message*</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
            
            <div class="contact-info">
                <h2>Our Office</h2>
                <p><strong>Address:</strong> 123 Vehicle St, Auto City, AC 12345</p>
                <p><strong>Phone:</strong> (555) 123-4567</p>
                <p><strong>Email:</strong> info@vehiclemarketplace.com</p>
                
                <h2>Business Hours</h2>
                <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                <p>Saturday: 10:00 AM - 4:00 PM</p>
                <p>Sunday: Closed</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '../../includes/footer.php'; ?>