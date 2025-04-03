<?php
session_start();
require '../includes/session_helper.php';

header('Content-Type: application/json');

if (is_logged_in()) {
    echo json_encode([
        'loggedIn' => true,
        'username' => $_SESSION['gebruiker_naam']
    ]);
} else {
    echo json_encode([
        'loggedIn' => false
    ]);
}
?>