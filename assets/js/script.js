document.addEventListener("DOMContentLoaded", function() {
// --- AUTO-REFRESH LOGIC (DASHBOARD ONLY) ---
    function checkAutoRefresh() {
        // 1. Check if we are on the Dashboard
        // We look for the unique clock element that only exists on dashboard.php
        const clockElement = document.getElementById('server-clock');

        // If the clock element DOES NOT exist, stop immediately.
        // This prevents the refresh on Manage Schedules, Profile, etc.
        if (!clockElement) {
            return; 
        }

        // 2. Update the Clock Text (Optional, keeps the clock ticking)
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        clockElement.innerText = timeString;

        // 3. Trigger Refresh Logic
        // Only refreshes if seconds hit 00 AND we are on the dashboard
        if (now.getSeconds() === 0) {
            console.log("Dashboard sync: New minute detected. Refreshing...");
            window.location.reload();
        }
    }

    // Run the check every 1 second (1000ms)
    setInterval(checkAutoRefresh, 1000);
    
    // Run it once immediately to set the clock text without delay
    checkAutoRefresh();

    // Sidebar Toggle Logic
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    });

    // Close sidebar when clicking outside on mobile (Optional UX improvement)
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !e.target.closest('.toggle-btn')) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Confirm Delete Logic
    const deleteForms = document.querySelectorAll('.confirm-delete');
        deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
             if(!confirm("Are you sure you want to delete this?")) {
                 e.preventDefault();
            }
        });
    });
});

    // MODAL (CURRENT CLASS SCHED EDITOR)
const modal = document.getElementById('editModal');

    function openEditModal(data) {
      // Fill the form with data from the clicked row
      document.getElementById('edit_id').value = data.id;
      document.getElementById('edit_room_id').value = data.room_id;
      document.getElementById('edit_title').value = data.title;
      document.getElementById('edit_instructor').value = data.instructor;
      document.getElementById('edit_day').value = data.day_of_week;
      document.getElementById('edit_start').value = data.start_time;
      document.getElementById('edit_end').value = data.end_time;
      
      // Show the modal
      modal.style.display = 'flex';
    }

    function closeEditModal() {
      if (confirm("Are you sure you want to exit? Any unsaved changes will be lost.")) {
        const modal = document.getElementById('editModal');
        if (modal) modal.style.display = 'none';
        }
    }
