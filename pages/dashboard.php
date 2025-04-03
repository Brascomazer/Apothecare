<?php
session_start();
require '../includes/session_helper.php';

// Controleer of gebruiker is ingelogd
require_login();
start_secure_session();

require '../includes/db_connect.php';

// Haal gebruikersinfo op
$user_id = $_SESSION['gebruiker_id'];
$stmt = $conn->prepare("SELECT * FROM klant WHERE klant_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Medicijnen Webshop</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="hero">
            <h1>Welkom, <?php echo htmlspecialchars($_SESSION['gebruiker_naam']); ?>!</h1>
            <p>U bent succesvol ingelogd.</p>
        </div>
        
        <div class="dashboard-container">
            <div class="form-box">
                <h2>Mijn gegevens</h2>
                <div class="user-info">
                    <p><strong>Naam:</strong> <?php echo htmlspecialchars($user['naam']); ?></p>
                    <p><strong>E-mail:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Adres:</strong> <?php echo htmlspecialchars($user['adres']); ?></p>
                    <p><strong>Telefoonnummer:</strong> <?php echo htmlspecialchars($user['telefoonnummer']); ?></p>
                    <p><strong>Geboortedatum:</strong> <?php echo htmlspecialchars($user['geboortedatum']); ?></p>
                </div>
                <div class="dashboard-actions">
                    <a href="edit_profile.php" class="btn">Gegevens wijzigen</a>
                </div>
            </div>
            
            <div class="form-box">
                <h2>Mijn recente bestellingen</h2>
                <!-- Hier kun je recente bestellingen toevoegen als je die functionaliteit hebt -->
                <p>Je hebt nog geen recente bestellingen.</p>
                <div class="dashboard-actions">
                    <a href="medicijnen.php" class="btn">Bekijk medicijnen</a>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <a href="index.html">Home</a>
        <a href="over_ons.html">Over Ons</a>
        <a href="service.php">Service & contact</a>
        <p>&copy; 2025 Medicijnen Webshop</p>
    </footer>
</body>
</html>