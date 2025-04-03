<?php
session_start();
require '../includes/session_helper.php';

// Zorg dat uitlog functie wordt aangeroepen
logout_user();

// Redirect naar home met success message
header("Location: ../pages/index.php?logout=success");
exit();
?>