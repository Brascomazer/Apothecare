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
    <nav>
        <a href="index.php" <?php echo ($current_page == 'index.php' || $current_page == 'index.php') ? 'class="active"' : ''; ?>>Home</a>
        <a href="medicijnen.php" <?php echo ($current_page == 'medicijnen.php') ? 'class="active"' : ''; ?>>Medicijnen</a>
        <a href="over_ons.php" <?php echo ($current_page == 'over_ons.php') ? 'class="active"' : ''; ?>>Over Ons</a>
        <a href="bestellingen.php" <?php echo ($current_page == 'bestellingen.php') ? 'class="active"' : ''; ?>>Bestellingen</a>
        <a href="winkelwagen.php" <?php echo ($current_page == 'winkelwagen.php') ? 'class="active"' : ''; ?>>Winkelwagen</a>
        <a href="hulp.php" <?php echo ($current_page == 'hulp.php') ? 'class="active"' : ''; ?>>Hulp</a>
        <?php if ($is_logged_in): ?>
            <a href="dashboard.php" <?php echo ($current_page == 'dashboard.php') ? 'class="active"' : ''; ?>>Mijn Account</a>
            <a href="../api/logout.php" class="logout-btn">Uitloggen</a>
        <?php endif; ?>
    </nav>
</header>