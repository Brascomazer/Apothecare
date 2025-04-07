<?php
// filepath: c:\xampp\htdocs\Apothecare\admin\admin_logout.php
session_start();

// Admin sessievariabelen wissen
$_SESSION = array();

// Sessie cookie verwijderen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Sessie vernietigen
session_destroy();

// Redirect naar login pagina
header("Location: admin_login.php");
exit();
?>