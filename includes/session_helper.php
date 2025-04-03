<?php
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vernieuw de sessie-ID na inloggen voor veiligheid
    if (isset($_SESSION['needs_regeneration']) && $_SESSION['needs_regeneration']) {
        session_regenerate_id(true);
        $_SESSION['needs_regeneration'] = false;
    }
    
    // Controleer sessie-timeout
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 3600) {
        // Sessie is ouder dan 1 uur, uitloggen
        logout_user();
        header("Location: ../pages/index.html?timeout=1");
        exit();
    }
    
    // Update laatste activiteit
    $_SESSION['last_activity'] = time();
}

function is_logged_in() {
    return isset($_SESSION['gebruiker_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: ../pages/index.html");
        exit();
    }
}

function login_user($user_id, $user_name) {
    $_SESSION['gebruiker_id'] = $user_id;
    $_SESSION['gebruiker_naam'] = $user_name;
    $_SESSION['needs_regeneration'] = true;
    $_SESSION['last_activity'] = time();
}

function logout_user() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}
?>