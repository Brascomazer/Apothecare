<?php
// filepath: c:\xampp\htdocs\Apothecare\admin\admin_bestellingen.php
session_start();
require_once '../includes/db_connect.php';
include 'includes/admin_header.php';

// Order details weergeven als een specifiek ID wordt opgevraagd
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// Status bijwerken als er een formulier is verzonden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $order_id_to_update = (int)$_POST['order_id'];
    
    $update_sql = "UPDATE bestelling SET status = ? WHERE bestelling_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $order_id_to_update);
    
    if ($update_stmt->execute()) {
        $message = '<div class="alert alert-success">Status succesvol bijgewerkt</div>';
    } else {
        $message = '<div class="alert alert-error">Fout bij bijwerken status: ' . $update_stmt->error . '</div>';
    }
}

// Bepaal pagina titel en inhoud op basis van of we een specifieke bestelling bekijken
if ($order_id) {
    $page_title = "Bestelgegevens #" . $order_id;
    
    // Haal bestelgegevens op
    $order_sql = "SELECT b.*, k.naam as klant_naam, k.email, k.adres, k.telefoonnummer
                 FROM bestelling b
                 JOIN klant k ON b.klant_id = k.klant_id
                 WHERE b.bestelling_id = ?";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order = $order_stmt->get_result()->fetch_assoc();
    
    // Haal bestelitems op
    $items_sql = "SELECT bm.*, m.naam, m.prijs 
                 FROM bestelling_medicijn bm
                 JOIN medicijn m ON bm.medicijn_id = m.medicijn_id
                 WHERE bm.bestelling_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items = $items_stmt->get_result();
    
    $totaal = 0;
    $items_list = [];
    while ($item = $items->fetch_assoc()) {
        $totaal += $item['prijs'] * $item['hoeveelheid'];
        $items_list[] = $item;
    }
    
} else {
    $page_title = "Bestellingen beheren";
    
    // Haal alle bestellingen op met paginering
    $per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $per_page;
    
    $count_sql = "SELECT COUNT(*) as count FROM bestelling";
    $count_result = $conn->query($count_sql);
    $count_row = $count_result->fetch_assoc();
    $total_orders = $count_row['count'];
    $total_pages = ceil($total_orders / $per_page);
    
    $orders_sql = "SELECT b.*, k.naam as klant_naam 
                  FROM bestelling b 
                  JOIN klant k ON b.klant_id = k.klant_id 
                  ORDER BY b.datum DESC 
                  LIMIT ? OFFSET ?";
    $orders_stmt = $conn->prepare($orders_sql);
    $orders_stmt->bind_param("ii", $per_page, $offset);
    $orders_stmt->execute();
    $orders = $orders_stmt->get_result();
}
?>

<div class="admin-page-title">
    <h1><?php echo $page_title; ?></h1>
</div>

<?php echo $message; ?>

<?php if ($order_id && $order): ?>
    <!-- Besteldetails weergave -->
    <div class="admin-grid">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Bestelgegevens</h2>
                <a href="admin_bestellingen.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i> Terug naar bestellingen
                </a>
            </div>
            <div class="admin-card-body">
                <div class="order-details-grid">
                    <div class="order-info">
                        <h3>Bestelling #<?php echo $order['bestelling_id']; ?></h3>
                        <p><strong>Datum:</strong> <?php echo date('d-m-Y H:i', strtotime($order['datum'])); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status-badge <?php echo $order['status']; ?>">
                                <?php echo $order['status']; ?>
                            </span>
                        </p>
                        <form method="post" class="admin-form status-update-form">
                            <input type="hidden" name="order_id" value="<?php echo $order['bestelling_id']; ?>">
                            <div class="form-group">
                                <label>Status bijwerken:</label>
                                <select name="status">
                                    <option value="in behandeling" <?php echo $order['status'] == 'in behandeling' ? 'selected' : ''; ?>>In behandeling</option>
                                    <option value="verzonden" <?php echo $order['status'] == 'verzonden' ? 'selected' : ''; ?>>Verzonden</option>
                                    <option value="geannuleerd" <?php echo $order['status'] == 'geannuleerd' ? 'selected' : ''; ?>>Geannuleerd</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary">
                                <i class="fas fa-save"></i> Status bijwerken
                            </button>
                        </form>
                    </div>
                    <div class="customer-info">
                        <h3>Klantgegevens</h3>
                        <p><strong>Naam:</strong> <?php echo htmlspecialchars($order['klant_naam']); ?></p>
                        <p><strong>E-mail:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                        <p><strong>Adres:</strong> <?php echo htmlspecialchars($order['adres']); ?></p>
                        <p><strong>Telefoon:</strong> <?php echo htmlspecialchars($order['telefoonnummer']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Bestelde producten</h2>
            </div>
            <div class="admin-card-body">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Prijs per stuk</th>
                            <th>Aantal</th>
                            <th>Totaal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items_list as $item): ?>
                            <tr>
                                <td>
                                    <a href="admin_medicijnen.php?action=edit&id=<?php echo $item['medicijn_id']; ?>">
                                        <?php echo htmlspecialchars($item['naam']); ?>
                                    </a>
                                </td>
                                <td>€<?php echo number_format($item['prijs'], 2, ',', '.'); ?></td>
                                <td><?php echo $item['hoeveelheid']; ?></td>
                                <td>€<?php echo number_format($item['prijs'] * $item['hoeveelheid'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" style="text-align: right;">Subtotaal:</th>
                            <td>€<?php echo number_format($totaal, 2, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align: right;">Verzendkosten:</th>
                            <td>€<?php echo ($totaal >= 20) ? '0,00' : '4,95'; ?></td>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align: right;">Totaal:</th>
                            <td>€<?php echo number_format(($totaal >= 20) ? $totaal : $totaal + 4.95, 2, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
<?php else: ?>
    <!-- Lijst met alle bestellingen -->
    <div class="admin-card">
        <div class="admin-card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Bestelling #</th>
                        <th>Klant</th>
                        <th>Datum</th>
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['bestelling_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['klant_naam']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($order['datum'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $order['status']; ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?id=<?php echo $order['bestelling_id']; ?>" class="btn-icon" title="Details bekijken">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">Geen bestellingen gevonden</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="admin-pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<style>
.order-details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
.status-update-form {
    margin-top: 20px;
}
.status-update-form .form-group {
    margin-bottom: 10px;
}
.admin-pagination {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 10px;
}
.admin-pagination a {
    display: inline-block;
    padding: 5px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.admin-pagination a.active {
    background-color: var(--admin-primary);
    color: white;
    border-color: var(--admin-primary);
}
</style>

<?php include 'includes/admin_footer.php'; ?>