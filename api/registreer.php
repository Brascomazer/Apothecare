<?php
// Voor debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../includes/db_connect.php';
require '../includes/session_helper.php';

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $naam = trim($_POST['naam'] ?? '');
        $adres = trim($_POST['adres'] ?? '');
        $telefoonnummer = trim($_POST['nummer'] ?? '');
        $email = trim($_POST['email'] ?? ''); // Nieuw veld
        $geboortedatum = trim($_POST['geboortedatum'] ?? ''); // Nieuw veld
        $wachtwoord = trim($_POST['wachtwoord'] ?? '');
        $wachtwoord_bevestiging = trim($_POST['wachtwoord_bevestiging'] ?? '');
        
        // Validatie
        if (empty($naam) || empty($adres) || empty($telefoonnummer) || empty($email) || empty($wachtwoord)) {
            $response['message'] = "Alle velden zijn verplicht!";
        } elseif ($wachtwoord !== $wachtwoord_bevestiging) {
            $response['message'] = "Wachtwoorden komen niet overeen!";
        } elseif (strlen($wachtwoord) < 8) {
            $response['message'] = "Wachtwoord moet minimaal 8 tekens lang zijn!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = "Voer een geldig e-mailadres in!";
        } else {
            // Controleren of gebruiker al bestaat
            $stmt = $conn->prepare("SELECT klant_id FROM klant WHERE email = ?");
            if (!$stmt) {
                throw new Exception("SQL prepare error: " . $conn->error);
            }
            
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                throw new Exception("SQL execute error: " . $stmt->error);
            }
            
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $response['message'] = "E-mailadres is al in gebruik!";
            } else {
                // Wachtwoord hashen
                $hashed_wachtwoord = password_hash($wachtwoord, PASSWORD_DEFAULT);
                
                // Klant toevoegen aan database
                $insert_stmt = $conn->prepare("INSERT INTO klant (naam, adres, email, telefoonnummer, geboortedatum, wachtwoord) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$insert_stmt) {
                    throw new Exception("SQL prepare error: " . $conn->error);
                }
                
                // Standaardwaarde voor geboortedatum als deze leeg is
                if (empty($geboortedatum)) {
                    $geboortedatum = date('Y-m-d'); // Huidige datum als placeholder
                }
                
                $insert_stmt->bind_param("ssssss", $naam, $adres, $email, $telefoonnummer, $geboortedatum, $hashed_wachtwoord);
                
                if ($insert_stmt->execute()) {
                    // Gebruik de login_user functie
                    $user_id = $conn->insert_id;
                    login_user($user_id, $naam);
                    
                    $response['success'] = true;
                    $response['redirect'] = 'dashboard.php';
                } else {
                    throw new Exception("Registratie mislukt: " . $insert_stmt->error);
                }
                $insert_stmt->close();
            }
            $stmt->close();
        }
    }
} catch (Exception $e) {
    // Log de volledige fout voor debugging
    error_log("Registratie fout: " . $e->getMessage());
    $response['message'] = "Er is een fout opgetreden: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
?>