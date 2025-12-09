<?php
session_start();
require_once 'db_init.php';
$db = getDB();
if (empty($_SESSION['user_id'])) header('Location: login.php');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_POST['action'] === 'add_fixed') {
    $stmt = $db->prepare("INSERT INTO schedules (room_id, title, instructor, day_of_week, start_time, end_time, type, created_by) VALUES (:rid, :title, :instr, :day, :start, :end, 'fixed', :uid)");
    $stmt->execute([
      ':rid' => $_POST['room_id'],
      ':title' => $_POST['title'],
      ':instr' => $_POST['instructor'],
      ':day' => $_POST['day_of_week'],
      ':start' => $_POST['start_time'],
      ':end' => $_POST['end_time'],
      ':uid' => $_SESSION['user_id']
    ]);
    $message = "Class added successfully.";
  } elseif ($_POST['action'] === 'delete') {
    $db->prepare("DELETE FROM schedules WHERE id = :id")->execute([':id' => $_POST['id']]);
    $message = "Class removed.";
  }
}
$schedules = $db->query("SELECT s.*, r.name as room_name FROM schedules s JOIN rooms r ON s.room_id = r.id WHERE type='fixed' ORDER BY room_name, day_of_week")->fetchAll();
$rooms = $db->query("SELECT * FROM rooms ORDER BY name")->fetchAll();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Classes</title>
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
      <li><a href="manage_schedules.php" class="active"><span class="icon">✏️</span> Edit Classes</a></li>
      <li><a href="profile.php"><span class="icon">👤</span> Profile</a></li>
    </ul>
  </nav>

  <main class="main-content">
    <div class="mobile-header">
      <button class="toggle-btn" style="color:#333; font-size:1.5rem;">&#9776;</button>
      <strong style="font-size:1.2rem;">Edit Schedules</strong>
    </div>

    <?php if ($message): ?><div class="alert"><?= $message ?></div><?php endif; ?>

    <div class="box">
      <h3>Add Semester Class (Fixed)</h3>
      <form method="post" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px;">
        <input type="hidden" name="action" value="add_fixed">

        <select name="room_id" required>
          <?php foreach ($rooms as $r): ?><option value="<?= $r['id'] ?>"><?= $r['name'] ?></option><?php endforeach; ?>
        </select>
        <input type="text" name="title" placeholder="Subject / Title" required>
        <input type="text" name="instructor" placeholder="Instructor Name" required>

        <select name="day_of_week" required>
          <option value="Monday">Monday</option>
          <option value="Tuesday">Tuesday</option>
          <option value="Wednesday">Wednesday</option>
          <option value="Thursday">Thursday</option>
          <option value="Friday">Friday</option>
          <option value="Saturday">Saturday</option>
        </select>
        <input type="time" name="start_time" required>
        <input type="time" name="end_time" required>

        <div style="grid-column: 1 / -1;">
          <button type="submit" class="btn-add" style="width:100%;">Save Class</button>
        </div>
      </form>
    </div>

    <div class="box">
      <h3>Current Fixed Schedules</h3>
      <div style="overflow-x:auto;">
        <table>
          <thead>
            <tr>
              <th>Room</th>
              <th>Day</th>
              <th>Time</th>
              <th>Subject</th>
              <th>Instructor</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($schedules as $s): ?>
              <tr>
                <td><?= htmlspecialchars($s['room_name']) ?></td>
                <td><?= $s['day_of_week'] ?></td>
                <td><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['end_time'])) ?></td>
                <td><?= htmlspecialchars($s['title']) ?></td>
                <td><?= htmlspecialchars($s['instructor']) ?></td>
                <td>
                  <form method="post" class="confirm-delete">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <button class="btn-del">X</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>

</html>