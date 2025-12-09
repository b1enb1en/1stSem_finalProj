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
  $stmt->execute([$_POST['room_id'], $_POST['title'], $_POST['instructor'], $_SESSION['username'], $_POST['full_start'], $_POST['full_end'], $_SESSION['user_id']]);
  header("Location: scheduler.php?msg=booked");
  exit;
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scheduler</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="/assets/css/db_styles.css">
  <link rel="stylesheet" href="/assets/css/sidebar.css">
  <script src="/assets/css/script.js" defer></script>
</head>

<body>

    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo">Room Tracker</div>
            <button class="toggle-btn">&times;</button>
        </div>
        <ul class="nav-links">
            <li>
            <a href="dashboard.php">
                <i class="bi bi-house"></i> Dashboard
            </a>
            </li>

            <li>
            <a href="scheduler.php" class="active">
                <i class="bi bi-calendar-week"></i> Scheduler
            </a>
            </li>

            <li>
            <a href="manage_schedules.php">
                <i class="bi bi-pencil-square"></i> Edit Classes
            </a>
            </li>

            <li>
            <a href="profile.php">
                <i class="bi bi-person-circle"></i> Profile
            </a>
            </li>

        </ul>
    </nav>

  <main class="main-content">
    <div class="mobile-header">
      <button class="toggle-btn" style="color:#333; font-size:1.5rem;">&#9776;</button>
      <strong style="font-size:1.2rem;">Scheduler</strong>
    </div>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert">Booking Successful!</div>
    <?php endif; ?>

    <div class="box">
      <h3>Find Available Room (One-time Event)</h3>
      <form method="get" style="display:flex; flex-wrap:wrap; gap:10px;">
        <div style="flex:1; min-width:150px;">
          <label>Date</label>
          <input type="date" name="search_date" required value="<?= $_GET['search_date'] ?? '' ?>">
        </div>
        <div style="flex:1; min-width:120px;">
          <label>Start</label>
          <input type="time" name="start_time" required value="<?= $_GET['start_time'] ?? '' ?>">
        </div>
        <div style="flex:1; min-width:120px;">
          <label>End</label>
          <input type="time" name="end_time" required value="<?= $_GET['end_time'] ?? '' ?>">
        </div>
        <div style="width:100%;">
          <button class="btn-primary">Search Availability</button>
        </div>
      </form>
    </div>

    <?php if (isset($_GET['search_date'])): ?>
      <div class="box">
        <h3>Results for <?= htmlspecialchars($_GET['search_date']) ?></h3>
        <?php if (empty($results)): ?>
          <p>No rooms available for this time slot.</p>
        <?php else: ?>
          <div class="grid">
            <?php foreach ($results as $r): ?>
              <div class="card" style="border-top: 4px solid #28a745;">
                <strong><?= htmlspecialchars($r['name']) ?></strong>
                <p style="font-size:0.8em; color:green;">Available</p>
                <form method="post">
                  <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                  <input type="hidden" name="full_start" value="<?= $_GET['search_date'] . ' ' . $_GET['start_time'] ?>">
                  <input type="hidden" name="full_end" value="<?= $_GET['search_date'] . ' ' . $_GET['end_time'] ?>">
                  <input type="text" name="title" placeholder="Event Title" required>
                  <input type="text" name="instructor" placeholder="Instructor / Booker Name" required>
                  <button class="btn-primary" style="margin-top:10px; width:100%;">Book Now</button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </main>
</body>

</html>