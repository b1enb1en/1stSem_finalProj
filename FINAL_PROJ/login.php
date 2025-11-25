<?php
session_start();
require_once 'db_init.php';
$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($username === '' || $password === '') {
    $errors[] = 'All fields are required.';
  } else {
    // Check if user exists
    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
      // Requirement: Account not existing -> Show error with Register button
      $errors[] = 'Account not found. <a href="register.php" class="btn-link">Register here</a>';
    } else {
      // User exists, check password
      if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: dashboard.php');
        exit;
      } else {
        $errors[] = 'Incorrect password.';
      }
    }
  }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Login</title>
  <style>
    body {
      font-family: sans-serif;
      padding: 2rem;
      background: #f4f4f9;
    }

    .container {
      max-width: 400px;
      margin: 0 auto;
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .error {
      color: #d32f2f;
      background: #ffcdd2;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 15px;
    }

    input {
      width: 100%;
      padding: 8px;
      margin: 5px 0 15px;
      box-sizing: border-box;
    }

    button {
      width: 100%;
      padding: 10px;
      background: #333;
      color: white;
      border: none;
      cursor: pointer;
    }

    button:hover {
      background: #555;
    }

    .btn-link {
      color: #0056b3;
      text-decoration: underline;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2>Login</h2>
    <?php if ($errors): ?>
      <div class="error"><?php foreach ($errors as $e) echo "<p style='margin:0'>$e</p>"; ?></div>
    <?php endif; ?>
    <form method="post">
      <label>Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      <label>Password</label>
      <input type="password" name="password">
      <button type="submit">Login</button>
    </form>
    <p style="text-align:center; margin-top:15px;">Don't have an account? <a href="register.php">Register</a></p>
  </div>
</body>

</html>