<?php
require_once '../config.php';

// Secret registration code (must match the one in register.php)
define('ADMIN_REGISTRATION_CODE', 'NODBOT2025');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $registration_code = trim($_POST['registration_code']);

    // Validation
    $errors = [];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match!';
    }

    // Check password length
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long!';
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format!';
    }

    // Validate phone number
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = 'Phone number must be 10 digits!';
    }

    // Verify registration code
    if ($registration_code !== ADMIN_REGISTRATION_CODE) {
        $errors[] = 'Invalid registration code! Contact system administrator.';
    }

    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = 'Email already registered!';
    }

    // If there are errors, redirect back
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: register.php');
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert admin into database
    $query = "INSERT INTO users (name, email, phone, password, user_type, created_at) 
              VALUES ('$name', '$email', '$phone', '$hashed_password', 'admin', NOW())";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'Admin account created successfully! Please login.';
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['error'] = 'Registration failed! Please try again. Error: ' . mysqli_error($conn);
        header('Location: register.php');
        exit;
    }

} else {
    header('Location: register.php');
    exit;
}
?>
