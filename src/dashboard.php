<?php
session_start();
require_once 'db_init.php';
$db = getDB();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
date_default_timezone_set('Asia/Manila');
$current_day = date('l');
$current_time = date('H:i');
$current_date = date('Y-m-d');

$sql = "SELECT r.*,
    (SELECT title FROM schedules s 
     WHERE s.room_id = r.id 
     AND (
        (type = 'fixed' AND day_of_week = :day AND :time >= start_time AND :time < end_time)
        OR
        (type = 'booking' AND :date = date(start_time) AND :time >= strftime('%H:%M', start_time) AND :time < strftime('%H:%M', end_time))
     ) LIMIT 1) as event_title,
    (SELECT instructor FROM schedules s 
     WHERE s.room_id = r.id 
     AND (
        (type = 'fixed' AND day_of_week = :day AND :time >= start_time AND :time < end_time)
        OR
        (type = 'booking' AND :date = date(start_time) AND :time >= time(start_time) AND :time < time(end_time))
     ) LIMIT 1) as instructor
    FROM rooms r ORDER BY r.name ASC";

$stmt = $db->prepare($sql);
$stmt->execute([':day' => $current_day, ':time' => $current_time, ':date' => $current_date]);
$rooms = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/db_styles.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">

    <script src="/assets/css/script.js" defer></script>
</head>

<body>

    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo">Reserba Silid</div>
            <button class="toggle-btn">&times;</button>
        </div>
        <ul class="nav-links">
            <li>
                <a href="dashboard.php" class="active">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </li>

            <li>
                <a href="scheduler.php">
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
            <strong style="font-size:1.2rem;">Dashboard</strong>
        </div>
        <div class="box">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h2 style="margin:0;">Room Status</h2>
                <small><?= $current_day ?>, <?= date('M d, Y') ?> | <?= date('h:i A') ?></small>
            </div>

            <div class="grid">
                <?php foreach ($rooms as $r): ?>
                    <div class="card" style="<?= $r['event_title'] ? 'border-top: 5px solid #dc3545' : 'border-top: 5px solid #28a745' ?>">
                        <h2 style="color: #333; margin:0 0 10px 0;"><?= htmlspecialchars($r['name'] ?? '') ?></h2>
                        <?php if ($r['event_title']): ?>
                            <span class="status-badge occupied">Occupied</span>
                            <hr style="border:0; border-top:1px solid #eee; margin: 15px 0;">
                            <div style="text-align:left;">
                                <strong style="display:block; color:#333;"><?= htmlspecialchars($r['event_title'] ?? '') ?></strong>
                                <small>Instr. <?= htmlspecialchars($r['instructor'] ?? '') ?></small>
                            </div>
                        <?php else: ?>
                            <span class="status-badge available">Available</span>
                            <p style="margin-top:20px; color:#999; font-size:0.9em;">Room is free.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>

</html>