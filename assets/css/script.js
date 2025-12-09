document.addEventListener("DOMContentLoaded", function() {

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
