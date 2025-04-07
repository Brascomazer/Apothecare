<?php
session_start();
require_once '../includes/db_connect.php';
include 'includes/admin_header.php';

// Statistieken ophalen
$total_orders_query = "SELECT COUNT(*) as total FROM bestelling";
$total_orders_result = $conn->query($total_orders_query);
$total_orders = $total_orders_result->fetch_assoc()['total'];

$total_products_query = "SELECT COUNT(*) as total FROM medicijn";
$total_products_result = $conn->query($total_products_query);
$total_products = $total_products_result->fetch_assoc()['total'];

$total_customers_query = "SELECT COUNT(*) as total FROM klant";
$total_customers_result = $conn->query($total_customers_query);
$total_customers = $total_customers_result->fetch_assoc()['total'];

$low_stock_query = "SELECT COUNT(*) as total FROM medicijn WHERE hoeveelheid < 10";
$low_stock_result = $conn->query($low_stock_query);
$low_stock = $low_stock_result->fetch_assoc()['total'];

// Recente bestellingen
$recent_orders_query = "SELECT b.*, k.naam as klant_naam 
                        FROM bestelling b 
                        JOIN klant k ON b.klant_id = k.klant_id 
                        ORDER BY b.datum DESC LIMIT 5";
$recent_orders_result = $conn->query($recent_orders_query);
?>

<div class="admin-page-title">
    <h1>Admin Dashboard</h1>
    <p>Welkom bij het Apothecare Admin Paneel</p>
</div>

<div class="admin-stats">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $total_orders; ?></h3>
            <p>Totaal bestellingen</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-pills"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $total_products; ?></h3>
            <p>Medicijnen</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $total_customers; ?></h3>
            <p>Klanten</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $low_stock; ?></h3>
            <p>Lage voorraad</p>
        </div>
    </div>
</div>

<div class="admin-grid">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Recente bestellingen</h2>
            <a href="admin_bestellingen.php" class="btn btn-outline btn-sm">Alle bestellingen</a>
        </div>
        
        <div class="admin-card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Klant</th>
                        <th>Datum</th>
                        <th>Status</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_orders_result->num_rows > 0): ?>
                        <?php while($order = $recent_orders_result->fetch_assoc()): ?>
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
                                    <a href="admin_bestellingen.php?id=<?php echo $order['bestelling_id']; ?>" class="btn-icon">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">Geen recente bestellingen</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Lage voorraad medicijnen</h2>
            <a href="admin_voorraad.php" class="btn btn-outline btn-sm">Voorraad beheren</a>
        </div>
        
        <div class="admin-card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Medicijn</th>
                        <th>Voorraad</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $low_stock_items_query = "SELECT * FROM medicijn WHERE hoeveelheid < 10 ORDER BY hoeveelheid ASC LIMIT 5";
                    $low_stock_items_result = $conn->query($low_stock_items_query);
                    
                    if ($low_stock_items_result->num_rows > 0):
                        while($item = $low_stock_items_result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['naam']); ?></td>
                            <td>
                                <span class="stock-badge <?php echo $item['hoeveelheid'] <= 5 ? 'critical' : 'warning'; ?>">
                                    <?php echo $item['hoeveelheid']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="admin_voorraad.php?id=<?php echo $item['medicijn_id']; ?>" class="btn btn-sm btn-outline">Bijwerken</a>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="3" class="no-data">Geen medicijnen met lage voorraad</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>