<?php
session_start();
$estConnecte = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title"></title>
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="info-bateau.js"></script>
</head>
<body>
<div class="background-fixe"></div>

<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu.png" alt="Menu">
</div>

<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content" id="menu-links"></div>
</div>

<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a id="a-propos-link" href="a-propos.php" class="top-infos"></a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos"></a>
    <a href="favoris.php">
        <img src="images/panier.png" alt="Panier">
    </a>
</div>

<div class="top-center">
    <img src="images/logo.png" alt="Logo">
</div>

<section class="section-accueil">
    <div class="texte-wrapper">
        <div class="texte principal" id="texte-principal">Cap'Tivant</div>
        <div class="texte secondaire" id="texte-secondaire">Location de bateau</div>
    </div>
</section>

<section class="section-bateau">
    <div class="bloc-bateau-carre">
        <div class="carousel">
            <div class="section-bateau">
                <div class="contenu-bateau">
                    <a href="ports.php" class="carte" id="carte-link">
                        <div id="carte"></div>
                    </a>
                    <div class="carre-carousel-slide">
                        <div class="carre-track-slide" id="carouselTrack">
                            <a href="info-bateau.php?id=3"><img src="images/bateau-voile1.png" alt="Bateau 3"></a>
                            <a href="info-bateau.php?id=1"><img src="images/bateau1.png" alt="Bateau 1"></a>
                            <a href="info-bateau.php?id=2"><img src="images/catamaran1.png" alt="Bateau 2"></a>
                            <a href="info-bateau.php?id=4"><img src="images/moteur1.png" alt="Bateau 4"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="bottom-center">
    <a id="lien-mentions" href="MentionsLegales.php" class="bottom-text"></a>
    <span class="bottom-text">•</span>
    <a id="lien-contact" href="Contact.php" class="bottom-text"></a>
</div>

<script>
    function toggleMenu() {
        document.getElementById("menu-overlay").classList.toggle("active");
    }

    function toggleLangDropdown() {
        const dropdown = document.getElementById("lang-dropdown");
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    }

    function changerLangue(langue) {
        localStorage.setItem("langue", langue);
        location.reload();
    }

    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("lang-dropdown");
        const icon = document.getElementById("current-lang");
        if (!dropdown.contains(event.target) && !icon.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const langue = localStorage.getItem("langue") || "fr";
        const commun = langue === "en" ? CommunEN : CommunFR;
        const texte = langue === "en" ? AccueilEN : AccueilFR;

        document.getElementById("page-title").textContent = texte.titre;
        document.getElementById("texte-principal").textContent = texte.textePrincipal;
        document.getElementById("texte-secondaire").textContent = texte.texteSecondaire;

        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("a-propos-link").textContent = commun.info;
        document.getElementById("compte-link").textContent = commun.compte;

        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        document.getElementById("lang-dropdown").innerHTML = langue === "en"
            ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;

        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        const menuContent = document.getElementById("menu-links");
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a href="${liens[index]}.php">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const track = document.querySelector(".carre-track-slide");
        const images = document.querySelectorAll(".carre-track-slide img");
        const imageWidth = 400;
        let index = 0;

        setInterval(() => {
            index = (index + 1) % images.length;
            track.style.transform = `translateX(-${index * imageWidth}px)`;
        }, 2000);

        const map = L.map('carte').setView([47.2186, -1.5536], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
    });
</script>
</body>
</html>
