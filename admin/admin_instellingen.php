<?php
session_start();
require_once '../includes/db_connect.php';
include 'includes/admin_header.php';

$message = '';
$admin_id = $_SESSION['admin_id'];

// Haal huidige admin gegevens op
$admin_sql = "SELECT admin_id, gebruikersnaam FROM admin WHERE admin_id = ?";
$admin_stmt = $conn->prepare($admin_sql);
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin = $admin_stmt->get_result()->fetch_assoc();

// Wachtwoord wijzigen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Valideer inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = '<div class="alert alert-error">Alle velden zijn verplicht</div>';
    } elseif ($new_password !== $confirm_password) {
        $message = '<div class="alert alert-error">Nieuwe wachtwoorden komen niet overeen</div>';
    } elseif (strlen($new_password) < 8) {
        $message = '<div class="alert alert-error">Wachtwoord moet minimaal 8 tekens lang zijn</div>';
    } else {
        // Controleer of het huidige wachtwoord correct is
        $password_sql = "SELECT wachtwoord FROM admin WHERE admin_id = ?";
        $password_stmt = $conn->prepare($password_sql);
        $password_stmt->bind_param("i", $admin_id);
        $password_stmt->execute();
        $current_hash = $password_stmt->get_result()->fetch_assoc()['wachtwoord'];
        
        if (password_verify($current_password, $current_hash)) {
            // Update het wachtwoord
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE admin SET wachtwoord = ? WHERE admin_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_hash, $admin_id);
            
            if ($update_stmt->execute()) {
                $message = '<div class="alert alert-success">Wachtwoord succesvol bijgewerkt</div>';
            } else {
                $message = '<div class="alert alert-error">Fout bij bijwerken wachtwoord: ' . $update_stmt->error . '</div>';
            }
        } else {
            $message = '<div class="alert alert-error">Huidig wachtwoord is onjuist</div>';
        }
    }
}
?>

<div class="admin-page-title">
    <h1>Instellingen</h1>
    <p>Beheer je accountinstellingen</p>
</div>

<?php echo $message; ?>

<div class="admin-grid">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2><i class="fas fa-user-shield"></i> Account informatie</h2>
        </div>
        <div class="admin-card-body">
            <div class="user-details">
                <p><strong>Admin ID:</strong> <?php echo $admin['admin_id']; ?></p>
                <p><strong>Gebruikersnaam:</strong> <?php echo htmlspecialchars($admin['gebruikersnaam']); ?></p>