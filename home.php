<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'Not provided'); ?>!</h1>
    <p>Email: <?php echo htmlspecialchars($user['email'] ?? 'Not provided'); ?></p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>