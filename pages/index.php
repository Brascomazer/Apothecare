<?php
session_start();
require '../includes/db_connect.php';
require '../includes/session_helper.php';

// Haal featured producten op uit de database
$sql = "SELECT * FROM medicijn ORDER BY medicijn_id DESC LIMIT 3";
$result = $conn->query($sql);
$featured_products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $featured_products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apothecare - Uw online apotheek</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-banner">
        <div class="container">
            <div class="hero-content">
                <h1>Welkom bij Apothecare</h1>
                <p>Uw online apotheek voor betrouwbare medicijnen en gezondheidsproducten</p>
                <div class="hero-cta">
                    <a href="medicijnen.php" class="btn btn-primary">Bekijk ons assortiment</a>
                    <?php if (!is_logged_in()): ?>
                        <a href="register.php" class="btn btn-secondary">Word nu klant</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Over Ons Introductie -->
    <section class="about-section">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h2>Over Apothecare</h2>
                    <p>Al meer dan 7 jaar staat Apothecare voor u klaar met deskundig advies en kwalitatief hoogwaardige medicijnen. Met meer dan 10.000 tevreden klanten en een waardering van 4,7 sterren, kunt u vertrouwen op onze service.</p>
                    <p>Onze gecertificeerde apothekers zorgen voor veilige medicatie en persoonlijk advies, speciaal afgestemd op uw behoeften.</p>
                    <a href="over_ons.php" class="btn btn-outline">Meer over ons</a>
                </div>
                <div class="about-features">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3>Snelle bezorging</h3>
                        <p>Gratis bezorging bij bestellingen boven €20</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <h3>Professioneel advies</h3>
                        <p>Deskundig advies van gediplomeerde apothekers</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Veilige medicatie</h3>
                        <p>Alle producten voldoen aan de hoogste kwaliteitseisen</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Uitgelichte Producten -->
    <section class="featured-products">
        <div class="container">
            <h2>Uitgelichte Producten</h2>
            <div class="products-grid">
                <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="../assets/images/medicine-placeholder.png" alt="<?php echo htmlspecialchars($product['naam']); ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['naam']); ?></h3>
                        <p><?php echo substr(htmlspecialchars($product['beschrijving']), 0, 80); ?>...</p>
                        <div class="product-price">€<?php echo number_format($product['prijs'], 2, ',', '.'); ?></div>
                        <div class="product-actions">
                            <a href="medicijn_details.php?id=<?php echo $product['medicijn_id']; ?>" class="btn btn-outline">Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all">
                <a href="medicijnen.php" class="btn btn-primary">Bekijk alle producten</a>
            </div>
        </div>
    </section>

    <!-- Getuigenissen -->
    <section class="testimonials">
        <div class="container">
            <h2>Wat onze klanten zeggen</h2>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <div class="testimonial-quote">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p>De bezorging was snel en het medicijn werkte uitstekend. Zeer tevreden over de service van Apothecare!</p>
                    <div class="testimonial-author">
                        <div class="author-name">Petra de Jong</div>
                        <div class="author-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-quote">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p>Fijn dat ik via de website altijd mijn vaste medicatie kan bestellen. Het persoonlijke advies is ook zeer waardevol.</p>
                    <div class="testimonial-author">
                        <div class="author-name">Johan Visser</div>
                        <div class="author-stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/script.js"></script>
</body>
</html>