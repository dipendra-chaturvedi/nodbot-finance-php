<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    $query = "SELECT id, name, email, phone, user_type FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
