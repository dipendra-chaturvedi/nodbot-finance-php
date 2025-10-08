<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format!';
    }
    
    // Check if email exists for other users
    $check_query = "SELECT * FROM users WHERE email = '$email' AND id != $user_id";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = 'Email already used by another user!';
    }
    
    // If password is being changed
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match!';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters!';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
        header('Location: users.php');
        exit;
    }
    
    // Update user
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query = "UPDATE users 
                  SET name = '$name', email = '$email', phone = '$phone', 
                      user_type = '$user_type', password = '$hashed_password' 
                  WHERE id = $user_id";
    } else {
        $query = "UPDATE users 
                  SET name = '$name', email = '$email', phone = '$phone', user_type = '$user_type' 
                  WHERE id = $user_id";
    }
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = 'User updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update user: ' . mysqli_error($conn);
    }
}

header('Location: users.php');
exit;
?>
