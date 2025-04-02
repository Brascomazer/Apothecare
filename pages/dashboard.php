<?php
session_start();

if (!isset($_SESSION['gebruiker_id'])) {
    header("Location: index.html");
    exit();
}

require 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Medicijnen Webshop</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.html">Home</a>
            <a href="over_ons.php">Over Ons</a>
            <a href="bestellingen.php">Bestellingen</a>
            <a href="winkelwagen.php">Winkelwagen</a>
            <a href="hulp.php">Hulp</a>
            <a href="logout.php">Uitloggen</a>
        </nav>
    </header>
    
    <div class="container">
        <div class="welcome">
            <h1>Welkom, <?php echo htmlspecialchars($_SESSION['gebruiker_naam']); ?>!</h1>
            <p>U bent succesvol ingelogd.</p>
            <p>Hier kunt u uw bestellingen bekijken en medicijnen bestellen.</p>
        </div>
    </div>
    
    <footer>
        <a href="index.html">Home</a>
        <a href="over_ons.php">Over Ons</a>
        <a href="service.php">Service & contact</a>
        <p>&copy; 2025 Medicijnen Webshop</p>
    </footer>
</body>
</html>