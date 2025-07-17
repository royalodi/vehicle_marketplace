<?php
require_once __DIR__ . '/../includes/header.php';

if (!is_logged_in()) {
    redirect('/vehicle_marketplace/auth/login.php');
}

// Redirect to add vehicle page
redirect('/vehicle_marketplace/vehicles/add.php');
?>