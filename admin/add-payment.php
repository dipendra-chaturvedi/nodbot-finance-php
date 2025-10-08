<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);
    $payment_type = mysqli_real_escape_string($conn, $_POST['payment_type']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $transaction_id = !empty($_POST['transaction_id']) ? mysqli_real_escape_string($conn, $_POST['transaction_id']) : 'TXN' . time() . rand(1000, 9999);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validation
    if ($amount <= 0) {
        $_SESSION['error'] = 'Amount must be greater than zero!';
        header('Location: payments.php');
        exit;
    }
    
    // Insert payment
    $query = "INSERT INTO payments (user_id, amount, payment_type, payment_method, transaction_id, status, payment_date) 
              VALUES ($user_id, $amount, '$payment_type', '$payment_method', '$transaction_id', '$status', NOW())";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Payment added successfully! Transaction ID: ' . $transaction_id;
    } else {
        $_SESSION['error'] = 'Failed to add payment: ' . mysqli_error($conn);
    }
}

header('Location: payments.php');
exit;
?>
