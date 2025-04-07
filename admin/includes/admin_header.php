<?php
// Controleer of admin is ingelogd
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Huidige pagina bepalen voor actieve status
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneel - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="admin-body">
    <div class="admin-sidebar">
        <div class="admin-logo">
            <i class="fas fa-clinic-medical"></i>
            <span>Apothecare</span>
        </div>
        <nav class="admin-nav">
            <a href="admin_dashboard.php" <?php echo ($current_page == 'admin_dashboard.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="admin_medicijnen.php" <?php echo ($current_page == 'admin_medicijnen.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-pills"></i> Medicijnen
            </a>
            <a href="admin_bestellingen.php" <?php echo ($current_page == 'admin_bestellingen.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-shopping-cart"></i> Bestellingen
            </a>
            <a href="admin_voorraad.php" <?php echo ($current_page == 'admin_voorraad.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-boxes"></i> Voorraad
            </a>
            <a href="admin_gebruikers.php" <?php echo ($current_page == 'admin_gebruikers.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-users"></i> Klanten
            </a>
            <a href="admin_instellingen.php" <?php echo ($current_page == 'admin_instellingen.php') ? 'class="active"' : ''; ?>>
                <i class="fas fa-cog"></i> Instellingen
            </a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="admin_logout.php" class="admin-logout">
                <i class="fas fa-sign-out-alt"></i> Uitloggen
            </a>
        </div>
    </div>
    
    <div class="admin-content">
        <div class="admin-topbar">
            <div class="admin-toggle">
                <i class="fas fa-bars"></i>
            </div>
            <div class="admin-user">
                <span><?php echo htmlspecialchars($_SESSION['admin_naam']); ?></span>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>
        
        <div class="admin-container">