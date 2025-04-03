<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-column">
                <h3>Apothecare</h3>
                <p>Uw online apotheek voor betrouwbare medicatie en gezondheidsadvies.</p>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h3>Navigatie</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="medicijnen.php">Medicijnen</a></li>
                    <li><a href="over_ons.php">Over Ons</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Account</h3>
                <ul>
                    <?php if (is_logged_in()): ?>
                        <li><a href="dashboard.php">Mijn Account</a></li>
                        <li><a href="bestellingen.php">Mijn Bestellingen</a></li>
                        <li><a href="winkelwagen.php">Winkelwagen</a></li>
                        <li><a href="../api/logout.php">Uitloggen</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Inloggen</a></li>
                        <li><a href="register.php">Registreren</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Contact</h3>
                <address>
                    <p><i class="fas fa-map-marker-alt"></i> Gezondheidsstraat 123<br>1234 AB Amsterdam</p>
                    <p><i class="fas fa-phone"></i> 020-123 4567</p>
                    <p><i class="fas fa-envelope"></i> info@apothecare.nl</p>
                </address>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Apothecare. Alle rechten voorbehouden.</p>
        </div>
    </div>
</footer>