<?php include 'header.php'; ?>
<?php include 'nav.php'; ?>

<main>
    <section class="info-box blue">
        <h2>Wie zijn wij?</h2>
        <p>Wij zijn een betrouwbare online apotheek die streeft naar de beste service voor onze klanten. 
           Onze missie is om medicijnen toegankelijk en begrijpelijk te maken voor iedereen.</p>
    </section>

    <section class="info-box orange">
        <h2>Onze Missie</h2>
        <p>Wij geloven in een transparante en klantgerichte aanpak. Door samen te werken met gecertificeerde 
           leveranciers, garanderen wij kwaliteit en veiligheid.</p>
    </section>

    <section class="info-box teal">
        <h2>Waarom kiezen voor ons?</h2>
        <ul>
            <li>✔ Gecertificeerde medicijnen</li>
            <li>✔ Snelle en veilige levering</li>
            <li>✔ Deskundig advies van professionals</li>
        </ul>
    </section>

    <section class="info-box yellow">
        <h2>Contact</h2>
        <p>Heb je vragen? Neem contact met ons op via:</p>
        <p>📞 Telefoon: 0123-456789</p>
        <p>📧 E-mail: info@medicijnenwebshop.nl</p>
    </section>
</main>

<?php include 'footer.php'; ?>


<style >
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    color: black;
    line-height: 1.4;
}

nav ul {
    list-style: none;
    display: flex;
    background: lightgray;
    padding: 10px;
    margin: 0;
    justify-content: space-around;
}

nav ul li {
    padding: 5px 0;
}

nav ul li a {
    text-decoration: none;
    color: black;
    font-weight: bold;
    padding: 5px 10px;
}

nav ul li a:hover {
    background-color: #d1d1d1;
    border-radius: 3px;
}

header {
    text-align: center;
    padding: 20px;
    font-size: 2em;
    background-color: #e0e0e0;
    margin-bottom: 20px;
}

main {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.info-box {
    padding: 20px;
    border-radius: 8px;
    color: black;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.blue { background: #1f4e79; color: white; }
.orange { background: #d87a1f; color: white; }
.teal { background: #1fa7a7; color: white; }
.yellow { background: #c7b61f; }

footer {
    text-align: center;
    padding: 15px;
    background: lightgray;
    position: fixed;
    bottom: 0;
    width: 100%;
    border-top: 1px solid #999;
}

@media (max-width: 600px) {
    main {
        grid-template-columns: 1fr;
    }
    
    nav ul {
        flex-direction: column;
        align-items: center;
    }
}
 </style>