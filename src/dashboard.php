<?php
session_start();
require_once 'db_init.php';
$db = getDB();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


$user_id = (int)$_SESSION['user_id'];
$username = $_SESSION['username'];

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// HANDLE POST ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_schedule') {
            $room_id = (int)($_POST['room_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $start = $_POST['start_time'] ?? '';
            $end = $_POST['end_time'] ?? '';
            $type = ($_POST['type'] === 'fixed') ? 'fixed' : 'booking';

            if ($room_id && $title && $start && $end) {
                // Determine if this is a "Fixed" room (Classroom)
                if ($type === 'fixed') {
                    $db->prepare('UPDATE rooms SET status = "fixed" WHERE id = :r')->execute([':r' => $room_id]);
                }

                $stmt = $db->prepare('INSERT INTO schedules (room_id, title, start_time, end_time, type, status, created_by, notes) VALUES (:r, :t, :s, :e, :type, :st, :u, :n)');
                $stmt->execute([
                    ':r' => $room_id,
                    ':t' => $title,
                    ':s' => $start,
                    ':e' => $end,
                    ':type' => $type,
                    ':st' => 'approved',
                    ':u' => $user_id,
                    ':n' => trim($_POST['notes'] ?? '')
                ]);

                $_SESSION['message'] = "Schedule created successfully."; //save natin yung success message sa Session 
                header("Location: dashboard.php"); //nireredirect nito tayo dun sa updated na dashboard para hindi na ulit mag run ung POST data
                exit;
            }
        } elseif ($action === 'delete_schedule') {
            $sid = (int)($_POST['schedule_id'] ?? 0);
            if ($sid) {
                $db->prepare('DELETE FROM schedules WHERE id = :sid')->execute([':sid' => $sid]);
                $message = "Schedule removed.";
            }
        } elseif ($action === 'delete_account') {
            $db->prepare('DELETE FROM users WHERE id = :uid')->execute([':uid' => $user_id]);
            session_destroy();
            header('Location: index.html');
            exit;
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// --- REAL TIME LOGIC ---
$now = date('Y-m-d\TH:i');

// 1. Fetch Rooms + Check if they are currently occupied
$sqlRooms = "SELECT r.*, 
    (SELECT title FROM schedules s 
     WHERE s.room_id = r.id 
     AND :now >= s.start_time 
     AND :now <= s.end_time 
     LIMIT 1) as current_event
    FROM rooms r ORDER BY r.name ASC";
$stmt = $db->prepare($sqlRooms);
$stmt->execute([':now' => $now]);
$rooms = $stmt->fetchAll();

// 2. Fetch All Future Schedules
$schedules = $db->query('
    SELECT s.*, r.name AS room_name, u.username AS creator 
    FROM schedules s 
    LEFT JOIN rooms r ON s.room_id = r.id 
    LEFT JOIN users u ON s.created_by = u.id 
    ORDER BY s.start_time ASC
')->fetchAll();
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Dashboard - Room Tracker</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            padding: 20px;
            max-width: 1200px;  
            margin: auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #343a40;
            color: #fff;
            padding: 16px;
            border-radius: 8px;
        }

        h1,
        h2,
        h3 {
            margin: 0;
        }

        .nav a {
            color: #ccc;
            margin-left: 15px;
            text-decoration: none;
            font-weight: 500;
        }

        .nav a:hover {
            color: #fff;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            border: 1px solid #e3e6f0;
            padding: 20px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Forms & Labels */
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 0.9em;
            color: #555;
        }

        input,
        select,
        textarea {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            height: 80px;
            resize: vertical;
        }

        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #f1f3f5;
            color: #495057;
        }

        th,
        td {
            border-bottom: 1px solid #eee;
            text-align: center;
            padding: 12px 8px;
            font-size: 0.9em;
        }

        .card1 {
            margin-top: 4rem;
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Buttons & Badges */
        button {
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
        }

        .btn-primary {
            background: #007bff;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
        }

        .bg-success {
            background-color: #28a745;
        }

        .bg-danger {
            background-color: #dc3545;
        }

        .bg-warning {
            background-color: #ffc107;
            color: #333;
        }

        .alert {
            padding: 15px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>

<body>

    <header>
        <div>
            <h1>Room Tracker</h1>
            <small>Logged in as: <strong><?= htmlspecialchars($username) ?></strong></small>
        </div>
        <div class="nav">
            <a href="#rooms">Status</a>
            <a href="booking.php">Booking</a>
            <a href="account.php">Profile</a>
            <a href="logout.php" style="color:#ff6b6b">Logout</a>
        </div>
    </header>

    <div class="card" style="margin-top:20px;">
        <h3>Upcoming Events & Classes</h3>
        <table>
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Title</th>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Notes</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $s): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['room_name']) ?></strong></td>
                        <td><?= htmlspecialchars($s['title']) ?></td>
                        <td>
                            <?= date('M d, h:i A', strtotime($s['start_time'])) ?> <br>
                            <span style="color:#666; font-size:0.9em">to <?= date('h:i A', strtotime($s['end_time'])) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $s['type'] === 'fixed' ? 'bg-warning' : 'bg-primary' ?>">
                                <?= strtoupper($s['type']) ?>
                            </span>
                        </td>
                        <td><small><?= htmlspecialchars($s['notes'] ?? '-') ?></small></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Remove this schedule?');">
                                <input type="hidden" name="action" value="delete_schedule">
                                <input type="hidden" name="schedule_id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn-danger">X</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        
<!-- ROOM STATUS -->
    <div class="grid">
        <div class="card1" id="rooms">
            <h2>Room Status</h2>
            <p style="color:#666; font-size:0.9em; margin-bottom:15px;">Real-time availability for EFS 401 - EFS 410.</p>
            <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;">

            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Current Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $r): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($r['name']) ?></strong>
                            </td>
                            <td>
                                <?php if (!empty($r['current_event'])): ?>
                                    <span class="badge bg-danger">Occupied</span>
                                    <div style="font-size:0.8em; margin-top:2px;">
                                        <?= htmlspecialchars($r['current_event']) ?>
                                    </div>
                                <?php elseif ($r['status'] === 'fixed'): ?>
                                    <span class="badge bg-warning">Classroom</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="height:50px"></div>
</body>

</html>