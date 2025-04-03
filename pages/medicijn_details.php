<?php
session_start();
require '../includes/db_connect.php';
require '../includes/session_helper.php';

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
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="product-detail">
            <div class="product-image">
                <img src="../assets/images/medicine-placeholder.png" alt="<?php echo htmlspecialchars($medicijn['naam']); ?>">
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($medicijn['naam']); ?></h1>
                
                <div class="product-price-section">
                    <div class="product-price">
                        €<?php echo number_format($medicijn['prijs'], 2, ',', '.'); ?>
                    </div>
                    <div class="product-availability <?php echo $medicijn['hoeveelheid'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $medicijn['hoeveelheid'] > 0 ? 'Op voorraad' : 'Niet op voorraad'; ?>
                    </div>
                </div>
                
                <div class="product-description">
                    <h3>Beschrijving</h3>
                    <p><?php echo htmlspecialchars($medicijn['beschrijving']); ?></p>
                </div>
                
                <?php if (is_logged_in()): ?>
                    <div class="product-actions">
                        <div class="quantity-selector">
                            <label for="quantity">Aantal:</label>
                            <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $medicijn['hoeveelheid']; ?>" value="1">
                        </div>
                        
                        <?php if ($medicijn['hoeveelheid'] > 0): ?>
                            <button class="btn btn-primary add-to-cart" data-id="<?php echo $medicijn['medicijn_id']; ?>">
                                <i class="fas fa-shopping-cart"></i> In winkelwagen
                            </button>
                        <?php else: ?>
                            <button class="btn btn-disabled" disabled>Niet beschikbaar</button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>Log in om dit product te bestellen.</p>
                        <a href="login.php" class="btn btn-primary">Inloggen</a>
                    </div>
                <?php endif; ?>
                
                <div class="back-to-products">
                    <a href="medicijnen.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Terug naar alle medicijnen
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>

    <script>
        // JavaScript voor de 'In winkelwagen' knop
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButtons = document.querySelectorAll('.add-to-cart');
            
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const medicijnId = this.getAttribute('data-id');
                    const quantity = document.getElementById('quantity').value;
                    
                    fetch('../api/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            product_id: medicijnId,
                            quantity: Number(quantity)
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Response from server:', data); // Debug info
                        
                        if (data.success) {
                            alert(data.message);
                        } else {
                            alert(data.message || 'Er is een probleem opgetreden.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Er is een fout opgetreden bij het toevoegen aan de winkelwagen.');
                    });
                });
            });
        });
    </script>
</body>
</html>