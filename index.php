<?php
session_start();

// If user is already logged in, redirect to home
if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login with Keycloak</title>
    <style>
        .login-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .login-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Welcome to Our Application</h1>
    <p>Please login to continue:</p>
    <a href="login.php" class="login-btn">Login with Keycloak</a>
</body>
</html>