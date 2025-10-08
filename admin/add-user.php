<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match!';
    }
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters!';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format!';
    }
    
    // Check if email exists
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = 'Email already registered!';
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: users.php');
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $query = "INSERT INTO users (name, email, phone, password, user_type, created_at) 
              VALUES ('$name', '$email', '$phone', '$hashed_password', '$user_type', NOW())";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'User added successfully!';
    } else {
        $_SESSION['error'] = 'Failed to add user: ' . mysqli_error($conn);
    }
}

header('Location: users.php');
exit;
?>
