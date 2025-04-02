<?php
include 'db_connection.php';

// Verwerk verwijderingen
if (isset($_POST['remove']) && is_array($_POST['remove'])) {
    foreach ($_POST['remove'] as $id) {
        $stmt = $conn->prepare("DELETE FROM winkelwagen WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Update aantallen
if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $id => $quantity) {
        $stmt = $conn->prepare("UPDATE winkelwagen SET aantal = ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $id);
        $stmt->execute();
    }
}

// Doorsturen naar betaling
if (isset($_POST['checkout'])) {
    header("Location: checkout.php");
    exit();
}

$conn->close();
header("Location: cart.html");
exit();
?>