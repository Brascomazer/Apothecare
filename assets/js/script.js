document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.getElementById('menu-toggle');
    const navMenu = document.getElementById('nav-menu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
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
    
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    if (addToCartButtons.length > 0) {
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                fetch('../api/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: 1
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success notification
                        alert('Product toegevoegd aan winkelwagen!');
                    } else {
                        alert(data.message || 'Er is iets misgegaan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Er is een fout opgetreden');
                });
            });
        });
    }
});