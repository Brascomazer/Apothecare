
<?php
include 'db_connection.php';

$total = 0;
$sql = "SELECT SUM(prijs * aantal) as total FROM winkelwagen";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total = $row['total'] ?? 0;
}

echo number_format($total, 2);
$conn->close();
?>