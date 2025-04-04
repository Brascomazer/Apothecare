<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/session_helper.php';

// Controleer of de gebruiker ingelogd is
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Controleer of er een bestelling_id is
if (!isset($_SESSION['bestelling_id'])) {
    header('Location: winkelwagen.php');
    exit();
}

$klant_id = $_SESSION['gebruiker_id'];
$bestelling_id = $_SESSION['bestelling_id'];

// Controleer of deze bestelling van deze klant is
$check_sql = "SELECT bestelling_id FROM bestelling 
             WHERE bestelling_id = ? AND klant_id = ? AND status = 'in behandeling'";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $bestelling_id, $klant_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    header('Location: winkelwagen.php');
    exit();
}
$check_stmt->close();

// Haal de items in de winkelwagen op voor de JavaScript data
$cart_items = [];
$total_price = 0;
$items_count = 0;

$items_sql = "SELECT bm.*, m.naam, m.prijs 
             FROM bestelling_medicijn bm 
             JOIN medicijn m ON bm.medicijn_id = m.medicijn_id
             WHERE bm.bestelling_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $bestelling_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

while ($row = $items_result->fetch_assoc()) {
    $item_total = $row['prijs'] * $row['hoeveelheid'];
    $total_price += $item_total;
    $items_count += $row['hoeveelheid'];
    
    $cart_items[] = [
        'product' => $row['naam'],
        'quantity' => $row['hoeveelheid'],
        'price' => (float)$row['prijs'],
        'medicijn_id' => $row['medicijn_id']
    ];
}
$items_stmt->close();

// Haal klantgegevens op
$klant_sql = "SELECT * FROM klant WHERE klant_id = ?";
$klant_stmt = $conn->prepare($klant_sql);
$klant_stmt->bind_param("i", $klant_id);
$klant_stmt->execute();
$klant = $klant_stmt->get_result()->fetch_assoc();
$klant_stmt->close();

// Bereken verzendkosten
$shipping_cost = 4.95;
if ($total_price > 20) {
    $shipping_cost = 0;
}

$total_with_shipping = $total_price + $shipping_cost;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Betalen - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="checkout-banner">
            <h1>Betaling afronden</h1>
            <p>Laatste stap om uw bestelling te voltooien</p>
        </div>
        
        <div class="checkout-progress">
            <div class="progress-step completed">
                <div class="step-number"><i class="fas fa-check"></i></div>
                <div class="step-text">Winkelwagen</div>
            </div>
            <div class="progress-step active">
                <div class="step-number">2</div>
                <div class="step-text">Betaling</div>
            </div>
            <div class="progress-step">
                <div class="step-number">3</div>
                <div class="step-text">Bevestiging</div>
            </div>
        </div>
        
        <div class="checkout-container" id="app">
            <div class="checkout-grid">
                <div class="checkout-details">
                    <div class="checkout-section">
                        <h2><i class="fas fa-user-circle"></i> Bezorggegevens</h2>
                        <div class="customer-details">
                            <div class="customer-info-block">
                                <span class="label">Naam</span>
                                <span class="value"><?php echo htmlspecialchars($klant['naam']); ?></span>
                            </div>
                            <div class="customer-info-block">
                                <span class="label">E-mail</span>
                                <span class="value"><?php echo htmlspecialchars($klant['email']); ?></span>
                            </div>
                            <div class="customer-info-block">
                                <span class="label">Adres</span>
                                <span class="value"><?php echo htmlspecialchars($klant['adres']); ?> 
                                    <span class="checkout-address-badge">Bezorgadres</span>
                                </span>
                            </div>
                            <div class="customer-info-block">
                                <span class="label">Telefoonnummer</span>
                                <span class="value"><?php echo htmlspecialchars($klant['telefoonnummer']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkout-section">
                        <h2><i class="fas fa-credit-card"></i> Betaalmethode</h2>
                        <div class="payment-options">
                            <template v-for="(bank, index) in banks">
                                <div class="payment-option" :class="{ selected: selectedBank === bank }" @click="selectedBank = bank">
                                    <input type="radio" :id="'bank-' + index" name="bank" :value="bank" v-model="selectedBank" class="payment-option-radio">
                                    <div class="payment-radio-custom"></div>
                                    <label :for="'bank-' + index" class="payment-option-label">
                                        {{ bank }}
                                        <img v-if="bank.includes('iDEAL')" src="../assets/images/ideal-logo.png" alt="iDEAL">
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                
                <div class="order-summary">
                    <h2><i class="fas fa-shopping-basket"></i> Overzicht bestelling</h2>
                    <div class="order-items">
                        <div class="order-item" v-for="(item, index) in cartItems" :key="index">
                            <div class="order-item-details">
                                <span class="item-name">{{ item.product }}</span>
                                <span class="item-quantity">{{ item.quantity }}x</span>
                            </div>
                            <span class="item-price">€{{ formatPrice(item.price * item.quantity) }}</span>
                        </div>
                    </div>
                    
                    <div class="summary-totals">
                        <div class="summary-line">
                            <span>Subtotaal (<?php echo $items_count; ?> items)</span>
                            <span>€{{ formatPrice(totalPrice) }}</span>
                        </div>
                        <div class="summary-line">
                            <span>Verzendkosten</span>
                            <span>€{{ formatPrice(shippingCost) }}</span>
                        </div>
                        <div class="summary-line total">
                            <span>Totaal</span>
                            <span class="total-currency">€{{ formatPrice(totalWithShipping) }}</span>
                        </div>
                    </div>
                    
                    <button @click="processPayment" class="btn btn-primary btn-full">
                        <i class="fas fa-lock"></i> Betaling afronden
                    </button>
                    
                    <p class="checkout-disclaimer">
                        Door verder te gaan gaat u akkoord met onze <a href="#">algemene voorwaarden</a> en <a href="#">privacybeleid</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
    new Vue({
        el: '#app',
        data: {
            cartItems: <?php echo json_encode($cart_items); ?>,
            totalPrice: <?php echo $total_price; ?>,
            shippingCost: <?php echo $shipping_cost; ?>,
            totalWithShipping: <?php echo $total_with_shipping; ?>,
            banks: ['iDEAL - ING Bank', 'iDEAL - ABN AMRO', 'iDEAL - Rabobank', 'iDEAL - SNS Bank', 'Credit Card', 'PayPal'],
            selectedBank: 'iDEAL - ING Bank',
            bestellingId: <?php echo $bestelling_id; ?>
        },
        methods: {
            formatPrice(price) {
                return price.toFixed(2).replace('.', ',');
            },
            processPayment() {
                if (!this.selectedBank) {
                    alert('Selecteer een betaalmethode');
                    return;
                }
                
                // Verzend de betalingsgegevens naar de server
                fetch('../api/process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        bestelling_id: this.bestellingId,
                        payment_method: this.selectedBank
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect naar een bedankpagina
                        window.location.href = 'bedankt.php?order_id=' + data.order_id;
                    } else {
                        alert('Er is een fout opgetreden: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Er is een fout opgetreden bij het verwerken van uw betaling.');
                });
            }
        }
    });
    </script>
</body>
</html>