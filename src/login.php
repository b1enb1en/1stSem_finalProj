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

    <div class="login-wrapper d-flex justify-content-center align-items-center">
        <div class="login-card p-4 shadow-lg">

            <h2 class="text-center mb-4 login-title">Welcome Back</h2>

            <?php if ($errors): ?>
            <div class="error-box">
              <?php foreach ($errors as $e) echo "<p style='margin:0'>$e</p>"; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-3">
                    <label class="form-label text-light">Username</label>
                    <input type="text" name="username" class="form-control universal-input" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-light">Password</label>
                    <input type="password" name="password" class="form-control universal-input" required>
                </div>

                <button type="submit" class="btn btn-register w-100 mt-2">Login</button>

                <p class="text-center mt-3 text-light">
                    Don't have an account?  
                    <a href="register.php" class="login-link">Register</a>
                </p>

            </form>

        </div>
    </div>

</body>

</html>
