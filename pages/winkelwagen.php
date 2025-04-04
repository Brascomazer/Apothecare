<?php
session_start();
require_once '../includes/session_helper.php';
require_once '../includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelwagen - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h1>Winkelwagen</h1>

        <?php if (isset($_SESSION['cart_message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['cart_message']; 
                    unset($_SESSION['cart_message']); 
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (!is_logged_in()): ?>
            <div class="not-logged-in">
                <p>Je moet ingelogd zijn om je winkelwagen te bekijken.</p>
                <div class="action-buttons">
                    <a href="login.php" class="btn btn-primary">Inloggen</a>
                    <a href="register.php" class="btn btn-outline">Registreren</a>
                </div>
            </div>
        <?php else: ?>
            <?php
            // Debug info tonen
            echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9;'>";
            echo "<p><strong>Debug info:</strong></p>";
            echo "<p>Ingelogd als: " . $_SESSION['gebruiker_naam'] . " (ID: " . $_SESSION['gebruiker_id'] . ")</p>";
            
            // Controleer of er een winkelwagen bestaat voor deze gebruiker
            $klant_id = $_SESSION['gebruiker_id'];
            $check_cart_sql = "SELECT bestelling_id FROM bestelling WHERE klant_id = ? AND status = 'in behandeling'";
            $check_cart_stmt = $conn->prepare($check_cart_sql);
            $check_cart_stmt->bind_param("i", $klant_id);
            $check_cart_stmt->execute();
            $check_cart_result = $check_cart_stmt->get_result();
            
            if ($check_cart_result->num_rows > 0) {
                $cart = $check_cart_result->fetch_assoc();
                echo "<p>Winkelwagen gevonden: Bestelling #" . $cart['bestelling_id'] . "</p>";
                
                // Controleer hoeveel items in de winkelwagen zitten
                $count_items_sql = "SELECT COUNT(*) as aantal FROM bestelling_medicijn WHERE bestelling_id = ?";
                $count_items_stmt = $conn->prepare($count_items_sql);
                $count_items_stmt->bind_param("i", $cart['bestelling_id']);
                $count_items_stmt->execute();
                $count_result = $count_items_stmt->get_result()->fetch_assoc();
                echo "<p>Aantal items in winkelwagen: " . $count_result['aantal'] . "</p>";
                $count_items_stmt->close();
            } else {
                echo "<p>Geen actieve winkelwagen gevonden.</p>";
            }
            $check_cart_stmt->close();
            echo "</div>";
            ?>
            
            <div class="cart-container">
                <form action="../api/process_cart.php" method="post">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Aantal</th>
                                <th>Prijs</th>
                                <th>Verwijderen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Controleer of de gebruiker ingelogd is (nogmaals voor de zekerheid)
                            $empty_cart = true;
                            
                            if (is_logged_in()) {
                                $klant_id = $_SESSION['gebruiker_id'];
                                
                                // Zoek bestaande winkelwagen (bestelling met status 'in behandeling')
                                $cart_sql = "SELECT bestelling_id FROM bestelling 
                                            WHERE klant_id = ? AND status = 'in behandeling'";
                                $cart_stmt = $conn->prepare($cart_sql);
                                $cart_stmt->bind_param("i", $klant_id);
                                $cart_stmt->execute();
                                $cart_result = $cart_stmt->get_result();
                                
                                // Als er een winkelwagen bestaat, toon de inhoud
                                if ($cart_result->num_rows > 0) {
                                    $cart = $cart_result->fetch_assoc();
                                    $bestelling_id = $cart['bestelling_id'];
                                    
                                    // Haal alle items in de winkelwagen op
                                    $items_sql = "SELECT bm.*, m.naam, m.prijs 
                                                FROM bestelling_medicijn bm 
                                                JOIN medicijn m ON bm.medicijn_id = m.medicijn_id
                                                WHERE bm.bestelling_id = ?";
                                    $items_stmt = $conn->prepare($items_sql);
                                    $items_stmt->bind_param("i", $bestelling_id);
                                    $items_stmt->execute();
                                    $items_result = $items_stmt->get_result();
                                    
                                    if ($items_result->num_rows > 0) {
                                        $empty_cart = false;
                                        while($row = $items_result->fetch_assoc()) {
                                            $totaal_prijs = $row['prijs'] * $row['hoeveelheid'];
                                            
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['naam']) . "</td>";
                                            echo "<td><input type='number' name='quantity[" . $row['medicijn_id'] . "]' value='" . $row['hoeveelheid'] . "' min='1'></td>";
                                            echo "<td>€" . number_format($totaal_prijs, 2, ',', '.') . "</td>";
                                            echo "<td>
                                                <button type='button' class='remove-item-btn' data-id='" . $row['medicijn_id'] . "' data-bestelling='" . $bestelling_id . "'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </td>";
                                            echo "</tr>";
                                        }
                                        
                                        // Bewaar het bestelling_id in een hidden field
                                        echo "<input type='hidden' name='bestelling_id' value='" . $bestelling_id . "'>";
                                    }
                                    
                                    $items_stmt->close();
                                }
                                
                                $cart_stmt->close();
                            }
                            
                            // Toon een bericht als de winkelwagen leeg is
                            if ($empty_cart) {
                                echo "<tr><td colspan='4' style='text-align: center;'>Je winkelwagen is leeg</td></tr>";
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="total-label">Totaal:</td>
                                <td class="total-price">€<?php 
                                    // Bereken het totaalbedrag
                                    $total = 0;
                                    
                                    if (isset($bestelling_id)) {
                                        // Bereken totaal
                                        $total_sql = "SELECT SUM(bm.hoeveelheid * m.prijs) as total 
                                                    FROM bestelling_medicijn bm 
                                                    JOIN medicijn m ON bm.medicijn_id = m.medicijn_id
                                                    WHERE bm.bestelling_id = ?";
                                        $total_stmt = $conn->prepare($total_sql);
                                        $total_stmt->bind_param("i", $bestelling_id);
                                        $total_stmt->execute();
                                        $total_result = $total_stmt->get_result();
                                        
                                        if ($total_result->num_rows > 0) {
                                            $row = $total_result->fetch_assoc();
                                            $total = $row['total'] ?? 0;
                                        }
                                        
                                        $total_stmt->close();
                                    }
                                    
                                    echo number_format($total, 2, ',', '.');
                                ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="cart-actions">
                        <a href="medicijnen.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Verder winkelen
                        </a>
                        <?php if (!$empty_cart): ?>
                            <button type="submit" name="update" class="btn btn-secondary">
                                <i class="fas fa-sync"></i> Bijwerken
                            </button>
                            <button type="submit" name="checkout" class="btn btn-primary">
                                <i class="fas fa-shopping-cart"></i> Afrekenen
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selecteer alle verwijder-knoppen
        const removeButtons = document.querySelectorAll('.remove-item-btn');
        
        // Voeg event listener toe aan elke knop
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Weet je zeker dat je dit product wilt verwijderen?')) {
                    const medicijnId = this.getAttribute('data-id');
                    const bestellingId = this.getAttribute('data-bestelling');
                    
                    // Ajax verzoek om item direct te verwijderen
                    fetch('../api/remove_item.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            medicijn_id: medicijnId,
                            bestelling_id: bestellingId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Als succesvol, herlaad de pagina om de wijzigingen te tonen
                            window.location.reload();
                        } else {
                            alert('Fout bij het verwijderen: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Er is een fout opgetreden bij het verwijderen.');
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
<?php
// Sluit de database verbinding aan het einde van de pagina
$conn->close();
?>