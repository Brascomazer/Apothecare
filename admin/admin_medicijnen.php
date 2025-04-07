<?php
session_start();
require_once '../includes/db_connect.php';
include 'includes/admin_header.php';

// Kijken of er een actie moet worden uitgevoerd (toevoegen, bewerken, verwijderen)
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';
$medicijn = null;

// Verwerken van formulieren
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_medicijn']) || isset($_POST['update_medicijn'])) {
        $naam = $_POST['naam'] ?? '';
        $beschrijving = $_POST['beschrijving'] ?? '';
        $prijs = isset($_POST['prijs']) ? floatval($_POST['prijs']) : 0;
        $hoeveelheid = isset($_POST['hoeveelheid']) ? intval($_POST['hoeveelheid']) : 0;
        
        if (empty($naam)) {
            $message = '<div class="alert alert-error">Naam is verplicht</div>';
        } else {
            // Medicijn toevoegen of bijwerken
            if (isset($_POST['add_medicijn'])) {
                $sql = "INSERT INTO medicijn (naam, beschrijving, prijs, hoeveelheid) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdi", $naam, $beschrijving, $prijs, $hoeveelheid);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Medicijn succesvol toegevoegd</div>';
                    $action = 'list'; // Terug naar lijst view
                } else {
                    $message = '<div class="alert alert-error">Fout bij toevoegen: ' . $stmt->error . '</div>';
                }
            } else if (isset($_POST['update_medicijn']) && isset($_POST['medicijn_id'])) {
                $medicijn_id = intval($_POST['medicijn_id']);
                $sql = "UPDATE medicijn SET naam = ?, beschrijving = ?, prijs = ?, hoeveelheid = ? WHERE medicijn_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssdii", $naam, $beschrijving, $prijs, $hoeveelheid, $medicijn_id);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Medicijn succesvol bijgewerkt</div>';
                    $action = 'list'; // Terug naar lijst view
                } else {
                    $message = '<div class="alert alert-error">Fout bij bijwerken: ' . $stmt->error . '</div>';
                }
            }
        }
    } else if (isset($_POST['delete_medicijn']) && isset($_POST['medicijn_id'])) {
        $medicijn_id = intval($_POST['medicijn_id']);
        
        // Controleer eerst of het medicijn in gebruik is in bestellingen
        $check_sql = "SELECT COUNT(*) as count FROM bestelling_medicijn WHERE medicijn_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $medicijn_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            $message = '<div class="alert alert-error">Dit medicijn kan niet worden verwijderd omdat het in bestellingen wordt gebruikt</div>';
        } else {
            $sql = "DELETE FROM medicijn WHERE medicijn_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $medicijn_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Medicijn succesvol verwijderd</div>';
            } else {
                $message = '<div class="alert alert-error">Fout bij verwijderen: ' . $stmt->error . '</div>';
            }
        }
    }
}

// Als we een specifiek medicijn bewerken
if ($action === 'edit' && isset($_GET['id'])) {
    $medicijn_id = intval($_GET['id']);
    $sql = "SELECT * FROM medicijn WHERE medicijn_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $medicijn_id);
    $stmt->execute();
    $medicijn = $stmt->get_result()->fetch_assoc();
    
    if (!$medicijn) {
        $message = '<div class="alert alert-error">Medicijn niet gevonden</div>';
        $action = 'list';
    }
}

// Alle medicijnen ophalen voor de lijst
if ($action === 'list') {
    $medicijnen_query = "SELECT * FROM medicijn ORDER BY naam ASC";
    $medicijnen_result = $conn->query($medicijnen_query);
}
?>

<div class="admin-page-title">
    <h1>
        <?php if ($action === 'add'): ?>
            Medicijn toevoegen
        <?php elseif ($action === 'edit'): ?>
            Medicijn bewerken
        <?php else: ?>
            Medicijnen beheren
        <?php endif; ?>
    </h1>
</div>

<?php echo $message; ?>

<?php if ($action === 'list'): ?>
    <div class="admin-actions">
        <a href="?action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nieuw medicijn
        </a>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-body">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Naam</th>
                        <th>Prijs</th>
                        <th>Voorraad</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($medicijnen_result->num_rows > 0): ?>
                        <?php while($medicijn = $medicijnen_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $medicijn['medicijn_id']; ?></td>
                                <td><?php echo htmlspecialchars($medicijn['naam']); ?></td>
                                <td>€<?php echo number_format($medicijn['prijs'], 2, ',', '.'); ?></td>
                                <td>
                                    <span class="stock-badge <?php echo $medicijn['hoeveelheid'] <= 5 ? 'critical' : ($medicijn['hoeveelheid'] <= 10 ? 'warning' : 'success'); ?>">
                                        <?php echo $medicijn['hoeveelheid']; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="?action=edit&id=<?php echo $medicijn['medicijn_id']; ?>" class="btn-icon" title="Bewerken">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="post" class="delete-form" onsubmit="return confirm('Weet je zeker dat je dit medicijn wilt verwijderen?');">
                                        <input type="hidden" name="medicijn_id" value="<?php echo $medicijn['medicijn_id']; ?>">
                                        <button type="submit" name="delete_medicijn" class="btn-icon delete" title="Verwijderen">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">Geen medicijnen gevonden</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <div class="admin-card">
        <div class="admin-card-body">
            <form method="post" class="admin-form">
                <?php if ($action === 'edit'): ?>
                    <input type="hidden" name="medicijn_id" value="<?php echo $medicijn['medicijn_id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="naam">Naam</label>
                    <input type="text" id="naam" name="naam" value="<?php echo $action === 'edit' ? htmlspecialchars($medicijn['naam']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="beschrijving">Beschrijving</label>
                    <textarea id="beschrijving" name="beschrijving" rows="4"><?php echo $action === 'edit' ? htmlspecialchars($medicijn['beschrijving']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="prijs">Prijs (€)</label>
                    <input type="number" id="prijs" name="prijs" step="0.01" min="0" value="<?php echo $action === 'edit' ? $medicijn['prijs'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="hoeveelheid">Voorraad</label>
                    <input type="number" id="hoeveelheid" name="hoeveelheid" min="0" value="<?php echo $action === 'edit' ? $medicijn['hoeveelheid'] : ''; ?>" required>
                </div>
                
                <div class="admin-form-actions">
                    <a href="admin_medicijnen.php" class="btn btn-outline">Annuleren</a>
                    <button type="submit" name="<?php echo $action === 'add' ? 'add_medicijn' : 'update_medicijn'; ?>" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Medicijn toevoegen' : 'Medicijn bijwerken'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/admin_footer.php'; ?>