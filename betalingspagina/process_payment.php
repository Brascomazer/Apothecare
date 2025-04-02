<?php
header('Content-Type: application/json');

// Database configuratie
$servername = "localhost";
$username = "username"; // Vervang met je database gebruikersnaam
$password = "password"; // Vervang met je database wachtwoord
$dbname = "medicijnen_webshop";

try {
    // Maak verbinding met de database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ontvang de POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Valideer de data
    if (!isset($data['cartItems']) || !isset($data['totalPrice']) || !isset($data['selectedBank'])) {
        throw new Exception('Ongeldige betalingsgegevens ontvangen.');
    }

    // Begin een transactie
    $conn->beginTransaction();

    // 1. Sla de bestelling op in de orders tabel
    $stmt = $conn->prepare("INSERT INTO orders (total_price, payment_method, order_date) VALUES (:total_price, :payment_method, NOW())");
    $stmt->execute([
        ':total_price' => $data['totalPrice'],
        ':payment_method' => $data['selectedBank']
    ]);
    $orderId = $conn->lastInsertId();

    // 2. Sla de order items op in de order_items tabel
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (:order_id, :product_name, :quantity, :price)");
    
    foreach ($data['cartItems'] as $item) {
        $stmt->execute([
            ':order_id' => $orderId,
            ':product_name' => $item['product'],
            ':quantity' => $item['quantity'],
            ':price' => $item['price']
        ]);
    }

    // 3. Maak de winkelwagen leeg (als je een winkelwagen tabel hebt)
    // $stmt = $conn->prepare("DELETE FROM shopping_cart WHERE user_id = :user_id");
    // $stmt->execute([':user_id' => $_SESSION['user_id']]);

    // Commit de transactie
    $conn->commit();

    // Stuur een succesvolle response terug
    echo json_encode([
        'success' => true,
        'orderId' => $orderId
    ]);

} catch (Exception $e) {
    // Rollback bij een fout
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>