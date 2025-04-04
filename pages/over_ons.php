<?php
session_start();
require_once '../includes/session_helper.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Over Ons - Apothecare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="about-us-banner">
        <div class="container">
            <h1>Over Apothecare</h1>
            <p>Leer meer over onze missie, ons team en onze geschiedenis</p>
        </div>
    </div>
    
    <div class="container">
        <div class="about-us-grid">
            <div class="info-box">
                <h2>Betrouwbaarheid</h2>
                <p>Wij bestaan al 7 jaar en hebben 10 duizend actieve klanten met een 4,7 sterren beoordeling. Uw gezondheid ligt ons na aan het hart en wij streven ernaar om een betrouwbare partner te zijn in uw gezondheidsreis.</p>
            </div>
            
            <div class="info-box">
                <h2>Wie Zijn Wij</h2>
                <p>Wij zorgen dat jij de juiste medicatie krijgt. Met ons team van ervaren apothekers en farmaceutische experts helpen wij je graag om goed en gezond te blijven met toegewijde zorg en professioneel advies.</p>
            </div>
            
            <div class="info-box">
                <h2>Ons Team</h2>
                <p>Ons toegewijde team bestaat uit Jasper, Jay, Brasco en Mohammed. Samen brengen we een schat aan ervaring en vakkennis met zich mee, allemaal gericht op het leveren van de best mogelijke zorg aan onze klanten.</p>
            </div>
            
            <div class="info-box">
                <h2>Opleidingen</h2>
                <p>Ons team heeft diverse opleidingen en kwalificaties, waaronder Apothekersassistent, Farmaceutisch consulent, en Apotheker. Wij investeren continu in bijscholing om u de beste en meest actuele zorg te kunnen bieden.</p>
            </div>
        </div>
        
        <div class="team-section">
            <h2>Maak kennis met ons team</h2>
            
            <div class="team-grid">
                <div class="team-member">
                    <h3>Jasper</h3>
                    <p>Hoofdapotheker</p>
                </div>
                
                <div class="team-member">
                    <h3>Jay</h3>
                    <p>Farmaceutisch consulent</p>
                </div>
                
                <div class="team-member">
                    <h3>Brasco</h3>
                    <p>Apothekersassistent</p>
                </div>
                
                <div class="team-member">
                    <h3>Mohammed</h3>
                    <p>Klantenservice specialist</p>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
</body>
</html>