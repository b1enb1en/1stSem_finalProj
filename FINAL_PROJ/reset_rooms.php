<?php
// reset_rooms.php
require_once 'db_init.php';
$db = getDB();

try {
  // 1. Delete all schedules (because they depend on rooms)
  $db->exec("DELETE FROM schedules");

  // 2. Delete all rooms
  $db->exec("DELETE FROM rooms");

  // 3. Reset the ID counter so the new rooms start at ID 1
  $db->exec("DELETE FROM sqlite_sequence WHERE name='rooms'");

  echo "<h1 style='color:green'>Success!</h1>";
  echo "<p>All rooms have been deleted. The database is now empty.</p>";
  echo "<p>When you click the link below, the system will automatically create <strong>EFS 401 - EFS 410</strong>.</p>";
  echo "<br>";
  echo "<a href='dashboard.php' style='padding:10px 20px; background:blue; color:white; text-decoration:none;'>Go to Dashboard & Create Rooms</a>";
} catch (Exception $e) {
  echo "Error: " . $e->getMessage();
}
