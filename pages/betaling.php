<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Betalen - Medicijnen Webshop</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
</head>
<body>
<?php include '../includes/header.php'; ?>

    <main id="app">
        <h1>Winkelwagen</h1>
        
        <div class="cart-items">
            <div v-for="(item, index) in cartItems" :key="index" class="cart-item">
                <span class="product-name">{{ item.product }}</span>
                <span class="product-quantity">{{ item.quantity }}</span>
                <span class="product-price">€{{ item.price.toFixed(2) }}</span>
                <span class="product-remove" @click="removeItem(index)">❌</span>
            </div>
        </div>

        <div class="total">
            <strong>Totaal:</strong> €{{ totalPrice.toFixed(2) }}
        </div>

        <div class="payment-section">
            <h2>Met welke bank wilt u betalen:</h2>
            <select v-model="selectedBank">
                <option value="ING">ING Bank</option>
                <option value="ABN">ABN Amro</option>
                <option value="Rabobank">Rabobank</option>
                <option value="SNS">SNS Bank</option>
            </select>
            
            <button @click="processPayment" class="pay-button">Betalen</button>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Medicijnen Webshop</p>
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">Over Ons</a></li>
            <li><a href="service.html">Service & contact</a></li>
        </ul>
    </footer>

    <script src="../assets/js/betaling.js"></script>
</body>
</html>