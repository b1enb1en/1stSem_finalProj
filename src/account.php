<?php
session_start();

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Safely get username from session
$username = $_SESSION['username'] ?? 'Unknown User';

// Handle delete account action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_account') {
    // You should also delete the user from the database here
    session_destroy();
    header('Location: index.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <style>
        .card { padding: 20px; border: 1px solid #ccc; border-radius: 8px; max-width: 400px; margin: 20px auto; }
        .btn-danger { background: #dc3545; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="card" id="profile">
        <h2 style="color: #721c24;">Danger Zone</h2>
        <p>Logged in as: <strong><?= htmlspecialchars($username) ?></strong></p>
        <form method="post" onsubmit="return confirm('Are you sure you want to delete your account?');">
            <input type="hidden" name="action" value="delete_account">
            <button type="submit" class="btn-danger">Delete Account</button>
        </form>
    </div>
</body>
</html>
