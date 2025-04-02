<?php
include 'db_connection.php';

// Voorbeeld: haal producten op uit de database (in een echte app zou je de sessie of user ID gebruiken)
$sql = "SELECT * FROM winkelwagen";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['product_naam']) . "</td>";
        echo "<td><input type='number' name='quantity[" . $row['id'] . "]' value='" . $row['aantal'] . "' min='1'></td>";
        echo "<td>€" . number_format($row['prijs'] * $row['aantal'], 2) . "</td>";
        echo "<td><input type='checkbox' name='remove[]' value='" . $row['id'] . "'></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>Je winkelwagen is leeg</td></tr>";
}

$conn->close();
?>