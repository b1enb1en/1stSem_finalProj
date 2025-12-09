<?php
session_start();
require_once 'db_init.php';
$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if ($username === '' || $password === '' || $confirm_password === '') {
    $errors[] = 'All fields are required.';
  } elseif ($password !== $confirm_password) {
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
      header('Location: login.php');
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
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #0B132A;
      padding: 2rem;
    }

    .material-form {
      background: white;
      padding: 2.5rem;
      border-radius: 12px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 0 20px rgba(111, 255, 232, 0.3);
      text-align: center;
    }

    h2 {
      margin-bottom: 1.5rem;
      text-transform: uppercase;
      color: #0B132A;
    }

    .input-field {
      position: relative;
      margin: 2rem 0;
      text-align: left;
    }

    .material-form input {
      width: 100%;
      padding: 0.8rem 0;
      border: none;
      border-bottom: 2px solid #ddd;
      outline: none;
      transition: 0.2s;
      background: transparent;
      font-size: 1rem;
    }

    .material-form label {
      position: absolute;
      top: 0.8rem;
      left: 0;
      transition: 0.2s;
      color: #999;
      font-size: 1rem;
      pointer-events: none;
    }

    .bar {
      position: relative;
      display: block;
      width: 100%;
    }

    .bar::before {
      content: '';
      height: 2px;
      width: 0;
      bottom: 0;
      position: absolute;
      background: #0B132A;
      transition: 0.2s;
      left: 50%;
    }

    input:focus~.bar::before {
      width: 100%;
      left: 0;
    }

    input:focus~label,
    input:valid~label {
      top: -1rem;
      font-size: 0.8rem;
      color: #0B132A;
    }

    .material-form button {
      width: 100%;
      padding: 0.9rem;
      background: #0B132A;
      color: white;
      border: none;
      border-radius: 6px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      cursor: pointer;
      box-shadow: 0 3px 6px rgba(33, 150, 243, 0.25);
      transition: 0.3s;
      font-size: 1rem;
      margin-top: 1rem;
    }

    .material-form button:hover {
      box-shadow: 0 5px 10px rgba(33, 150, 243, 0.35);
      transform: translateY(-2px);
    }

    .login-text {
      margin-top: 1rem;
      font-size: 0.95rem;
    }

    .login-text a {
      color: #2196F3;
      font-weight: bold;
      text-decoration: none;
    }

    .login-text a:hover {
      color: #0b78d1;
    }

    .error {
      background: #ffcdd2;
      color: #b71c1c;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 1rem;
      text-align: left;
    }
  </style>
</head>

<body>

  <form class="material-form" method="POST" action="">

    <h2>Register</h2>

    <?php if ($errors): ?>
      <div class="error">
        <?php foreach ($errors as $e) echo "<p style='margin:0'>$e</p>"; ?>
      </div>
    <?php endif; ?>

    <div class="input-field">
      <input type="text" id="username" name="username" required
        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      <label for="username">Username</label>
      <span class="bar"></span>
    </div>

    <div class="input-field">
      <input type="password" id="password" name="password" required>
      <label for="password">Password</label>
      <span class="bar"></span>
    </div>

    <div class="input-field">
      <input type="password" id="confirm_password" name="confirm_password" required>
      <label for="confirm_password">Confirm Password</label>
      <span class="bar"></span>
    </div>

    <button type="submit">Register</button>

    <p class="login-text">
      Already have an account? <a href="login.php">Login</a>
    </p>

  </form>
</body>

</html>