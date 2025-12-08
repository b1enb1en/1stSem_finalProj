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

// VIEW ROOMS' SCHEDULES
$view_room_id = isset($_GET['view_room']) ? (int)$_GET['view_room'] : null;
$selected_room = null;
$room_schedules = [];

// kapag sinelect ni user yung room, kukunin ng block of code na ito yung details at specific schedules dun sa room na yun
if ($view_room_id) {
    $stmt = $db->prepare("SELECT * FROM rooms WHERE id = :id");
    $stmt->execute([':id' => $view_room_id]);
    $selected_room = $stmt->fetch();

    // Fetch schedules for this specific room
    $stmt = $db->prepare("
        SELECT s.*, u.username as instructor_name 
        FROM schedules s 
        LEFT JOIN users u ON s.created_by = u.id
        WHERE room_id = :rid 
        ORDER BY start_time ASC
    ");
    $stmt->execute([':rid' => $view_room_id]);
    $room_schedules = $stmt->fetchAll();
}

// HANDLE POST ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_schedule' || $action === 'update_schedule') {
            $room_id = (int)($_POST['room_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $start = $_POST['start_time'] ?? '';
            $end = $_POST['end_time'] ?? '';
            $type = ($_POST['type'] === 'fixed') ? 'fixed' : 'booking';
            $notes = trim($_POST['notes'] ?? '');

            // VALIDATION
            if ($room_id && $title && $start && $end) {
                // UPDATE ROOM STATUS
                if ($type === 'fixed') {
                    $db->prepare('UPDATE rooms SET status = "fixed" WHERE id = :r')->execute([':r' => $room_id]);
                }

                if ($action === 'add_schedule') {
                    $stmt = $db->prepare('INSERT INTO schedules (room_id, title, start_time, end_time, type, status, created_by, notes) VALUES (:r, :t, :s, :e, :type, "approved", :u, :n)');
                    $stmt->execute([':r' => $room_id, ':t' => $title, ':s' => $start, ':e' => $end, ':type' => $type, ':u' => $user_id, ':n' => $notes]);
                    $_SESSION['message'] = "Schedule added successfully.";
                } elseif ($action === 'update_schedule') {
                    $schedule_id = (int)$_POST['schedule_id'];
                    $stmt = $db->prepare('UPDATE schedules SET title=:t, start_time=:s, end_time=:e, type=:type, notes=:n WHERE id=:id');
                    $stmt->execute([':t' => $title, ':s' => $start, ':e' => $end, ':type' => $type, ':n' => $notes, ':id' => $schedule_id]);
                    $_SESSION['message'] = "Schedule updated successfully.";
                }

                // Redirect back to the SAME room view
                header("Location: dashboard.php?view_room=" . $room_id);
                exit;
            }
        } elseif ($action === 'delete_schedule') {
            $sid = (int)($_POST['schedule_id'] ?? 0);
            $rid = (int)($_POST['redirect_room_id'] ?? 0); // Need to know where to go back to
            if ($sid) {
                $db->prepare('DELETE FROM schedules WHERE id = :sid')->execute([':sid' => $sid]);
                $_SESSION['message'] = "Schedule removed.";
                header("Location: dashboard.php?view_room=" . $rid);
                exit;
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch all rooms for the main grid
$rooms = $db->query("SELECT * FROM rooms ORDER BY name ASC")->fetchAll();


// <!-- $stmt = $db->prepare('INSERT INTO schedules (room_id, title, start_time, end_time, type, status, created_by, notes) VALUES (:r, :t, :s, :e, :type, :st, :u, :n)');
//                 $stmt->execute([
//                     ':r' => $room_id,
//                     ':t' => $title,
//                     ':s' => $start,
//                     ':e' => $end,
//                     ':type' => $type,
//                     ':st' => 'approved',
//                     ':u' => $user_id,
//                     ':n' => trim($_POST['notes'] ?? '')
//                 ]);

//                 $_SESSION['message'] = "Schedule created successfully."; //save natin yung success message sa Session 
//                 header("Location: dashboard.php"); //nireredirect nito tayo dun sa updated na dashboard para hindi na ulit mag run ung POST data
//                 exit;
//             }
//         } elseif ($action === 'delete_schedule') {
//             $sid = (int)($_POST['schedule_id'] ?? 0);
//             if ($sid) {
//                 $db->prepare('DELETE FROM schedules WHERE id = :sid')->execute([':sid' => $sid]);
//                 $message = "Schedule removed.";
//             }
//         } elseif ($action === 'delete_account') {
//             $db->prepare('DELETE FROM users WHERE id = :uid')->execute([':uid' => $user_id]);
//             session_destroy();
//             header('Location: index.html');
//             exit;
//         }
//     } catch (Exception $e) {
//         $message = "Error: " . $e->getMessage();
//     }
// } -->

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

        /* New Room Grid Styles */
        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }

        .room-box {
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .room-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-color: #007bff;
        }

        .room-box h3 {
            margin: 0 0 5px 0;
            color: #007bff;
        }

        /* Layout for the Single Room View */
        .split-view {
            display: grid;
            grid-template-columns: 2fr 1fr;
            /* Calendar takes 2/3, Form takes 1/3 */
            gap: 20px;
        }

        @media (max-width: 800px) {
            .split-view {
                grid-template-columns: 1fr;
            }
        }

        /* Schedule List / Calendar Styling */
        .schedule-item {
            background: white;
            border-left: 5px solid #007bff;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .schedule-item.fixed {
            border-left-color: #ffc107;
        }

        /* Orange for fixed classes */

        .schedule-info h4 {
            margin: 0 0 5px 0;
        }

        .schedule-info p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }

        .schedule-actions button {
            padding: 5px 10px;
            font-size: 0.8em;
            margin-left: 5px;
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
            <a href="dashboard.php">All Rooms</a>
            <a href="account.php">Profile</a>
            <a href="logout.php" style="color:#ff6b6b">Logout</a>
        </div>
    </header>

    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <?php if (!$selected_room): ?>
        <div class="card">
            <h2>4th Floor Rooms</h2>
            <p>Select a room to view its calendar and manage schedules.</p>
            <hr>
            <div class="room-grid">
                <?php foreach ($rooms as $r): ?>
                    <a href="dashboard.php?view_room=<?= $r['id'] ?>" class="room-box">
                        <h3><?= htmlspecialchars($r['name']) ?></h3>
                        <small>Click to Manage</small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    <?php else: ?>
        <div style="margin-bottom: 20px;">
            <a href="dashboard.php" style="text-decoration:none;">&larr; Back to all rooms</a>
            <h2 style="margin-top:10px;">Managing: <?= htmlspecialchars($selected_room['name']) ?></h2>
        </div>

        <div class="split-view">

            <div class="card">
                <h3>Schedule / Events</h3>
                <p style="font-size:0.9em; color:#666;">Upcoming classes and bookings.</p>

                <?php if (count($room_schedules) === 0): ?>
                    <p><em>No schedules found for this room. It is currently vacant.</em></p>
                <?php endif; ?>

                <?php foreach ($room_schedules as $s): ?>
                    <?php
                    $dayOfWeek = date('l', strtotime($s['start_time']));
                    $dateStr = date('M d, Y', strtotime($s['start_time']));
                    $timeStr = date('h:i A', strtotime($s['start_time'])) . ' - ' . date('h:i A', strtotime($s['end_time']));
                    ?>
                    <div class="schedule-item <?= $s['type'] ?>">
                        <div class="schedule-info">
                            <h4><?= htmlspecialchars($s['title']) ?></h4>
                            <p><strong><?= $dayOfWeek ?>, <?= $dateStr ?></strong> | <?= $timeStr ?></p>
                            <p>Instructor: <?= htmlspecialchars($s['instructor_name'] ?? 'Unknown') ?></p>
                            <?php if ($s['notes']): ?>
                                <small style="color:#888;">Note: <?= htmlspecialchars($s['notes']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="schedule-actions">
                            <button type="button" class="btn-primary"
                                onclick='fillEditForm(<?= json_encode($s) ?>)'>
                                Edit
                            </button>

                            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this schedule?');">
                                <input type="hidden" name="action" value="delete_schedule">
                                <input type="hidden" name="schedule_id" value="<?= $s['id'] ?>">
                                <input type="hidden" name="redirect_room_id" value="<?= $selected_room['id'] ?>">
                                <button type="submit" class="btn-danger">X</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>


            <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?>
                </div><?php endif; ?>

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
            <div class="card" style="height: fit-content; position:sticky; top:20px;">
                <h3 id="form-title">Create Schedule</h3>
                <p id="form-desc" style="font-size:0.8em; color:#666;">Add a new class or booking.</p>

                <form method="post" id="schedule-form">
                    <input type="hidden" name="action" value="add_schedule" id="form-action">
                    <input type="hidden" name="room_id" value="<?= $selected_room['id'] ?>">
                    <input type="hidden" name="schedule_id" id="schedule_id_input">

                    <label>Title / Class Name</label>
                    <input type="text" name="title" id="inp_title" required placeholder="e.g. Science 101">

                    <label>Start Time</label>
                    <input type="datetime-local" name="start_time" id="inp_start" required>

                    <label>End Time</label>
                    <input type="datetime-local" name="end_time" id="inp_end" required>

                    <label>Type</label>
                    <select name="type" id="inp_type">
                        <option value="booking">One-time Booking</option>
                        <option value="fixed">Fixed Class</option>
                    </select>

                    <label>Notes</label>
                    <textarea name="notes" id="inp_notes"></textarea>

                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn-primary" id="btn-submit">Add Schedule</button>
                        <button type="button" id="btn-cancel" onclick="resetForm()" style="display:none; background:#6c757d; color:white; border:none;">Cancel Edit</button>
                    </div>
                </form>
            </div>

        </div>
    <?php endif; ?>

    <script>
        function fillEditForm(data) {
            // Change Form Title and Action
            document.getElementById('form-title').innerText = "Edit Schedule";
            document.getElementById('form-desc').innerText = "Update details for " + data.title;
            document.getElementById('form-action').value = "update_schedule";
            document.getElementById('btn-submit').innerText = "Update Schedule";
            document.getElementById('btn-cancel').style.display = "inline-block";

            // Fill inputs
            document.getElementById('schedule_id_input').value = data.id;
            document.getElementById('inp_title').value = data.title;
            document.getElementById('inp_type').value = data.type;
            document.getElementById('inp_notes').value = data.notes || '';

            // Format dates for datetime-local input (YYYY-MM-DDTHH:MM)
            document.getElementById('inp_start').value = data.start_time.replace(' ', 'T');
            document.getElementById('inp_end').value = data.end_time.replace(' ', 'T');

            // Scroll to form (for mobile)
            document.getElementById('schedule-form').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function resetForm() {
            document.getElementById('schedule-form').reset();
            document.getElementById('form-title').innerText = "Create Schedule";
            document.getElementById('form-desc').innerText = "Add a new class or booking.";
            document.getElementById('form-action').value = "add_schedule";
            document.getElementById('btn-submit').innerText = "Add Schedule";
            document.getElementById('btn-cancel').style.display = "none";
            document.getElementById('schedule_id_input').value = "";
        }
    </script>
</body>

</html>