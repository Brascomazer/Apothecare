<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/session_helper.php';

// Controleer of de gebruiker is ingelogd
if (!is_logged_in()) {
    header('Location: ../pages/login.php');
    exit();
}

$klant_id = $_SESSION['gebruiker_id'];
$bestelling_id = $_POST['bestelling_id'] ?? null;

if (!$bestelling_id) {
    header('Location: ../pages/winkelwagen.php');
    exit();
}

// Controleer of deze bestelling van deze klant is
$check_sql = "SELECT bestelling_id FROM bestelling 
             WHERE bestelling_id = ? AND klant_id = ? AND status = 'in behandeling'";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $bestelling_id, $klant_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    header('Location: ../pages/winkelwagen.php');
    exit();
}
$check_stmt->close();

// Update aantallen
if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $medicijn_id => $hoeveelheid) {
        if ($hoeveelheid > 0) {
            $update_sql = "UPDATE bestelling_medicijn 
                          SET hoeveelheid = ? 
                          WHERE bestelling_id = ? AND medicijn_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iii", $hoeveelheid, $bestelling_id, $medicijn_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Verwijder items met hoeveelheid 0
            $delete_sql = "DELETE FROM bestelling_medicijn 
                          WHERE bestelling_id = ? AND medicijn_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("ii", $bestelling_id, $medicijn_id);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
    }
    
    // Toon bericht als het alleen een update was
    if (isset($_POST['update'])) {
        $_SESSION['cart_message'] = "Winkelwagen succesvol bijgewerkt.";
    }
}

// Doorsturen naar betaling of winkelwagen
if (isset($_POST['checkout'])) {
    // Eerst controleren of er nog items in de winkelwagen zijn
    $count_sql = "SELECT COUNT(*) as aantal FROM bestelling_medicijn WHERE bestelling_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $bestelling_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count = $count_result->fetch_assoc()['aantal'];
    $count_stmt->close();
    
    if ($count > 0) {
        // Sla het bestelling_id op in de sessie voor de betaalpagina
        $_SESSION['bestelling_id'] = $bestelling_id;
        
        header("Location: ../pages/betaling.php");
        exit();
    }
}

// Als we hier komen, gaan we terug naar winkelwagen
header("Location: ../pages/winkelwagen.php");
exit();
?>