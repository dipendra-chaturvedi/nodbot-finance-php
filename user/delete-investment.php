<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

if (isset($_GET['id'])) {
    $investment_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if investment belongs to user
    $check_query = "SELECT * FROM investments WHERE id = $investment_id AND user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $delete_query = "DELETE FROM investments WHERE id = $investment_id AND user_id = $user_id";
        
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['success'] = 'Investment deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete investment.';
        }
    } else {
        $_SESSION['error'] = 'Investment not found or unauthorized access.';
    }
} else {
    $_SESSION['error'] = 'Invalid request.';
}

header('Location: investments.php');
exit;
?>
