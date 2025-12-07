<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="card" id="profile" style="margin-top:20px; border: 1px solid #f5c6cb;">
        <h2 style="color: #721c24;">Danger Zone</h2>
        <p>Logged in as: <strong><?= htmlspecialchars($username) ?></strong></p>
        <form method="post" onsubmit="return confirm('Are you sure you want to delete your account permanently?');">
            <input type="hidden" name="action" value="delete_account">
            <button type="submit" class="btn-danger">Delete My Account</button>
        </form>
    </div>
</body>
</html>