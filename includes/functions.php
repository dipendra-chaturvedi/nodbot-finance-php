<?php
// includes/functions.php

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

// Redirect to user dashboard if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../user/dashboard.php');
        exit;
    }
}

// Get logged in user info
function getUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'user_type' => $_SESSION['user_type']
        ];
    }
    return null;
}

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Display flash message
function showFlashMessage() {
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo $_SESSION['success'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo $_SESSION['error'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['error']);
    }
}
?>
