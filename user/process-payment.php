<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $payment_type = mysqli_real_escape_string($conn, $_POST['payment_type']);
    $amount = floatval($_POST['amount']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $transaction_id = !empty($_POST['transaction_id']) ? mysqli_real_escape_string($conn, $_POST['transaction_id']) : 'TXN' . time() . rand(1000, 9999);
    $investment_id = !empty($_POST['investment_id']) ? intval($_POST['investment_id']) : null;
    $loan_id = !empty($_POST['loan_id']) ? intval($_POST['loan_id']) : null;
    
    // Validation
    if ($amount <= 0) {
        $_SESSION['error'] = 'Invalid amount! Amount must be greater than zero.';
        header('Location: payments.php');
        exit;
    }
    
    if (empty($payment_type) || empty($payment_method)) {
        $_SESSION['error'] = 'Please fill all required fields!';
        header('Location: payments.php');
        exit;
    }
    
    // Insert payment
    $query = "INSERT INTO payments (user_id, amount, payment_type, payment_method, transaction_id, investment_id, loan_id, status, payment_date) 
              VALUES ($user_id, $amount, '$payment_type', '$payment_method', '$transaction_id', " . 
              ($investment_id ? $investment_id : 'NULL') . ", " . 
              ($loan_id ? $loan_id : 'NULL') . ", 'pending', NOW())";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Payment submitted successfully! Transaction ID: ' . $transaction_id . '. Status: Pending verification.';
        
        // If it's an investment payment, you might want to create an investment record
        if ($payment_type === 'investment' && !$investment_id) {
            // Optional: Create investment record automatically
        }
    } else {
        $_SESSION['error'] = 'Failed to process payment. Please try again. Error: ' . mysqli_error($conn);
    }
    
    header('Location: payments.php');
    exit;
} else {
    header('Location: payments.php');
    exit;
}
?>
