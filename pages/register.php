<?php
session_start();
require_once '../includes/session_helper.php';

// Als gebruiker al is ingelogd, doorsturen naar dashboard
if(is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container auth-container">
        <div class="auth-wrapper">
            <div class="auth-box">
                <div class="auth-header">
                    <h1>Account aanmaken</h1>
                    <p>Word lid van Apothecare voor gepersonaliseerde medicijnzorg.</p>
                </div>
                
                <div id="registratie-error" class="error"></div>
                
                <form action="../api/registreer.php" method="post" id="registratie-form">
                    <div class="form-group">
                        <label for="naam">Volledige naam</label>
                        <input type="text" id="naam" name="naam" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mailadres</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefoonnummer">Telefoonnummer</label>
                        <input type="tel" id="nummer" name="nummer" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="geboortedatum">Geboortedatum</label>
                        <input type="date" id="geboortedatum" name="geboortedatum" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="adres">Adres</label>
                        <input type="text" id="adres" name="adres" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="wachtwoord">Wachtwoord</label>
                        <input type="password" id="wachtwoord" name="wachtwoord" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="wachtwoord_bevestiging">Wachtwoord bevestigen</label>
                        <input type="password" id="wachtwoord_bevestiging" name="wachtwoord_bevestiging" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Registreren</button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>Al een account? <a href="login.php">Inloggen</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>