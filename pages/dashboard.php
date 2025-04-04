<?php
session_start();
require '../includes/session_helper.php';

// Controleer of gebruiker is ingelogd
require_login();
start_secure_session();

require '../includes/db_connect.php';

// Haal gebruikersinfo op
$user_id = $_SESSION['gebruiker_id'];
$stmt = $conn->prepare("SELECT * FROM klant WHERE klant_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Haal recente bestellingen op (indien aanwezig)
$orders_query = "SELECT * FROM bestelling WHERE klant_id = ? ORDER BY datum DESC LIMIT 5";
$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$recent_orders = $orders_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard-welcome">
            <h1>Welkom, <?php echo htmlspecialchars($_SESSION['gebruiker_naam']); ?>!</h1>
            <p>Bekijk en beheer je account, bestellingen en persoonlijke gegevens.</p>
        </div>
        
        <div class="dashboard-container">
            <div class="dashboard-card">
                <h2><i class="fas fa-user-circle"></i> Mijn gegevens</h2>
                <div class="user-info">
                    <p><strong>Naam:</strong> <?php echo htmlspecialchars($user['naam']); ?></p>
                    <p><strong>E-mail:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Adres:</strong> <?php echo htmlspecialchars($user['adres']); ?></p>
                    <p><strong>Telefoonnummer:</strong> <?php echo htmlspecialchars($user['telefoonnummer']); ?></p>
                    <p><strong>Geboortedatum:</strong> <?php echo date("d-m-Y", strtotime($user['geboortedatum'])); ?></p>
                </div>
                <div class="dashboard-actions">
                    <a href="edit_profile.php" class="btn btn-outline"><i class="fas fa-edit"></i> Gegevens wijzigen</a>
                    <a href="#" class="btn btn-outline"><i class="fas fa-key"></i> Wachtwoord wijzigen</a>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h2><i class="fas fa-shopping-bag"></i> Recente bestellingen</h2>
                
                <?php if ($recent_orders->num_rows > 0): ?>
                    <div class="orders-list">
                        <?php while($order = $recent_orders->fetch_assoc()): ?>
                            <div class="order-item">
                                <div class="order-details">
                                    <span class="order-number">Bestelling #<?php echo $order['bestelling_id']; ?></span>
                                    <span class="order-date"><?php echo date("d-m-Y", strtotime($order['datum'])); ?></span>
                                </div>
                                <span class="order-status <?php echo $order['status'] == 'verzonden' ? 'status-completed' : 'status-processing'; ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="dashboard-actions">
                        <a href="bestellingen.php" class="btn btn-outline"><i class="fas fa-list"></i> Alle bestellingen</a>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <p>Je hebt nog geen bestellingen geplaatst.</p>
                        <div class="dashboard-actions">
                            <a href="medicijnen.php" class="btn btn-outline"><i class="fas fa-pills"></i> Bekijk medicijnen</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="dashboard-card">
                <h2><i class="fas fa-clipboard-list"></i> Snelle navigatie</h2>
                <div class="dashboard-actions" style="margin-top: 0;">
                    <a href="medicijnen.php" class="btn btn-outline"><i class="fas fa-pills"></i> Medicijnen</a>
                    <a href="winkelwagen.php" class="btn btn-outline"><i class="fas fa-shopping-cart"></i> Winkelwagen</a>
                    <a href="bestellingen.php" class="btn btn-outline"><i class="fas fa-box"></i> Bestellingen</a>
                    <a href="contact.php" class="btn btn-outline"><i class="fas fa-envelope"></i> Contact</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>