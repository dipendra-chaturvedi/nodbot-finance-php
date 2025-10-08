<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

if (isset($_GET['id'])) {
    $payment_id = intval($_GET['id']);
    
    $query = "DELETE FROM payments WHERE id = $payment_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Payment deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete payment: ' . mysqli_error($conn);
    }
}

header('Location: payments.php');
exit;
?>
