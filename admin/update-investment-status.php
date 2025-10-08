<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $investment_id = intval($_POST['investment_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $query = "UPDATE investments SET status = '$status' WHERE id = $investment_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Investment status updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update status.';
    }
}

header('Location: investments.php');
exit;
?>
