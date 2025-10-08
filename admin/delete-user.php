<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // Prevent deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = 'You cannot delete your own account!';
        header('Location: users.php');
        exit;
    }
    
    // Delete user (CASCADE will handle related records)
    $query = "DELETE FROM users WHERE id = $user_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'User deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete user: ' . mysqli_error($conn);
    }
}

header('Location: users.php');
exit;
?>
