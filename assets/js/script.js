document.addEventListener('DOMContentLoaded', function() {
    // Registratie formulier
    const registratieForm = document.getElementById('registratie-form');
    if (registratieForm) {
        registratieForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorElement = document.getElementById('registratie-error');
            
            fetch('../api/registreer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '../pages/' + data.redirect;
                } else {
                    errorElement.textContent = data.message;
                }
            })
            .catch(error => {
                errorElement.textContent = 'Er is een fout opgetreden. Probeer het later opnieuw.';
            });
        });
    }
    
    // Login formulier
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorElement = document.getElementById('login-error');
            
            fetch('../api/login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '../pages/' + data.redirect;
                } else {
                    errorElement.textContent = data.message;
                }
            })
            .catch(error => {
                errorElement.textContent = 'Er is een fout opgetreden. Probeer het later opnieuw.';
            });
        });
    }
});