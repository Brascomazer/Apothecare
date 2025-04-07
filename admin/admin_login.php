<?php
session_start();
require_once '../includes/db_connect.php';

// Check of admin al is ingelogd
if(isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Debug informatie
    echo "Attempting login with: $username <br>";
    
    if (empty($username) || empty($password)) {
        $error = "Vul alle velden in";
    } else {
        // Admin ophalen uit database
        $stmt = $conn->prepare("SELECT admin_id, gebruikersnaam, wachtwoord FROM admin WHERE gebruikersnaam = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "Database records found: " . $result->num_rows . "<br>";
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            echo "Stored hash: " . $admin['wachtwoord'] . "<br>";
            echo "Password verification result: " . (password_verify($password, $admin['wachtwoord']) ? "true" : "false") . "<br>";
            
            // Wachtwoord verifiëren
            if (password_verify($password, $admin['wachtwoord'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_naam'] = $admin['gebruikersnaam'];
                
                header('Location: admin_dashboard.php');
                exit();
            } else {
                $error = "Onjuiste inloggegevens";
            }
        } else {
            $error = "Onjuiste inloggegevens";
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="admin-login-body">
    <div class="admin-login-container">
        <div class="admin-login-box">
            <div class="admin-login-header">
                <h1><i class="fas fa-clinic-medical"></i> Apothecare Admin</h1>
                <p>Log in om het admin paneel te beheren</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="admin_login.php" method="post" class="admin-login-form">
                <div class="form-group">
                    <label for="username">Gebruikersnaam</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Wachtwoord</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i> Inloggen
                </button>
            </form>
            
            <div class="admin-login-footer">
                <a href="../pages/index.php">Terug naar de website</a>
            </div>
        </div>
    </div>
</body>
</html>