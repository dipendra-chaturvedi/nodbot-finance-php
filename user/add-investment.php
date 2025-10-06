<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $amount = floatval($_POST['amount']);
    $plan = mysqli_real_escape_string($conn, $_POST['plan']);
    $duration = intval($_POST['duration']);
    $interest_rate = floatval($_POST['interest_rate']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Validation
    if ($amount < 100) {
        $_SESSION['error'] = 'Minimum deposit amount is ₹100';
        header('Location: investments.php');
        exit;
    }

    if (empty($plan)) {
        $_SESSION['error'] = 'Please select an investment plan';
        header('Location: investments.php');
        exit;
    }

    // Insert investment
    $query = "INSERT INTO investments (user_id, amount, plan, duration_months, interest_rate, notes, status, investment_date) 
              VALUES ($user_id, $amount, '$plan', $duration, $interest_rate, '$notes', 'active', NOW())";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Investment added successfully! Amount: ' . formatCurrency($amount);
        header('Location: investments.php');
    } else {
        $_SESSION['error'] = 'Failed to add investment. Please try again.';
        header('Location: investments.php');
    }
    exit;
} else {
    header('Location: investments.php');
    exit;
}
?>
