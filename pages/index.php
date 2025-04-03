<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicijnen Webshop - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="hero">
            <h1>Alles over medicijnen</h1>
            <p>Betrouwbare informatie van de apotheker</p>
        </div>
        
        <!-- Login/Registratie forms - worden verborgen als ingelogd -->
        <div id="auth-forms" class="form-container">
            <div class="form-box">
                <h2>Registreer</h2>
                <div id="registratie-error" class="error"></div>
                <form action="../api/registreer.php" method="post" id="registratie-form">
                    <div class="form-group">
                        <label for="naam">Naam:</label>
                        <input type="text" id="naam" name="naam" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="geboortedatum">Geboortedatum:</label>
                        <input type="date" id="geboortedatum" name="geboortedatum" required>
                    </div>
                    <div class="form-group">
                        <label for="adres">Adres:</label>
                        <input type="text" id="adres" name="adres" required>
                    </div>
                    <div class="form-group">
                        <label for="nummer">Telefoonnummer:</label>
                        <input type="text" id="nummer" name="nummer" required>
                    </div>
                    <div class="form-group">
                        <label for="wachtwoord">Wachtwoord:</label>
                        <input type="password" id="wachtwoord" name="wachtwoord" required>
                    </div>
                    <div class="form-group">
                        <label for="wachtwoord_bevestiging">Wachtwoord bevestigen:</label>
                        <input type="password" id="wachtwoord_bevestiging" name="wachtwoord_bevestiging" required>
                    </div>
                    <button type="submit" name="registreer" class="btn">Registreer</button>
                </form>
            </div>
            
            <div class="form-box">
                <h2>Login</h2>
                <div id="login-error" class="error"></div>
                <form action="../api/login.php" method="post" id="login-form">
                    <div class="form-group">
                        <label for="login_email">E-mail:</label>
                        <input type="email" id="login_email" name="login_email" required>
                    </div>
                    <div class="form-group">
                        <label for="login_wachtwoord">Wachtwoord:</label>
                        <input type="password" id="login_wachtwoord" name="login_wachtwoord" required>
                    </div>
                    <button type="submit" name="login" class="btn">Login</button>
                </form>
            </div>
        </div>
        
        <!-- Welkomstbericht dat wordt getoond als gebruiker ingelogd is -->
        <div id="welcome-message" class="form-box" style="display:none;">
            <h2>Welkom bij Apothecare</h2>
            <p>Je bent succesvol ingelogd.</p>
            <div class="action-buttons">
                <a href="medicijnen.php" class="btn">Bekijk medicijnen</a>
                <a href="dashboard.php" class="btn">Ga naar dashboard</a>
            </div>
        </div>
    </div>
    
    <footer>
        <a href="index.html">Home</a>
        <a href="over_ons.html">Over Ons</a>
        <a href="service.php">Service & contact</a>
        <p>&copy; 2025 Medicijnen Webshop</p>
    </footer>

    <script src="../assets/js/script.js"></script>
    <script>
        // Controleer login status en toon juiste elementen
        document.addEventListener('DOMContentLoaded', function() {
            fetch('../api/check_login.php')
                .then(response => response.json())
                .then(data => {
                    if (data.loggedIn) {
                        // Gebruiker is ingelogd
                        document.getElementById('auth-forms').style.display = 'none';
                        document.getElementById('welcome-message').style.display = 'block';
                        
                        // Voeg uitlog knop toe aan de navigatie
                        const nav = document.getElementById('main-nav');
                        const logoutLink = document.createElement('a');
                        logoutLink.href = '../api/logout.php';
                        logoutLink.textContent = 'Uitloggen';
                        logoutLink.className = 'logout-btn';
                        nav.appendChild(logoutLink);
                    }
                });
        });
    </script>
</body>
</html>