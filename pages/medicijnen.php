<?php
session_start();
require '../includes/db_connect.php';
require '../includes/session_helper.php';

// Sessie controleren
start_secure_session();

// Update database naam in de query
$conn->select_db("apothecare_db");

// Haal medicijnen op uit de database
$sql = "SELECT * FROM medicijn";
$result = mysqli_query($conn, $sql);

// Converteer de query resultaten naar een JSON array voor Vue.js
$medicijnen = [];
if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $medicijnen[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicijnen - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Voeg Vue.js toe -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div id="app" class="container">
        <div class="hero">
            <h1>Onze Medicijnen</h1>
            <p>Bekijk ons assortiment aan medicijnen</p>
            
            <!-- Vue.js zoek- en filteropties -->
            <div class="search-filter">
                <input v-model="zoekterm" placeholder="Zoek medicijnen..." class="search-input">
                <select v-model="sortering" class="sort-select">
                    <option value="naam">Naam (A-Z)</option>
                    <option value="naam-desc">Naam (Z-A)</option>
                    <option value="prijs-laag">Prijs (laag-hoog)</option>
                    <option value="prijs-hoog">Prijs (hoog-laag)</option>
                    <option value="voorraad">Beschikbaarheid</option>
                </select>
            </div>
        </div>
        
        <div class="medicijnen-grid">
            <div v-for="medicijn in gefilterdeMedicijnen" :key="medicijn.medicijn_id" class="medicijn-kaart form-box">
                <h2>{{ medicijn.naam }}</h2>
                <p>{{ medicijn.beschrijving.substring(0, 100) }}...</p>
                <p class="medicijn-prijs">€{{ formatteerPrijs(medicijn.prijs) }}</p>
                <p>Voorraad: {{ medicijn.hoeveelheid }}</p>
                <div class="medicijn-knoppen">
                    <a :href="'medicijn_details.php?id=' + medicijn.medicijn_id" class="btn">Meer informatie</a>
                    <button class="btn" @click="toevoegenAanWinkelwagen(medicijn)">In winkelwagen</button>
                </div>
            </div>
            <p v-if="gefilterdeMedicijnen.length === 0" class="geen-resultaten">Geen medicijnen gevonden</p>
        </div>
    </div>
    
    <footer>
        <a href="index.html">Home</a>
        <a href="over_ons.html">Over Ons</a>
        <a href="service.php">Service & contact</a>
        <p>&copy; 2025 Medicijnen Webshop</p>
    </footer>

    <script>
        // Maak hier het Vue-object aan en geef de PHP-data direct door aan Vue
        new Vue({
            el: '#app',
            data: {
                // Haal medicijnen rechtstreeks uit PHP
                medicijnen: <?php echo json_encode($medicijnen); ?>,
                zoekterm: '',
                sortering: 'naam',
                winkelwagen: []
            },
            computed: {
                gefilterdeMedicijnen() {
                    let result = this.medicijnen;
                    
                    // Zoeken filteren
                    if (this.zoekterm) {
                        const zoekLowerCase = this.zoekterm.toLowerCase();
                        result = result.filter(item => 
                            item.naam.toLowerCase().includes(zoekLowerCase) || 
                            item.beschrijving.toLowerCase().includes(zoekLowerCase)
                        );
                    }
                    
                    // Sorteren
                    switch(this.sortering) {
                        case 'naam':
                            return result.sort((a, b) => a.naam.localeCompare(b.naam));
                        case 'naam-desc':
                            return result.sort((a, b) => b.naam.localeCompare(a.naam));
                        case 'prijs-laag':
                            return result.sort((a, b) => parseFloat(a.prijs) - parseFloat(b.prijs));
                        case 'prijs-hoog':
                            return result.sort((a, b) => parseFloat(b.prijs) - parseFloat(a.prijs));
                        case 'voorraad':
                            return result.sort((a, b) => b.hoeveelheid - a.hoeveelheid);
                        default:
                            return result;
                    }
                }
            },
            methods: {
                formatteerPrijs(prijs) {
                    return parseFloat(prijs).toLocaleString('nl-NL', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                toevoegenAanWinkelwagen(medicijn) {
                    // Hier kun je een AJAX-verzoek doen of sessie-opslag gebruiken
                    // Voor nu een eenvoudige melding:
                    alert('Medicijn "' + medicijn.naam + '" toegevoegd aan winkelwagen!');
                    
                    // Dit kan later uitgebreid worden met AJAX:
                    // fetch('add_to_cart.php', {
                    //    method: 'POST',
                    //    body: JSON.stringify({ medicijn_id: medicijn.medicijn_id, aantal: 1 }),
                    //    headers: { 'Content-Type': 'application/json' }
                    // })
                }
            }
        });
    </script>
</body>
</html>