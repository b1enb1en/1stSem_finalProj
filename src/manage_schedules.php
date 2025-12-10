<?php
session_start();
require_once 'db_init.php';
$db = getDB();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD A NEW SCHEDULE
    if ($action === 'add_class') {
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
        $_SESSION['message'] = "Class added successfully.";
        header("Location: manage_schedules.php");
        exit;

    // UPDATE EXISTING SCHEDULE
    } elseif ($action === 'update_class') {
        $stmt = $db->prepare("UPDATE schedules SET room_id=:rid, title=:title, instructor=:instr, day_of_week=:day, start_time=:start, end_time=:end WHERE id=:id");
        $stmt->execute([
            ':rid' => $_POST['room_id'],
            ':title' => $_POST['title'],
            ':instr' => $_POST['instructor'],
            ':day' => $_POST['day_of_week'],
            ':start' => $_POST['start_time'],
            ':end' => $_POST['end_time'],
            ':id' => $_POST['schedule_id']
        ]);
        $_SESSION['message'] = "Schedule updated successfully.";
        header("Location: manage_schedules.php");
        exit;

    // DELETE A SCHEDULE
    } elseif ($action === 'delete_schedule') {
        $stmt = $db->prepare("DELETE FROM schedules WHERE id = :id");
        $stmt->execute([':id' => $_POST['schedule_id']]);
        $_SESSION['message'] = "Schedule deleted.";
        header("Location: manage_schedules.php");
        exit;
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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/db_styles.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <script src="/assets/css/script.js"></script>

    <style>
        .box { margin: 20px 0; }
    </style>
</head>

<body>
    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo">Reserba Silid</div>
            <button class="toggle-btn">&times;</button>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
            <li><a href="scheduler.php"><i class="bi bi-calendar-week"></i> Scheduler</a></li>
            <li><a href="manage_schedules.php" class="active"><i class="bi bi-pencil-square"></i> Edit Classes</a></li>
            <li><a href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="mobile-header">
            <button class="toggle-btn" style="font-size:1.5rem;">&#9776;</button>
            <strong style="font-size:1.2rem;">Edit Schedules</strong>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div id="success-alert" class="alert-box">
                <?= $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="box">
            <h3>Add a Class Schedule</h3>
            <form method="post" class="schedule-grid-3col">
                <input type="hidden" name="action" value="add_class">

                <select name="room_id" required>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= $r['name'] ?></option>
                    <?php endforeach; ?>
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

                <div class="full-row">
                    <button class="btn-primary">Add</button>
                </div>
            </form>
        </div>

        <div class="box">
            <h3>Current Schedules</h3>
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
                                <td style="display:flex; gap:5px;">
                                    <button type="button" class="btn-primary" style="padding:5px 10px; font-size:0.8em;"
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)">Edit</button>

                                    <form method="post" class="confirm-delete">
                                        <input type="hidden" name="action" value="delete_schedule">
                                        <input type="hidden" name="schedule_id" value="<?= $s['id'] ?>">
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

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h3>Edit Schedule</h3>
            <form method="post" style="display:flex; flex-direction:column; gap:10px;">
                <input type="hidden" name="action" value="update_class">
                <input type="hidden" name="schedule_id" id="edit_id">

                <label>Room</label>
                <select name="room_id" id="edit_room_id" required>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= $r['name'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Title</label>
                <input type="text" name="title" id="edit_title" required>

                <label>Instructor</label>
                <input type="text" name="instructor" id="edit_instructor" required>

                <label>Day</label>
                <select name="day_of_week" id="edit_day" required>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                </select>

                <div style="display:flex; gap:10px;">
                    <div style="flex:1"><label>Start</label><input type="time" name="start_time" id="edit_start" required></div>
                    <div style="flex:1"><label>End</label><input type="time" name="end_time" id="edit_end" required></div>
                </div>

                <button type="submit" class="btn-primary" style="margin-top:10px;">Update Class</button>
            </form>
        </div>
    </div>

</body>
</html>
