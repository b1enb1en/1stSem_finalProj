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

// Fetch all fixed schedules
$schedules = $db->query("SELECT s.*, r.name as room_name FROM schedules s JOIN rooms r ON s.room_id = r.id WHERE type='fixed' ORDER BY room_name, day_of_week")->fetchAll();
$rooms = $db->query("SELECT * FROM rooms ORDER BY name")->fetchAll();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Manage Schedules</title>
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

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }

    th,
    td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: left;
    }

    input,
    select {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .btn {
      padding: 8px 15px;
      cursor: pointer;
      color: white;
      border: none;
      border-radius: 4px;
    }

    .btn-add {
      background: #007bff;
    }

    .btn-del {
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
        <a href="manage_schedules.php" class="active"><i class="bi bi-calendar-event"></i><span> Manager Schedule</span></a>
        <a href="profile.php"><i class="bi bi-gear"></i><span>Settings</span></a>
      </nav>
    </aside>

    <!-- CONTENT -->
    <div class="content">

      <?php if ($message): ?><div style="background:#d4edda; color:#155724; padding:10px; margin-bottom:20px;"><?= $message ?></div><?php endif; ?>

      <div style="background:white; padding:20px; margin-bottom:30px; border-radius:8px;">
    <h3>Add Semester Class (Fixed)</h3>
    <form method="post" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px;">
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

      <button type="submit" class="btn btn-add" style="grid-column: 1 / -1;">Save Class</button>
    </form>
  </div>

  <h3>Current Fixed Schedules</h3>
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
            <form method="post" onsubmit="return confirm('Delete?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $s['id'] ?>">
              <button class="btn btn-del">X</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
    </div>
  </div>
</body>

</html>