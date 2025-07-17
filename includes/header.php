<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '../functions.php';

// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
$user_role = $logged_in ? $_SESSION['role'] : 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Marketplace - <?php echo isset($page_title) ? $page_title : 'Buy & Sell Vehicles'; ?></title>
    <link rel="stylesheet" href="../vehicle_marketplace/assets/css/style.css">
    <link rel="stylesheet" href="../vehicle_marketplace/assets/css/responsive.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="/vehicle_marketplace/index.php">
                    <img src="../vehicle_marketplace/assets/images/logos/logo.png" alt="Vehicle Marketplace">
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../../vehicle_marketplace/index.php">Home</a></li>
                    <li><a href="../../vehicle_marketplace/pages/buy.php">Buy</a></li>
                    <?php if ($logged_in): ?>
                        <li><a href="../../vehicle_marketplace/pages/sell.php">Sell</a></li>
                    <?php endif; ?>
                    <li><a href="../../vehicle_marketplace/pages/about.php">About</a></li>
                    <li><a href="../../vehicle_marketplace/pages/contact.php">Contact</a></li>
                </ul>
            </nav>
            <div class="auth-buttons">
                <?php if ($logged_in): ?>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="../../vehicle_marketplace/admin/dashboard.php" class="btn btn-admin">Admin Panel</a>
                    <?php elseif ($user_role === 'manager'): ?>
                        <a href="../../vehicle_marketplace/manager/dashboard.php" class="btn btn-manager">Manager Panel</a>
                    <?php endif; ?>
                    <a href="../../vehicle_marketplace/auth/logout.php" class="btn btn-logout">Logout</a>
                <?php else: ?>
                    <a href="../../vehicle_marketplace/auth/login.php" class="btn btn-login">Login</a>
                    <a href="../../vehicle_marketplace/auth/register.php" class="btn btn-register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="main-content">