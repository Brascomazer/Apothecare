document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const toggle = document.querySelector('.admin-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const content = document.querySelector('.admin-content');
    
    if (toggle) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                sidebar.style.width = 'var(--admin-sidebar-collapsed)';
                content.style.marginLeft = 'var(--admin-sidebar-collapsed)';
            } else {
                sidebar.style.width = 'var(--admin-sidebar-width)';
                content.style.marginLeft = 'var(--admin-sidebar-width)';
            }
        });
    }
    
    // Automatisch verdwijnen van meldingen
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});