<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelwagen - Medicijnen Webshop</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

    <main>
        <h1>Winkelwagen</h1>
        <div class="cart-container">
            <form action="../api/process_cart.php" method="post">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Aantal</th>
                            <th>Prijs</th>
                            <th>Verwijderen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php include '../api/display_cart.php'; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">Totaal:</td>
                            <td class="total-price">€<?php include '../api/calculate_total.php'; ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="checkout-button">
                    <button type="submit" name="checkout">Door gaan naar betaling</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <nav>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="over_ons.html">Over Ons</a></li>
                <li><a href="service.html">Service & contact</a></li>
            </ul>
        </nav>
        <p>&copy; 2025 Medicijnen Webshop</p>
    </footer>
</body>
</html>