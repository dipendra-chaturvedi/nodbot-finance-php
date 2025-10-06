<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    // Fetch ONLY regular users (not admin)
    $query = "SELECT * FROM users WHERE email = '$email' AND user_type = 'user' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];

            // Redirect to user dashboard
            header('Location: user/dashboard.php');
            exit;
        } else {
            $_SESSION['error'] = 'Invalid password!';
            header('Location: index.php');
            exit;
        }
    } else {
        $_SESSION['error'] = 'User not found! Please check your email or register.';
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>
