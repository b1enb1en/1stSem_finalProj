<?php
session_start();
require_once 'db_init.php';
$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $password2 = $_POST['password2'] ?? '';

  if ($username === '' || $password === '' || $password2 === '') {
    $errors[] = 'All fields are required.';
  } elseif ($password !== $password2) {
    $errors[] = 'Passwords do not match.';
  } else {
    $stmt = $db->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
      $errors[] = 'Username already taken.';
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = $db->prepare('INSERT INTO users (username, password_hash) VALUES (:u, :p)');
      $ins->execute([':u' => $username, ':p' => $hash]);

      $_SESSION['user_id'] = $db->lastInsertId();
      $_SESSION['username'] = $username;
      header('Location: dashboard.php');
      exit;
    }
  }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Register</title>
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
      background: #28a745;
      color: white;
      border: none;
      cursor: pointer;
    }

    button:hover {
      background: #218838;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2>Create Account</h2>
    <?php if ($errors): ?>
      <div class="error"><?php foreach ($errors as $e) echo "<p style='margin:0'>$e</p>"; ?></div>
    <?php endif; ?>
    <form method="post">
      <label>Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      <label>Password</label>
      <input type="password" name="password">
      <label>Confirm Password</label>
      <input type="password" name="password2">
      <button type="submit">Register</button>
    </form>
    <p style="text-align:center; margin-top:15px;">Already have an account? <a href="login.php">Login</a></p>
  </div>
</body>

</html>