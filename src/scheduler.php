<?php
session_start();
require_once 'db_init.php';
$db = getDB();
if (empty($_SESSION['user_id'])) header('Location: login.php');

$results = [];
if (isset($_GET['search_date'])) {
  $date = $_GET['search_date'];
  $day = date('l', strtotime($date));
  $start = $_GET['start_time'];
  $end = $_GET['end_time'];
  $s_start = "$date $start";
  $s_end = "$date $end";

  // Complex Query: Find rooms NOT occupied by Fixed OR Booking
  $sql = "SELECT * FROM rooms WHERE id NOT IN (
        SELECT room_id FROM schedules WHERE 
        (type='fixed' AND day_of_week = :day AND (:start < end_time AND :end > start_time))
        OR 
        (type='booking' AND (:s_start < end_time AND :s_end > start_time))
    )";
  $stmt = $db->prepare($sql);
  $stmt->execute([':day' => $day, ':start' => $start, ':end' => $end, ':s_start' => $s_start, ':s_end' => $s_end]);
  $results = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $db->prepare("INSERT INTO schedules (room_id, title, instructor, start_time, end_time, type, created_by) VALUES (?, ?, ?, ?, ?, 'booking', ?)");
  $stmt->execute([$_POST['room_id'], $_POST['title'], $_SESSION['username'], $_POST['full_start'], $_POST['full_end'], $_SESSION['user_id']]);
  $msg = "Booked!";
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Scheduler</title>
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

    .box {
      background: white;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .room-card {
      border: 1px solid #28a745;
      padding: 15px;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #e6ffed;
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
        <a href="scheduler.php" class="active"><i class="bi bi-door-open"></i><span>Schedules</span></a>
        <a href="manage_schedules.php"><i class="bi bi-calendar-event"></i><span> Manager Schedule</span></a>
        <a href="profile.php"><i class="bi bi-gear"></i><span>Settings</span></a>
      </nav>
    </aside>

    <!-- CONTENT -->
    <div class="content">

      <div class="box">
    <h3>Find Available Room</h3>
    <form method="get">
      <input type="date" name="search_date" required value="<?= $_GET['search_date'] ?? '' ?>">
      <input type="time" name="start_time" required value="<?= $_GET['start_time'] ?? '' ?>">
      <input type="time" name="end_time" required value="<?= $_GET['end_time'] ?? '' ?>">
      <button style="padding:5px 15px; background:#007bff; color:white; border:none; border-radius:4px;">Search</button>
    </form>
  </div>

  <?php if (isset($_GET['search_date'])): ?>
    <div class="box">
      <h3>Results for <?= $_GET['search_date'] ?> (<?= $_GET['start_time'] ?> - <?= $_GET['end_time'] ?>)</h3>
      <?php foreach ($results as $r): ?>
        <div class="room-card">
          <strong><?= $r['name'] ?></strong>
          <form method="post">
            <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
            <input type="hidden" name="full_start" value="<?= $_GET['search_date'] . ' ' . $_GET['start_time'] ?>">
            <input type="hidden" name="full_end" value="<?= $_GET['search_date'] . ' ' . $_GET['end_time'] ?>">
            <input type="text" name="title" placeholder="Event Title" required>
            <button style="background:#28a745; color:white; border:none; padding:5px 10px; cursor:pointer;">Book</button>
          </form>
        </div>
      <?php endforeach; ?>
      <?php if (empty($results)) echo "<p>No rooms available.</p>"; ?>
    </div>
  <?php endif; ?>
    </div>
  </div>
</body>

</html>