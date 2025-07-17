<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (isset($_GET['make'])) {
    $make = sanitize($_GET['make']);
    
    $sql = "SELECT DISTINCT model FROM vehicles WHERE make = ? ORDER BY model";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $make);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $models = [];
    while ($row = $result->fetch_assoc()) {
        $models[] = $row['model'];
    }
    
    echo json_encode($models);
} else {
    echo json_encode([]);
}
?>