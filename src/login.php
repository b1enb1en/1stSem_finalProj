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
    // 1. Fetch the user
    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // 2. Check: Does user exist? AND Is password correct?
    // We combine them into one IF statement.
    if ($user && password_verify($password, $user['password_hash'])) {
      // SUCCESS: Log them in
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      header('Location: dashboard.php');
      exit;
    } else {
      // FAILED: Generic error message (More secure)
      $errors[] = 'Incorrect username or password.';
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
      color: #2196F3;
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

    .register-text {
      margin-top: 1rem;
      font-size: 0.95rem;
    }

    .register-text a {
      color: #182d6dff;
      font-weight: bold;
      text-decoration: none;
    }

    .register-text a:hover {
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

    <h2>Login</h2>

    <?php if ($errors): ?>
      <div class="error">
        <?php foreach ($errors as $e) echo "<p style='margin:0'>$e</p>"; ?>
      </div>
    <?php endif; ?>

    <div class="input-field">
      <input type="text" id="username" name="username" required
        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      <label for="username" style="cursor: text;">Username</label>
      <span class="bar"></span>
    </div>

    <div class="input-field">
      <input type="password" id="password" name="password" required>
      <label for="password" style="cursor: text;">Password</label>
      <span class="bar"></span>
    </div>

    <button type="submit">Login</button>

    <p class="register-text">
      Don't have an account? <a href="register.php">Register</a>
    </p>

  </form>

</body>

</html>