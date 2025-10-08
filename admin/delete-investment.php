<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

if (isset($_GET['id'])) {
    $investment_id = intval($_GET['id']);
    
    $query = "DELETE FROM investments WHERE id = $investment_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Investment deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete investment.';
    }
}

header('Location: investments.php');
exit;
?>
