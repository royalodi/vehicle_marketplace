<?php
// Redirect to specified page
function redirect($page) {
    header("Location: " . $page);
    exit();
}

// Sanitize input data
function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($data)));
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user has specific role
function has_role($role) {
    return is_logged_in() && $_SESSION['role'] === $role;
}

// Get user data
function get_user_data($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get all vehicles
function get_vehicles($limit = null, $offset = null) {
    global $conn;
    $sql = "SELECT v.*, vi.image_path 
            FROM vehicles v 
            LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = TRUE
            WHERE v.status = 'available'
            ORDER BY v.created_at DESC";
    
    if ($limit !== null) {
        $sql .= " LIMIT ?";
        if ($offset !== null) {
            $sql .= " OFFSET ?";
        }
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($limit !== null) {
        if ($offset !== null) {
            $stmt->bind_param("ii", $limit, $offset);
        } else {
            $stmt->bind_param("i", $limit);
        }
    }
    
    $stmt->execute();
    return $stmt->get_result();
}

// Get vehicle by ID
function get_vehicle($id) {
    global $conn;
    $sql = "SELECT v.*, u.name as seller_name, u.phone as seller_phone, u.location as seller_location 
            FROM vehicles v 
            JOIN users u ON v.user_id = u.id 
            WHERE v.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $vehicle = $stmt->get_result()->fetch_assoc();
    
    if ($vehicle) {
        $sql = "SELECT * FROM vehicle_images WHERE vehicle_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $vehicle['images'] = $images;
    }
    
    return $vehicle;
}
?>