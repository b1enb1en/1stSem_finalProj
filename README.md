# ReserbaSilid

**ReserbaSilid** A web-based room reservation and room scheduling management system designed for schools or institutions. It allows users to view real-time room availability, manage class schedules, and book rooms for specific time slots.

---

## Members:

**Mahusay, Karl Ashton**  
**Rodriguez, Leorenz Bien G.**  
**Binos, Samuel**  
**Jarina, Jimuel**
**Gud-ay, April Joy**

---

## Technologies:
- Back-End: PHP (Native), SQLite (Database)
- Front-End: HTML5, CSS3, JavaScript (embedded)
- Server: PHP Built-in Server (for development)

## Features:
- Room Booking
- Room Scheduling management system
- Real-Time room status

---

## HOW TO RUN OUR CODE
SQLite Notes for Room & Schedule Management
- Database Creation: A rooms.db file is created automatically in the src/data/ folder on the first visit to any page (e.g., login.php).
- Tables: The system automatically generates the following tables:
    - users: id, username, password_hash
    - rooms: id, name, description, status
    - schedules: id, room_id, title, instructor, start_time, end_time, day_of_week, type, ...

1. Start: Go to http://localhost:7000/index.php to see the landing page.
2. Login/Register: Navigate to http://localhost:7000/src/login.php.
    Note: You must Register a user first to log in.
3. Dashboard: Once logged in, dashboard.php displays real-time room availability (Green for Available, Red for Occupied).
4. Booking: Use the "Scheduler" page to search for free slots and book rooms.


