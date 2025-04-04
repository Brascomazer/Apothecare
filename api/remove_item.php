<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/session_helper.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'debug' => []];

// Controleer of de gebruiker is ingelogd
if (!is_logged_in()) {
    $response['message'] = 'Je moet ingelogd zijn om deze actie uit te voeren.';
    echo json_encode($response);
    exit;
}

// Voeg debugging informatie toe
$response['debug']['is_logged_in'] = true;
$response['debug']['session'] = $_SESSION;

// Ontvang en decodeer de JSON data
$data = json_decode(file_get_contents('php://input'), true);
$response['debug']['received_data'] = $data;

$medicijn_id = isset($data['medicijn_id']) ? (int)$data['medicijn_id'] : 0;
$bestelling_id = isset($data['bestelling_id']) ? (int)$data['bestelling_id'] : 0;
$klant_id = $_SESSION['gebruiker_id'];

$response['debug']['medicijn_id'] = $medicijn_id;
$response['debug']['bestelling_id'] = $bestelling_id;
$response['debug']['klant_id'] = $klant_id;

// Valideer de ontvangen gegevens
if (!$medicijn_id || !$bestelling_id) {
    $response['message'] = 'Ongeldige gegevens ontvangen.';
    echo json_encode($response);
    exit;
}

try {
    // Controleer of de bestelling van deze gebruiker is
    $check_sql = "SELECT bestelling_id FROM bestelling 
                 WHERE bestelling_id = ? AND klant_id = ? AND status = 'in behandeling'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $bestelling_id, $klant_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $response['debug']['order_check_rows'] = $check_result->num_rows;
    
    if ($check_result->num_rows === 0) {
        $response['message'] = 'Je hebt geen toegang tot deze bestelling.';
        echo json_encode($response);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();
    
    // Debug check bestelling_medicijn records voor deze combinatie
    $debug_check = "SELECT * FROM bestelling_medicijn WHERE bestelling_id = ? AND medicijn_id = ?";
    $debug_stmt = $conn->prepare($debug_check);
    $debug_stmt->bind_param("ii", $bestelling_id, $medicijn_id);
    $debug_stmt->execute();
    $debug_result = $debug_stmt->get_result();
    $response['debug']['item_exists'] = ($debug_result->num_rows > 0);
    if ($debug_result->num_rows > 0) {
        $response['debug']['item_data'] = $debug_result->fetch_assoc();
    }
    $debug_stmt->close();
    
    // Verwijder het item uit de winkelwagen
    $delete_sql = "DELETE FROM bestelling_medicijn 
                  WHERE bestelling_id = ? AND medicijn_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $bestelling_id, $medicijn_id);
    $result = $delete_stmt->execute();
    $response['debug']['delete_success'] = $result;
    $response['debug']['delete_affected_rows'] = $delete_stmt->affected_rows;
    $delete_stmt->close();
    
    if ($result && $delete_stmt->affected_rows > 0) {
        // Controleer of er nog items in de winkelwagen zijn
        $count_sql = "SELECT COUNT(*) as aantal FROM bestelling_medicijn WHERE bestelling_id = ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("i", $bestelling_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $count = $count_result->fetch_assoc()['aantal'];
        $response['debug']['remaining_items'] = $count;
        $count_stmt->close();
        
        // Als er geen items meer zijn, verwijder de bestelling
        if ($count == 0) {
            $delete_bestelling_sql = "DELETE FROM bestelling WHERE bestelling_id = ?";
            $delete_bestelling_stmt = $conn->prepare($delete_bestelling_sql);
            $delete_bestelling_stmt->bind_param("i", $bestelling_id);
            $delete_bestelling_result = $delete_bestelling_stmt->execute();
            $response['debug']['delete_order_success'] = $delete_bestelling_result;
            $delete_bestelling_stmt->close();
        }
        
        $response['success'] = true;
        $response['message'] = 'Product succesvol verwijderd.';
    } else {
        $response['message'] = 'Kon het product niet verwijderen. Het item bestaat mogelijk niet meer.';
    }
} catch (Exception $e) {
    $response['message'] = 'Er is een fout opgetreden: ' . $e->getMessage();
    $response['debug']['error'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>