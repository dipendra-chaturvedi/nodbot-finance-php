<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

if (isset($_GET['id']) && isset($_GET['status'])) {
    $payment_id = intval($_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    
    $query = "UPDATE payments SET status = '$status' WHERE id = $payment_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Payment status updated to ' . ucfirst($status) . ' successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update payment status.';
    }
}

header('Location: payments.php');
exit;
?>
