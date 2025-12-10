<?php
// db_init.php
function getDB()
{
    static $db = null;
    if ($db !== null) return $db;

    if (!is_dir(__DIR__ . '/data')) {
        mkdir(__DIR__ . '/data', 0755, true);
    }

    $dbPath = __DIR__ . '/data/rooms.db';

    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA foreign_keys = ON');

        $db->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        $db->exec('CREATE TABLE IF NOT EXISTS rooms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            status TEXT NOT NULL DEFAULT "available"
        )');

        $db->exec('CREATE TABLE IF NOT EXISTS schedules (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            room_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            instructor TEXT,
            start_time TEXT NOT NULL,
            end_time TEXT NOT NULL,
            day_of_week TEXT,
            type TEXT NOT NULL DEFAULT "booking",
            status TEXT NOT NULL DEFAULT "approved",
            created_by INTEGER,
            notes TEXT,
            FOREIGN KEY(room_id) REFERENCES rooms(id) ON DELETE CASCADE,
            FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE CASCADE
        )');

        // Auto-generate rooms if empty
        $count = $db->query('SELECT COUNT(*) FROM rooms')->fetchColumn();
        if ($count == 0) {
            $stmt = $db->prepare("INSERT INTO rooms (name, description) VALUES (:name, 'Standard Classroom')");
            for ($i = 401; $i <= 410; $i++) {
                $stmt->execute([':name' => "EFS $i"]);
            }
        }

        return $db;

    } catch (PDOException $e) {
        die('Database Error: ' . $e->getMessage());
    }
}
