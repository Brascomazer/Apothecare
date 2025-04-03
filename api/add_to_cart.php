<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/session_helper.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'debug' => []];

try {
    // Controleer of gebruiker is ingelogd
    if (!is_logged_in()) {
        $response['message'] = 'Je moet ingelogd zijn om producten aan de winkelwagen toe te voegen.';
        echo json_encode($response);
        exit;
    }
    
    // Debug info toevoegen
    $response['debug']['user_id'] = $_SESSION['gebruiker_id'];
    
    // Ontvang gegevens
    $data = json_decode(file_get_contents('php://input'), true);
    $response['debug']['received_data'] = $data;
    
    if (!isset($data['product_id']) || !isset($data['quantity'])) {
        $response['message'] = 'Ongeldige aanvraag.';
        echo json_encode($response);
        exit;
    }
    
    $medicijn_id = (int)$data['product_id'];
    $hoeveelheid = max(1, (int)$data['quantity']);
    $klant_id = $_SESSION['gebruiker_id'];
    
    $response['debug']['medicijn_id'] = $medicijn_id;
    $response['debug']['hoeveelheid'] = $hoeveelheid;
    $response['debug']['klant_id'] = $klant_id;
    
    // Start een transactie
    $conn->begin_transaction();
    
    // Controleer of het medicijn bestaat
    $medicijn_sql = "SELECT medicijn_id, naam, prijs, hoeveelheid FROM medicijn WHERE medicijn_id = ?";
    $medicijn_stmt = $conn->prepare($medicijn_sql);
    $medicijn_stmt->bind_param("i", $medicijn_id);
    $medicijn_stmt->execute();
    $medicijn_result = $medicijn_stmt->get_result();
    
    if ($medicijn_result->num_rows === 0) {
        $response['message'] = 'Medicijn niet gevonden.';
        echo json_encode($response);
        $medicijn_stmt->close();
        $conn->rollback();
        exit;
    }
    
    $medicijn = $medicijn_result->fetch_assoc();
    $response['debug']['medicijn'] = $medicijn;
    
    if ($medicijn['hoeveelheid'] < $hoeveelheid) {
        $response['message'] = 'Onvoldoende voorraad. Er zijn nog ' . $medicijn['hoeveelheid'] . ' beschikbaar.';
        echo json_encode($response);
        $medicijn_stmt->close();
        $conn->rollback();
        exit;
    }
    $medicijn_stmt->close();
    
    // Zoek bestaande winkelwagen (bestelling met status 'in behandeling')
    $cart_sql = "SELECT bestelling_id FROM bestelling 
                WHERE klant_id = ? AND status = 'in behandeling'";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $klant_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    $response['debug']['cart_exists'] = $cart_result->num_rows > 0;
    
    // Als er nog geen winkelwagen is, maak een nieuwe aan
    if ($cart_result->num_rows === 0) {
        $create_cart_sql = "INSERT INTO bestelling (klant_id, status, datum) 
                          VALUES (?, 'in behandeling', NOW())";
        $create_cart_stmt = $conn->prepare($create_cart_sql);
        $create_cart_stmt->bind_param("i", $klant_id);
        $create_cart_stmt->execute();
        $bestelling_id = $conn->insert_id;
        $response['debug']['new_cart_id'] = $bestelling_id;
        $create_cart_stmt->close();
    } else {
        $cart = $cart_result->fetch_assoc();
        $bestelling_id = $cart['bestelling_id'];
        $response['debug']['existing_cart_id'] = $bestelling_id;
    }
    $cart_stmt->close();
    
    // Controleer of het medicijn al in de winkelwagen zit
    $check_sql = "SELECT hoeveelheid FROM bestelling_medicijn 
                 WHERE bestelling_id = ? AND medicijn_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $bestelling_id, $medicijn_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $response['debug']['item_exists'] = $check_result->num_rows > 0;
    
    if ($check_result->num_rows > 0) {
        // Update bestaand item
        $item = $check_result->fetch_assoc();
        $new_quantity = $item['hoeveelheid'] + $hoeveelheid;
        
        $update_sql = "UPDATE bestelling_medicijn 
                      SET hoeveelheid = ? 
                      WHERE bestelling_id = ? AND medicijn_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iii", $new_quantity, $bestelling_id, $medicijn_id);
        $update_result = $update_stmt->execute();
        $response['debug']['update_result'] = $update_result;
        $update_stmt->close();
        
        $response['success'] = true;
        $response['message'] = 'Aantal bijgewerkt in je winkelwagen.';
    } else {
        // Voeg nieuw item toe
        $add_sql = "INSERT INTO bestelling_medicijn (bestelling_id, medicijn_id, hoeveelheid) 
                   VALUES (?, ?, ?)";
        $add_stmt = $conn->prepare($add_sql);
        $add_stmt->bind_param("iii", $bestelling_id, $medicijn_id, $hoeveelheid);
        $add_result = $add_stmt->execute();
        $response['debug']['add_result'] = $add_result;
        $add_stmt->close();
        
        $response['success'] = true;
        $response['message'] = 'Product toegevoegd aan je winkelwagen.';
    }
    $check_stmt->close();
    
    // Commit de transactie
    $conn->commit();
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    $response['message'] = 'Er is een fout opgetreden: ' . $e->getMessage();
    $response['debug']['error'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>