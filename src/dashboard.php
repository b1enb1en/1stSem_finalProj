<?php
session_start();
require_once 'db_init.php';
$db = getDB();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];

// --- REAL TIME LOGIC ---
// We get the current Day (e.g., "Monday") and current Time (e.g., "10:30")
date_default_timezone_set('Asia/Manila'); // Set your timezone
$current_day = date('l');
$current_time = date('H:i');
$current_date = date('Y-m-d');

// SQL Logic:
// 1. Check for Fixed Class (Type='fixed', Day matches, Time overlaps)
// 2. Check for One-time Booking (Type='booking', Date+Time overlaps)
$sql = "SELECT r.*,
    (SELECT title FROM schedules s 
     WHERE s.room_id = r.id 
     AND (
        (type = 'fixed' AND day_of_week = :day AND :time >= start_time AND :time < end_time)
        OR
        (type = 'booking' AND :date = date(start_time) AND :time >= time(start_time) AND :time < time(end_time))
     )
     LIMIT 1) as event_title,
     
    (SELECT instructor FROM schedules s 
     WHERE s.room_id = r.id 
     AND (
        (type = 'fixed' AND day_of_week = :day AND :time >= start_time AND :time < end_time)
        OR
        (type = 'booking' AND :date = date(start_time) AND :time >= time(start_time) AND :time < time(end_time))
     )
     LIMIT 1) as instructor

    FROM rooms r ORDER BY r.name ASC";

$stmt = $db->prepare($sql);
$stmt->execute([':day' => $current_day, ':time' => $current_time, ':date' => $current_date]);
$rooms = $stmt->fetchAll();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard - Room Tracker</title>

    <link rel="stylesheet" href="../assets/css/db_styles.css">
    <!-- <style>
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
            margin-top: 10px;
        }

        .available {
            background: #28a745;
        }

        .occupied {
            background: #dc3545;
        }
    </style> -->
</head>

<body>
    <div class="dashboard">

        <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="logo">Reserba Silid</span>
            <!-- <button class="toggle-btn" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button> -->
        </div>

        <nav class="nav-links">
            <a href="dashboard.php"><i class="bi bi-house"></i><span>Dashboard</span></a>
            <a href="scheduler.php"><i class="bi bi-door-open"></i><span>Schedules</span></a>
            <a href="manage_schedules.php"><i class="bi bi-calendar-event"></i><span> Manage Schedule</span></a>
            <a href="profile.php"><i class="bi bi-gear"></i><span>Settings</span></a>
        </nav>
    </aside>


<!-- CONTENTS -->        
 <div class="card">
    <h2>4th Floor Rooms</h2>
    <p>Select a room to view its calendar and manage schedules.</p>
    <hr>
    <div class="grid">
        <?php foreach ($rooms as $r): ?>
            <div class="card1" style="<?= $r['event_title'] ? 'border-top: 5px solid #dc3545' : 'border-top: 5px solid #28a745' ?>">
                <h2 style="color: #333; margin:0 0 10px 0;"><?= htmlspecialchars($r['name']) ?></h2>

                <?php if ($r['event_title']): ?>
                    <span class="status-badge occupied">Occupied</span>
                    <hr style="border:0; border-top:1px solid #eee; margin: 15px 0;">
                    <div style="text-align:left;">
                        <strong style="display:block; color:#333; font-size:1.1em;"><?= htmlspecialchars($r['event_title']) ?></strong>
                        <small style="color:#666;">Instr. <?= htmlspecialchars($r['instructor']) ?></small>
                    </div>
                <?php else: ?>
                    <span class="status-badge available">Available</span>
                    <p style="margin-top:20px; color:#999; font-size:0.9em;">Room is free.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</div>

<!--  -->
</body>

</html>