<?php
session_start();
require 'db_connect.php';

// Update database naam in de query
$conn->select_db("apothecare_db");

// Haal medicijnen op uit de database
$sql = "SELECT * FROM medicijn";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicijnen - Apothecare</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <a href="medicijnen.php" class="active">Medicijnen</a>
            <a href="over_ons.php">Over Ons</a>
            <a href="bestellingen.php">Bestellingen</a>
            <a href="winkelwagen.php">Winkelwagen</a>
            <a href="hulp.php">Hulp</a>
        </nav>
    </header>
    
    <div class="container">
        <div class="hero">
            <h1>Onze Medicijnen</h1>
            <p>Bekijk ons assortiment aan medicijnen</p>
        </div>
        
        <div class="medicijnen-grid">
            <?php
            // Controleer of er medicijnen zijn
            if (mysqli_num_rows($result) > 0) {
                // Toon elk medicijn
                while($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="medicijn-kaart form-box">';
                    echo '<h2>' . htmlspecialchars($row['naam']) . '</h2>';
                    echo '<p>' . htmlspecialchars(substr($row['beschrijving'], 0, 100)) . '...</p>';
                    echo '<p class="medicijn-prijs">€' . number_format($row['prijs'], 2, ',', '.') . '</p>';
                    echo '<p>Voorraad: ' . $row['hoeveelheid'] . '</p>';
                    echo '<div class="medicijn-knoppen">';
                    echo '<a href="medicijn_details.php?id=' . $row['medicijn_id'] . '" class="btn">Meer informatie</a>';
                    echo '<button class="btn add-to-cart" data-id="' . $row['medicijn_id'] . '">In winkelwagen</button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>Geen medicijnen gevonden</p>';
            }
            ?>
        </div>
    </div>
    
    <footer>
        <a href="index.php">Home</a>
        <a href="over_ons.php">Over Ons</a>
        <a href="service.php">Service & contact</a>
        <p>&copy; 2025 Medicijnen Webshop</p>
    </footer>

    <script>
        // JavaScript om 'In winkelwagen' knoppen te laten werken
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const medicijnId = this.getAttribute('data-id');
                // Hier zou je AJAX kunnen gebruiken om het medicijn aan een winkelwagensessie toe te voegen
                alert('Medicijn toegevoegd aan winkelwagen!');
            });
        });
    </script>
</body>
</html>