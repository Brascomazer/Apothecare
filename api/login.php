<?php
session_start();
require '../includes/db_connect.php';
require '../includes/session_helper.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['login_email']); // Aangepast van naam naar email
    $wachtwoord = trim($_POST['login_wachtwoord']);
    
    if (empty($email) || empty($wachtwoord)) {
        $response['message'] = "E-mail en wachtwoord zijn verplicht!";
    } else {
        $stmt = $conn->prepare("SELECT klant_id, naam, wachtwoord FROM klant WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $gebruiker = $result->fetch_assoc();
            
            if (password_verify($wachtwoord, $gebruiker['wachtwoord'])) {
                // Gebruik de nieuwe login_user functie
                login_user($gebruiker['klant_id'], $gebruiker['naam']);
                
                $response['success'] = true;
                $response['redirect'] = 'dashboard.php';
            } else {
                $response['message'] = "Ongeldig e-mailadres of wachtwoord!";
            }
        } else {
            $response['message'] = "Ongeldig e-mailadres of wachtwoord!";
        }
        $stmt->close();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>