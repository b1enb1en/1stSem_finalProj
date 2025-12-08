<?php
session_start();
require_once 'db_init.php';
$db = getDB();

if (empty($_SESSION['user_id'])) header('Location: login.php');

$message = '';
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
  <title>Profile</title>
  <link rel="stylesheet" href="../assets/css/db_styles.css">
  <style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Segoe UI", sans-serif;
}

body {
  background: #f5f7fa;
}

.dashboard {
  display: flex;
  min-height: 100vh;
}
    .nav a {
      color: #ccc;
      margin-left: 15px;
      text-decoration: none;
      font-weight: bold;
    }

    .nav a:hover,
    .nav a.active {
      color: #fff;
      text-decoration: underline;
    }

    .card {
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 15px;
      margin: 10px 0;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      color: white;
      font-weight: bold;
    }

    .btn-logout {
      background: #6c757d;
    }

    .btn-danger {
      background: #dc3545;
    }
  </style>
</head>

<body>
  <div class="dashboard">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <span class="logo">Reserba Silid</span>
      </div>

      <nav class="nav-links">
        <a href="dashboard.php"><i class="bi bi-house"></i><span>Dashboard</span></a>
        <a href="scheduler.php"><i class="bi bi-door-open"></i><span>Schedules</span></a>
        <a href="manage_schedules.php"><i class="bi bi-calendar-event"></i><span> Manager Schedule</span></a>
        <a href="profile.php" class="active"><i class="bi bi-gear"></i><span>Settings</span></a>
      </nav>
    </aside>

    <!-- CONTENT -->
    <div class="content">

      <div class="card">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
    <p>Manage your account settings here.</p>
    <hr>

    <form method="post">
      <input type="hidden" name="action" value="logout">
      <button class="btn btn-logout">Logout</button>
    </form>

    <form method="post" onsubmit="return confirm('Are you sure? This cannot be undone.');">
      <input type="hidden" name="action" value="delete_account">
      <button class="btn btn-danger">Delete My Account</button>
    </form>
  </div>
      </div>
    </div>
  </body>

  </html>