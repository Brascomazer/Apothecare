<?php
session_start();
require 'db_connect.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam = trim($_POST['login_naam']);
    $wachtwoord = trim($_POST['login_wachtwoord']);
    
    if (empty($naam) || empty($wachtwoord)) {
        $response['message'] = "Gebruikersnaam en wachtwoord zijn verplicht!";
    } else {
        $stmt = $conn->prepare("SELECT id, naam, wachtwoord FROM gebruikers WHERE naam = ?");
        $stmt->bind_param("s", $naam);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $gebruiker = $result->fetch_assoc();
            
            if (password_verify($wachtwoord, $gebruiker['wachtwoord'])) {
                $_SESSION['gebruiker_id'] = $gebruiker['id'];
                $_SESSION['gebruiker_naam'] = $gebruiker['naam'];
                $response['success'] = true;
                $response['redirect'] = 'dashboard.php';
            } else {
                $response['message'] = "Ongeldige gebruikersnaam of wachtwoord!";
            }
        } else {
            $response['message'] = "Ongeldige gebruikersnaam of wachtwoord!";
        }
        $stmt->close();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>