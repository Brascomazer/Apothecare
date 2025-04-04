<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/session_helper.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Als er een order ID is, haal de details op
$order_details = null;
if ($order_id > 0 && is_logged_in()) {
    $klant_id = $_SESSION['gebruiker_id'];
    
    $order_sql = "SELECT b.*, COUNT(bm.medicijn_id) as aantal_items
                 FROM bestelling b
                 LEFT JOIN bestelling_medicijn bm ON b.bestelling_id = bm.bestelling_id
                 WHERE b.bestelling_id = ? AND b.klant_id = ? AND b.status = 'verzonden'
                 GROUP BY b.bestelling_id";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("ii", $order_id, $klant_id);
    $order_stmt->execute();
    $order_details = $order_stmt->get_result()->fetch_assoc();
    $order_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bedankt voor je bestelling - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="thank-you-container">
            <div class="thank-you-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>Bedankt voor je bestelling!</h1>
            
            <?php if ($order_details): ?>
                <p class="order-info">Bestelling #<?php echo $order_id; ?> is succesvol geplaatst</p>
                <div class="order-details">
                    <p>Je bestelling van <?php echo $order_details['aantal_items']; ?> item(s) wordt binnenkort verzonden.</p>
                    <p>De besteldatum is: <?php echo date('d-m-Y H:i', strtotime($order_details['datum'])); ?></p>
                </div>
            <?php else: ?>
                <p class="order-info">Je bestelling is succesvol geplaatst</p>
            <?php endif; ?>
            
            <p>We sturen je een bevestigingsmail met de details van je bestelling.</p>
            
            <div class="thank-you-actions">
                <a href="medicijnen.php" class="btn btn-outline">
                    <i class="fas fa-pills"></i> Verder winkelen
                </a>
                <a href="bestellingen.php" class="btn btn-primary">
                    <i class="fas fa-box"></i> Bekijk je bestellingen
                </a>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>