<?php
// filepath: c:\xampp\htdocs\Apothecare\admin\admin_gebruikers.php
session_start();
require_once '../includes/db_connect.php';
include 'includes/admin_header.php';

$message = '';
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Delete confirmation
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $user_id_to_delete = (int)$_POST['user_id'];
    
    // Check eerst of de klant bestellingen heeft
    $check_sql = "SELECT COUNT(*) as count FROM bestelling WHERE klant_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id_to_delete);
    $check_stmt->execute();
    $result = $check_stmt->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        $message = '<div class="alert alert-error">Deze klant heeft bestellingen en kan niet worden verwijderd</div>';
    } else {
        // Verwijder de klant als er geen bestellingen zijn
        $delete_sql = "DELETE FROM klant WHERE klant_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $user_id_to_delete);
        
        if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
            $message = '<div class="alert alert-success">Klant succesvol verwijderd</div>';
            $user_id = 0; // Reset user_id om terug te gaan naar de lijst
        } else {
            $message = '<div class="alert alert-error">Fout bij verwijderen: ' . $delete_stmt->error . '</div>';
        }
    }
}

// Fetch paginated user list
if (!$user_id) {
    $per_page = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $per_page;
    
    // Count total users
    $count_sql = "SELECT COUNT(*) as count FROM klant";
    $count_result = $conn->query($count_sql);
    $count_row = $count_result->fetch_assoc();
    $total_users = $count_row['count'];
    $total_pages = ceil($total_users / $per_page);
    
    // Fetch users for current page
    $users_sql = "SELECT klant_id, naam, email, adres, telefoonnummer, geboortedatum FROM klant ORDER BY naam LIMIT ? OFFSET ?";
    $users_stmt = $conn->prepare($users_sql);
    $users_stmt->bind_param("ii", $per_page, $offset);
    $users_stmt->execute();
    $users = $users_stmt->get_result();
} else {
    // Fetch specific user details
    $user_sql = "SELECT k.*, COUNT(b.bestelling_id) as order_count 
                FROM klant k 
                LEFT JOIN bestelling b ON k.klant_id = b.klant_id 
                WHERE k.klant_id = ? 
                GROUP BY k.klant_id";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        $message = '<div class="alert alert-error">Klant niet gevonden</div>';
        $user_id = 0;
    } else {
        // Fetch user's orders
        $orders_sql = "SELECT * FROM bestelling WHERE klant_id = ? ORDER BY datum DESC";
        $orders_stmt = $conn->prepare($orders_sql);
        $orders_stmt->bind_param("i", $user_id);
        $orders_stmt->execute();
        $orders = $orders_stmt->get_result();
    }
}
?>

<div class="admin-page-title">
    <h1><?php echo $user_id ? "Klantgegevens: " . htmlspecialchars($user['naam']) : "Klanten beheren"; ?></h1>
</div>

<?php echo $message; ?>

<?php if ($user_id && $user): ?>
    <!-- Gebruikersdetails -->
    <div class="admin-grid">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Klantgegevens</h2>
                <a href="admin_gebruikers.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i> Terug naar klantenlijst
                </a>
            </div>
            <div class="admin-card-body">
                <div class="user-details">
                    <p><strong>ID:</strong> <?php echo $user['klant_id']; ?></p>
                    <p><strong>Naam:</strong> <?php echo htmlspecialchars($user['naam']); ?></p>
                    <p><strong>E-mail:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Adres:</strong> <?php echo htmlspecialchars($user['adres']); ?></p>
                    <p><strong>Telefoonnummer:</strong> <?php echo htmlspecialchars($user['telefoonnummer']); ?></p>
                    <p><strong>Geboortedatum:</strong> <?php echo date('d-m-Y', strtotime($user['geboortedatum'])); ?></p>
                    <p><strong>Aantal bestellingen:</strong> <?php echo $user['order_count']; ?></p>
                </div>
                
                <?php if ($user['order_count'] == 0): ?>
                    <div class="admin-form-actions">
                        <form method="post" onsubmit="return confirm('Weet je zeker dat je deze klant wilt verwijderen?');">
                            <input type="hidden" name="user_id" value="<?php echo $user['klant_id']; ?>">
                            <button type="submit" name="delete_user" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Klant verwijderen
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($user['order_count'] > 0): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2>Bestellingen</h2>
                </div>
                <div class="admin-card-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Bestelling #</th>
                                <th>Datum</th>
                                <th>Status</th>
                                <th>Actie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['bestelling_id']; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($order['datum'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $order['status']; ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="admin_bestellingen.php?id=<?php echo $order['bestelling_id']; ?>" class="btn-icon" title="Details bekijken">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <!-- Gebruikerslijst -->
    <div class="admin-card">
        <div class="admin-card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>E-mail</th>
                        <th>Telefoonnummer</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['klant_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['naam']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['telefoonnummer']); ?></td>
                                <td>
                                    <a href="?id=<?php echo $user['klant_id']; ?>" class="btn-icon" title="Details bekijken">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">Geen klanten gevonden</td>
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
.user-details {
    max-width: 600px;
}
.user-details p {
    display: flex;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}
.user-details p strong {
    width: 150px;
    font-weight: 600;
}
.btn-danger {
    background-color: var(--admin-danger);
    color: white;
}
.btn-danger:hover {
    background-color: #d04215;
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