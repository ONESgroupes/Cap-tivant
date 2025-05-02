<?php
session_start();
$estConnecte = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="page-title"></title>
    <link rel="stylesheet" href="ports.css">
    <link rel="stylesheet" href="PageAccueil.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>
<body style="background-color: #c5d8d3;">
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu-vert.png" alt="Menu">
</div>

<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content">
        <a href="location.php" id="menu-location"></a>
        <a href="ports.php" id="menu-ports"></a>
        <a href="MonCompte.php" id="menu-compte"></a>
        <a href="historique.php" id="menu-historique"></a>
        <a href="faq.php" id="menu-faq"></a>
        <a href="avis.php" id="menu-avis"></a>
        <span onclick="toggleMenu()" class="close-menu">✕</span>
    </div>
</div>

<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo-transparent.png" alt="Logo" style="width: 30px;">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-page">Nos Ports</h1>
    </div>
</div>

<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a href="a-propos.php" id="lien-apropos" style="color: #577550; text-decoration: none;"></a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos">Mon Compte</a>
    <a href="favoris.php">
        <img src="images/panier.png" alt="Panier">
    </a>
</div>

<main>
    <div class="carte-container">
        <div id="map"></div>
    </div>
</main>

<div class="bouton-bas">
    <a href="MentionsLegales.php" id="lien-mentions" class="bottom-text" style="color: #577550"></a>
    <span class="bottom-text" style="color: #577550">•</span>
    <a href="Contact.php" id="lien-contact" class="bottom-text" style="color: #577550"></a>
</div>

<script>
    function toggleMenu() {
        const menu = document.getElementById("menu-overlay");
        menu.classList.toggle("active");
    }

    function toggleLangDropdown() {
        const dropdown = document.getElementById("lang-dropdown");
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    }

    function changerLangue(langue) {
        localStorage.setItem("langue", langue);
        location.reload();
    }

    window.onload = function () {
        const langue = localStorage.getItem("langue") || "fr";
        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        const texte = langue === "en" ? PortsEN : PortsFR;
        const texteCommun = langue === "en" ? CommunEN : CommunFR;

        document.querySelector('a[href="a-propos.php"]').textContent = texteCommun.info;
        document.querySelector('a[href="Connexion.php"]').textContent = texteCommun.compte;
        document.querySelector('a[href="MentionsLegales.php"]').textContent = texteCommun.mentions;
        document.querySelector('a[href="Contact.php"]').textContent = texteCommun.contact;
        document.getElementById("titre-page").textContent = texte.titre;

        const menuItems = document.querySelectorAll('#menu-overlay .menu-content a');
        menuItems.forEach((link, index) => {
            if (texteCommun.menu[index]) {
                link.textContent = texteCommun.menu[index];
            }
        });

        const dropdown = document.getElementById("lang-dropdown");
        dropdown.innerHTML = langue === "en"
            ? `<img src=\"images/drapeau-francais.png\" alt=\"Français\" class=\"drapeau-option\" onclick=\"changerLangue('fr')\">`
            : `<img src=\"images/drapeau-anglais.png\" alt=\"Anglais\" class=\"drapeau-option\" onclick=\"changerLangue('en')\">`;
    };

    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("lang-dropdown");
        const icon = document.getElementById("current-lang");
        if (!dropdown.contains(event.target) && !icon.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
</script>
<script src="info-bateau.js"></script>
<script src="ports.js"></script>
</body>
</html>
