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
            if (!confirm("Are you sure you want to delete this?")) {
                e.preventDefault();
            }
        });
    });
});