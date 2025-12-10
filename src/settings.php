<?php
session_start();
require_once 'db_init.php';
$db = getDB();
if (empty($_SESSION['user_id'])) header('Location: login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action === 'logout') {
        session_destroy();
        header('Location: ../index.php');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/db_styles.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
</head>

<style>
    body {
        font-family: 'Inter', sans-serif;
        color: #333;
        margin: 0;
        padding: 0;
        display: flex;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        padding: 10px;
    }

    .header .info h2 {
        font-size: 26px;
        color: #1e293b;
        font-weight: 700;
    }

    .header .info p {
        color: #64748b;
        font-size: 14px;
    }

    .section-title {
        font-size: 20px;
        margin: 30px 0 10px;
        color: #1e293b;
        padding-left: 10px;
        font-weight: 600;
    }

    .settings-list {
        list-style: none;
        margin-top: 10px;
        font-size: 16px;
        color: #1e293b;
    }

    .settings-list li {
        background: #f1f5f9;
        padding: 15px 20px;
        margin-bottom: 10px;
        border-radius: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: 0.2s ease;
        cursor: pointer;
    }

    .settings-list li:hover,
    .btn-delete:hover {
        background: #e2e8f0;
    }

    .btn-delete {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f1f5f9;
        width: 100%;
        margin-bottom: 10px;
        border-radius: 12px;
        color: #dc2626 !important;
        transition: 0.2s ease;
        padding: 15px 20px;
    }

    .btn-delete::after {
        content: "➜";
        margin-left: auto;
        color: #1e293b;
    }

    button {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }

    .btn-logout {
        margin-top: 20px;
        width: 100%;
        padding: 15px;
        background: #dc2626;
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-logout:hover {
        background: #b91c1c;
    }

    .profile-main-content {
        flex-grow: 1;
        overflow: auto;
        padding: 20px;
        background: #0B132A;
    }

    @media (max-width: 882px) {
        .profile-container {
            padding: 20px;
            margin: 40px auto;
            width: auto;
            height: auto;
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 400px;
        }
    }
</style>

<body>

    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo">Reserba Silid</div>
            <button class="toggle-btn">&times;</button>
        </div>
        <ul class="nav-links">
            <li>
                <a href="dashboard.php">
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
                <a href="settings.php" class="active">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
        </ul>
    </nav>

    <main class="profile-main-content">
        <div class="mobile-header">
            <button class="toggle-btn" style="font-size:1.5rem;">&#9776;</button>
            <strong style="font-size:1.2rem;">Profile</strong>
        </div>

        <div class="profile-container">
            <div class="header">
                <div class="info">
                    <h2>Hello, <?php echo htmlspecialchars(ucwords(strtolower($_SESSION['username']))) ?></h2>
                    <p>Manage your account settings here.</p>
                </div>
            </div>

            <h3 class="section-title">Account Controls</h3>
            <div class="settings-list">
                <form method="post" class="confirm-delete">
                    <input type="hidden" name="action" value="delete_account">
                    <button class="btn-delete">Delete My Account</button>
                </form>
            </div>

            <form method="post" class="confirm-logout">
                <input type="hidden" name="action" value="logout">
                <button class="btn-logout">Log Out</button>
            </form>
        </div>
    </main>

    <script src="/assets/js/script.js"></script>
</body>

</html>
