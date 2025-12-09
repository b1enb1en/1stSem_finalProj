<?php
session_start();
require_once 'db_init.php';
$db = getDB();
if (empty($_SESSION['user_id'])) header('Location: login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'];
  if ($action === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
  } elseif ($action === 'delete_account') {
    $db->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $_SESSION['user_id']]);
    session_destroy();
    header('Location: register.php');
    exit;
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
  <link rel="stylesheet" href="/assets/css/styles.css">
  <script src="/assets/css/script.js" defer></script>
</head>

<body>

  <nav class="sidebar">
    <div class="sidebar-header">
      <div class="logo">Room Tracker</div>
      <button class="toggle-btn">&times;</button>
    </div>
    <ul class="nav-links">
      <li><a href="dashboard.php"><span class="icon">📊</span> Dashboard</a></li>
      <li><a href="scheduler.php"><span class="icon">📅</span> Scheduler</a></li>
      <li><a href="manage_schedules.php"><span class="icon">✏️</span> Edit Classes</a></li>
      <li><a href="profile.php" class="active"><span class="icon">👤</span> Profile</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <div class="mobile-header">
      <button class="toggle-btn" style="color:#333; font-size:1.5rem;">&#9776;</button>
      <strong style="font-size:1.2rem;">Profile</strong>
    </div>

    <div class="box" style="max-width: 500px; margin: auto; text-align: center;">
      <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
      <p>Manage your account settings here.</p>
      <hr style="margin: 20px 0; border:0; border-top:1px solid #eee;">

      <form method="post" class="confirm-logout">
        <input type="hidden" name="action" value="logout">
        <button class="btn-logout" style="width:100%; margin-bottom:10px; padding:12px;">Logout</button>
      </form>

      <form method="post" class="confirm-delete">
        <input type="hidden" name="action" value="delete_account">
        <button class="btn-del" style="width:100%; padding:12px;">Delete My Account</button>
      </form>
    </div>
  </main>
</body>

</html>