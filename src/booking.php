<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
            <div class="card" id="schedules">
            <h2>Create Schedule / Booking</h2>
            <p style="color:#666; font-size:0.9em; margin-bottom:15px;">Book a room for an event or class.</p>
            <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;">

            <form method="post">
                <input type="hidden" name="action" value="add_schedule">

                <label>Select Room</label>
                <select name="room_id" required>
                    <option value="" disabled selected>-- Choose a Room --</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Event / Class Title</label>
                <input type="text" name="title" placeholder="e.g. Math 101 or Staff Meeting" required>

                <div style="display:flex; gap:10px">
                    <div style="flex:1">
                        <label>Start Time</label>
                        <input type="datetime-local" name="start_time" required>
                    </div>
                    <div style="flex:1">
                        <label>End Time</label>
                        <input type="datetime-local" name="end_time" required>
                    </div>
                </div>

                <label>Booking Type</label>
                <select name="type">
                    <option value="booking">One-time Booking</option>
                    <option value="fixed">Fixed Schedule (Recurring Class)</option>
                </select>

                <label>Additional Notes</label>
                <textarea name="notes" placeholder="Any specific requirements..."></textarea>

                <button type="submit" class="btn-primary" style="width:100%">Create Schedule</button>
            </form>
        </div>
</body>
</html>