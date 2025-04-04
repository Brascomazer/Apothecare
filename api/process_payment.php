<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/session_helper.php';

header('Content-Type: application/json');

// Controleer of gebruiker is ingelogd
if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'message' => 'Je moet ingelogd zijn om deze actie uit te voeren.'
    ]);
    exit;
}

try {
    // Ontvang de POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Valideer de data
    if (!isset($data['bestelling_id']) || !isset($data['payment_method'])) {
        throw new Exception('Ongeldige betalingsgegevens ontvangen.');
    }
    
    $bestelling_id = (int)$data['bestelling_id'];
    $payment_method = $data['payment_method'];
    $klant_id = $_SESSION['gebruiker_id'];
    
    // Controleer of de bestelling bestaat en van deze klant is
    $check_sql = "SELECT bestelling_id FROM bestelling 
                 WHERE bestelling_id = ? AND klant_id = ? AND status = 'in behandeling'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $bestelling_id, $klant_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception('Ongeldige bestelling');
    }
    
    $check_stmt->close();
    
    // Begin een transactie
    $conn->begin_transaction();
    
    // Update het bezorginformatie veld met de betaalmethode
    $update_sql = "UPDATE bestelling SET 
                  bezorginformatie = CONCAT(IFNULL(bezorginformatie, ''), ' Betaalmethode: ', ?),
                  status = 'verzonden'
                  WHERE bestelling_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $payment_method, $bestelling_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    // Verlaag de voorraad van elk medicijn
    $inventory_sql = "SELECT medicijn_id, hoeveelheid FROM bestelling_medicijn WHERE bestelling_id = ?";
    $inventory_stmt = $conn->prepare($inventory_sql);
    $inventory_stmt->bind_param("i", $bestelling_id);
    $inventory_stmt->execute();
    $inventory_result = $inventory_stmt->get_result();
    
    while ($item = $inventory_result->fetch_assoc()) {
        $update_inventory_sql = "UPDATE medicijn 
                              SET hoeveelheid = hoeveelheid - ? 
                              WHERE medicijn_id = ? AND hoeveelheid >= ?";
        $update_inventory_stmt = $conn->prepare($update_inventory_sql);
        $update_inventory_stmt->bind_param("iii", $item['hoeveelheid'], $item['medicijn_id'], $item['hoeveelheid']);
        $update_inventory_stmt->execute();
        $update_inventory_stmt->close();
    }
    
    $inventory_stmt->close();
    
    // Commit de transactie
    $conn->commit();
    
    // Verwijder de bestelling ID uit de sessie, bestelling is nu verwerkt
    unset($_SESSION['bestelling_id']);
    
    // Stuur een succesvolle response terug
    echo json_encode([
        'success' => true,
        'order_id' => $bestelling_id
    ]);

} catch (Exception $e) {
    // Rollback bij een fout
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>