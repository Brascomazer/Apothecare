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
    <title>Inloggen - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container auth-container">
        <div class="auth-wrapper">
            <div class="auth-box">
                <div class="auth-header">
                    <h1>Inloggen</h1>
                    <p>Welkom terug bij Apothecare. Log in om door te gaan.</p>
                </div>
                
                <div id="login-error" class="error"></div>
                
                <form action="../api/login.php" method="post" id="login-form">
                    <div class="form-group">
                        <label for="login_email">E-mailadres</label>
                        <input type="email" id="login_email" name="login_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login_wachtwoord">Wachtwoord</label>
                        <input type="password" id="login_wachtwoord" name="login_wachtwoord" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Inloggen</button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>Nog geen account? <a href="register.php">Registreer nu</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>