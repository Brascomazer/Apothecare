<?php
session_start();
require 'db_connect.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam = trim($_POST['naam']);
    $adres = trim($_POST['adres']);
    $nummer = trim($_POST['nummer']);
    $wachtwoord = trim($_POST['wachtwoord']);
    $wachtwoord_bevestiging = trim($_POST['wachtwoord_bevestiging']);
    
    // Validatie
    if (empty($naam) || empty($adres) || empty($nummer) || empty($wachtwoord)) {
        $response['message'] = "Alle velden zijn verplicht!";
    } elseif ($wachtwoord !== $wachtwoord_bevestiging) {
        $response['message'] = "Wachtwoorden komen niet overeen!";
    } elseif (strlen($wachtwoord) < 8) {
        $response['message'] = "Wachtwoord moet minimaal 8 tekens lang zijn!";
    } else {
        // Controleren of gebruiker al bestaat
        $stmt = $conn->prepare("SELECT id FROM gebruikers WHERE naam = ?");
        $stmt->bind_param("s", $naam);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $response['message'] = "Gebruikersnaam is al in gebruik!";
        } else {
            // Wachtwoord hashen
            $hashed_wachtwoord = password_hash($wachtwoord, PASSWORD_DEFAULT);
            
            // Gebruiker toevoegen aan database
            $stmt = $conn->prepare("INSERT INTO gebruikers (naam, adres, telefoonnummer, wachtwoord) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $naam, $adres, $nummer, $hashed_wachtwoord);
            
            if ($stmt->execute()) {
                $_SESSION['gebruiker_naam'] = $naam;
                $response['success'] = true;
                $response['redirect'] = 'dashboard.php';
            } else {
                $response['message'] = "Registratie mislukt. Probeer het later opnieuw.";
            }
        }
        $stmt->close();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>