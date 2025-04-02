<?php
$servername = "localhost";
$username = "root"; // Vervang met je database gebruikersnaam
$password = ""; // Vervang met je database wachtwoord
$dbname = "apothecare_db";

// Maak verbinding
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer verbinding
if ($conn->connect_error) {
    die("Connectie mislukt: " . $conn->connect_error);
}
?>