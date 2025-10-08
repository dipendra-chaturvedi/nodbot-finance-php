<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

if (isset($_GET['id'])) {
    $loan_id = intval($_GET['id']);
    
    $query = "DELETE FROM loans WHERE id = $loan_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Loan deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete loan.';
    }
}

header('Location: loans.php');
exit;
?>
