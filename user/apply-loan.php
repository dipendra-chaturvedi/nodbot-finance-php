<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $amount = floatval($_POST['amount']);
    $interest_rate = floatval($_POST['interest_rate']);
    $duration_months = intval($_POST['duration_months']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Validation
    if ($amount < 1000) {
        $_SESSION['error'] = 'Minimum loan amount is ₹1,000';
        header('Location: loans.php');
        exit;
    }

    // Insert loan application
    $query = "INSERT INTO loans (user_id, amount, purpose, duration_months, interest_rate, notes, status, created_at) 
              VALUES ($user_id, $amount, '$purpose', $duration_months, $interest_rate, '$notes', 'pending', NOW())";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Loan application submitted successfully! We will review and contact you soon.';
        header('Location: loans.php');
    } else {
        $_SESSION['error'] = 'Failed to submit loan application. Please try again.';
        header('Location: loans.php');
    }
    exit;
} else {
    header('Location: loans.php');
    exit;
}
?>
