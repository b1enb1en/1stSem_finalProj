<?php
session_start();
require_once 'db_init.php';
$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = strtolower(trim($_POST['username'] ?? ''));
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
      $errors[] = 'Username already exist.';
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
  <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/assets/css/index.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
  </style>

</head>

<body>

<div class="register-wrapper d-flex justify-content-center align-items-center">
  <div class="register-card p-4 shadow-lg">

    <h2 class="text-center mb-4 register-title">Create Account</h2>

    <?php if ($errors): ?>
      <div class="error-box">
          <?php foreach ($errors as $e) echo "<p style='margin:0'>$e</p>"; ?>
      </div>
    <?php endif; ?>

    <form class="material-form" method="POST" action="">

      <div class="mb-3">
        <label for="username" class="form-label text-light">Username</label>
        <input type="text" id="username" name="username" class="form-control universal-input" required 
        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label for="password" class="form-label text-light">Password</label>
        <input type="password" id="password" name="password" class="form-control universal-input" required>
      </div>

      <div class="mb-3">
        <label for="confirm_password" class="form-label text-light">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control universal-input" required>
      </div>

      <button type="submit" class="btn btn-register w-100 mt-3">Register</button>

      <p class="login-text text-center mt-3 text-light">
        Already have an account? <a href="login.php" class="register-link">Login</a>
      </p>

    </form>

  </div>
</div>

</body>

</html>