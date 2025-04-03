<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once __DIR__ . '/session_helper.php';
$is_logged_in = is_logged_in();

// Bepaal huidige pagina voor 'active' class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header>
    <div class="header-container container">
        <div class="logo">
            <a href="index.php">
                <span>Apothecare</span>
            </a>
        </div>
        
        <button class="menu-toggle" id="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <nav id="nav-menu">
            <a href="index.php" <?php echo ($current_page == 'index.php') ? 'class="active"' : ''; ?>>Home</a>
            <a href="medicijnen.php" <?php echo ($current_page == 'medicijnen.php') ? 'class="active"' : ''; ?>>Medicijnen</a>
            <a href="over_ons.php" <?php echo ($current_page == 'over_ons.php') ? 'class="active"' : ''; ?>>Over Ons</a>
            <a href="bestellingen.php" <?php echo ($current_page == 'bestellingen.php') ? 'class="active"' : ''; ?>>Bestellingen</a>
            <a href="winkelwagen.php" <?php echo ($current_page == 'winkelwagen.php') ? 'class="active"' : ''; ?>>Winkelwagen</a>
            <a href="contact.php" <?php echo ($current_page == 'contact.php') ? 'class="active"' : ''; ?>>Contact</a>
            
            <?php if ($is_logged_in): ?>
                <a href="dashboard.php" <?php echo ($current_page == 'dashboard.php') ? 'class="active"' : ''; ?>>Mijn Account</a>
                <a href="../api/logout.php" class="logout-btn">Uitloggen</a>
            <?php else: ?>
                <a href="login.php" <?php echo ($current_page == 'login.php') ? 'class="active"' : ''; ?>>Inloggen</a>
                <a href="register.php" <?php echo ($current_page == 'register.php') ? 'class="active"' : ''; ?>>Registreren</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<script>
    // Menu toggle voor mobiele weergave
    document.getElementById('menu-toggle').addEventListener('click', function() {
        document.getElementById('nav-menu').classList.toggle('active');
    });
</script>