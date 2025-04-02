<?php
session_start();
require 'db_connect.php';

// Update database naam in de query
$conn->select_db("apothecare_db");

// Controleer of ID is opgegeven
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: medicijnen.php');
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM medicijn WHERE medicijn_id = $id";
$result = mysqli_query($conn, $sql);

// Controleer of medicijn bestaat
if (mysqli_num_rows($result) === 0) {
    header('Location: medicijnen.php');
    exit;
}

$medicijn = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($medicijn['naam']); ?> - Apothecare</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.html">Home</a>
            <a href="medicijnen.php">Medicijnen</a>
            <a href="over_ons.php">Over Ons</a>
            <a href="bestellingen.php">Bestellingen</a>
            <a href="winkelwagen.php">Winkelwagen</a>
            <a href="hulp.php">Hulp</a>
        </nav>
    </header>
    
    <div class="container">
        <div class="form-box medicijn-details">
            <h1><?php echo htmlspecialchars($medicijn['naam']); ?></h1>
            
            <div class="medicijn-info">
                <div class="medicijn-sectie">
                    <h3>Beschrijving</h3>
                    <p><?php echo htmlspecialchars($medicijn['beschrijving']); ?></p>
                </div>
                
                <div class="medicijn-prijs-voorraad">
                    <div class="medicijn-prijs">
                        <h3>Prijs</h3>
                        <p>€<?php echo number_format($medicijn['prijs'], 2, ',', '.'); ?></p>
                    </div>
                    
                    <div class="medicijn-voorraad">
                        <h3>Beschikbaarheid</h3>
                        <p>Voorraad: <?php echo $medicijn['hoeveelheid']; ?> stuks</p>
                    </div>
                </div>
                
                <div class="medicijn-knoppen">
                    <?php if ($medicijn['hoeveelheid'] > 0): ?>
                        <button class="btn add-to-cart" data-id="<?php echo $medicijn['medicijn_id']; ?>">In winkelwagen</button>
                        <a href="medicijnen.php" class="btn">Terug naar overzicht</a>
                    <?php else: ?>
                        <p>Dit product is momenteel niet op voorraad.</p>
                        <a href="medicijnen.php" class="btn">Terug naar overzicht</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <a href="index.">Home</a>
        <a href="over_ons.php">Over Ons</a>
        <a href="service.php">Service & contact</a>
        <p>&copy; 2025 Medicijnen Webshop</p>
    </footer>

    <script>
        // JavaScript voor de 'In winkelwagen' knop
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const medicijnId = this.getAttribute('data-id');
                // Hier zou je AJAX kunnen gebruiken om het medicijn aan de winkelwagen toe te voegen
                alert('Medicijn toegevoegd aan winkelwagen!');
            });
        });
    </script>
</body>
</html>