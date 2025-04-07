<?php
// filepath: c:\xampp\htdocs\Apothecare\admin\admin_voorraad.php
session_start();
require_once '../includes/db_connect.php';
include 'includes/admin_header.php';

$message = '';
$medicijn_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Update voorraad als formulier is verzonden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_voorraad'])) {
    $medicijn_id = (int)$_POST['medicijn_id'];
    $nieuwe_voorraad = (int)$_POST['hoeveelheid'];
    $reden = $_POST['reden'] ?? 'Handmatige aanpassing';
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update voorraad in medicijn tabel
        $update_sql = "UPDATE medicijn SET hoeveelheid = ? WHERE medicijn_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $nieuwe_voorraad, $medicijn_id);
        $update_stmt->execute();
        
        // Log de voorraadwijziging voor historische data
        $log_sql = "INSERT INTO voorraad_log (medicijn_id, oude_voorraad, nieuwe_voorraad, reden, admin_id, datum) VALUES (?, ?, ?, ?, ?, NOW())";
        $log_stmt = $conn->prepare($log_sql);
        
        // Haal oude voorraad op
        $voorraad_sql = "SELECT hoeveelheid FROM medicijn WHERE medicijn_id = ?";
        $voorraad_stmt = $conn->prepare($voorraad_sql);
        $voorraad_stmt->bind_param("i", $medicijn_id);
        $voorraad_stmt->execute();
        $oude_voorraad = $voorraad_stmt->get_result()->fetch_assoc()['hoeveelheid'];
        
        $admin_id = $_SESSION['admin_id'];
        $log_stmt->bind_param("iiisi", $medicijn_id, $oude_voorraad, $nieuwe_voorraad, $reden, $admin_id);
        $log_stmt->execute();
        
        // Commit de transactie
        $conn->commit();
        
        $message = '<div class="alert alert-success">Voorraad succesvol bijgewerkt</div>';
    } catch (Exception $e) {
        // Bij fouten, transactie terugdraaien
        $conn->rollback();
        $message = '<div class="alert alert-error">Fout bij bijwerken voorraad: ' . $e->getMessage() . '</div>';
    }
}

// Query voor alle medicijnen met lage voorraad
$low_stock_sql = "SELECT * FROM medicijn WHERE hoeveelheid < 10 ORDER BY hoeveelheid ASC";
$low_stock_result = $conn->query($low_stock_sql);

// Query voor alle medicijnen met normale voorraad
$normal_stock_sql = "SELECT * FROM medicijn WHERE hoeveelheid >= 10 ORDER BY naam ASC";
$normal_stock_result = $conn->query($normal_stock_sql);

// Als we een specifiek medicijn aan het bewerken zijn
if ($medicijn_id) {
    $medicijn_sql = "SELECT * FROM medicijn WHERE medicijn_id = ?";
    $medicijn_stmt = $conn->prepare($medicijn_sql);
    $medicijn_stmt->bind_param("i", $medicijn_id);
    $medicijn_stmt->execute();
    $medicijn = $medicijn_stmt->get_result()->fetch_assoc();
    
    if (!$medicijn) {
        $message = '<div class="alert alert-error">Medicijn niet gevonden</div>';
        $medicijn_id = 0;
    }
}
?>

<div class="admin-page-title">
    <h1><?php echo $medicijn_id ? "Voorraad bijwerken: " . htmlspecialchars($medicijn['naam']) : "Voorraad beheren"; ?></h1>
</div>

<?php echo $message; ?>

<?php if ($medicijn_id && $medicijn): ?>
    <!-- Voorraad bijwerken voor een specifiek medicijn -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Voorraad bijwerken</h2>
            <a href="admin_voorraad.php" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> Terug naar voorraadoverzicht
            </a>
        </div>
        <div class="admin-card-body">
            <form method="post" class="admin-form">
                <input type="hidden" name="medicijn_id" value="<?php echo $medicijn['medicijn_id']; ?>">
                
                <div class="form-group">
                    <label for="hoeveelheid">Huidige voorraad</label>
                    <input type="number" id="hoeveelheid" name="hoeveelheid" value="<?php echo $medicijn['hoeveelheid']; ?>" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="reden">Reden voor aanpassing</label>
                    <select id="reden" name="reden" required>
                        <option value="Nieuwe levering">Nieuwe levering</option>
                        <option value="Voorraadcorrectie">Voorraadcorrectie</option>
                        <option value="Verlopen producten">Verlopen producten</option>
                        <option value="Beschadigde producten">Beschadigde producten</option>
                        <option value="Anders">Anders</option>
                    </select>
                </div>
                
                <div class="admin-form-actions">
                    <a href="admin_voorraad.php" class="btn btn-outline">Annuleren</a>
                    <button type="submit" name="update_voorraad" class="btn btn-primary">
                        <i class="fas fa-save"></i> Voorraad bijwerken
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Voorraadoverzicht -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Lage voorraad producten</h2>
        </div>
        <div class="admin-card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>Voorraad</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($low_stock_result->num_rows > 0): ?>
                        <?php while($item = $low_stock_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $item['medicijn_id']; ?></td>
                                <td><?php echo htmlspecialchars($item['naam']); ?></td>
                                <td>
                                    <span class="stock-badge <?php echo $item['hoeveelheid'] <= 5 ? 'critical' : 'warning'; ?>">
                                        <?php echo $item['hoeveelheid']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?id=<?php echo $item['medicijn_id']; ?>" class="btn btn-sm btn-primary">Bijwerken</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-data">Geen producten met lage voorraad</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h2><i class="fas fa-boxes"></i> Alle voorraad</h2>
        </div>
        <div class="admin-card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>Voorraad</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($normal_stock_result->num_rows > 0): ?>
                        <?php while($item = $normal_stock_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $item['medicijn_id']; ?></td>
                                <td><?php echo htmlspecialchars($item['naam']); ?></td>
                                <td>
                                    <span class="stock-badge success">
                                        <?php echo $item['hoeveelheid']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?id=<?php echo $item['medicijn_id']; ?>" class="btn btn-sm btn-outline">Bijwerken</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-data">Geen producten gevonden</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/admin_footer.php'; ?>