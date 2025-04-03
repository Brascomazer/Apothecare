<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/session_helper.php';

// Controleer of de gebruiker ingelogd is
if (is_logged_in()) {
    $klant_id = $_SESSION['gebruiker_id'];
    
    // Zoek eerst bestaande winkelwagen (bestelling met status 'in behandeling')
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
            while($row = $items_result->fetch_assoc()) {
                $totaal_prijs = $row['prijs'] * $row['hoeveelheid'];
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['naam']) . "</td>";
                echo "<td><input type='number' name='quantity[" . $row['medicijn_id'] . "]' value='" . $row['hoeveelheid'] . "' min='1'></td>";
                echo "<td>€" . number_format($totaal_prijs, 2, ',', '.') . "</td>";
                echo "<td><input type='checkbox' name='remove[]' value='" . $row['medicijn_id'] . "'></td>";
                echo "</tr>";
                
                // Bewaar het bestelling_id in een hidden field
                echo "<input type='hidden' name='bestelling_id' value='" . $bestelling_id . "'>";
            }
        } else {
            echo "<tr><td colspan='4'>Je winkelwagen is leeg</td></tr>";
        }
        
        $items_stmt->close();
    } else {
        echo "<tr><td colspan='4'>Je winkelwagen is leeg</td></tr>";
    }
    
    $cart_stmt->close();
} else {
    echo "<tr><td colspan='4'>Log in om je winkelwagen te bekijken</td></tr>";
}

$conn->close();
?>