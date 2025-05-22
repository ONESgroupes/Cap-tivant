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
    <style>
        /* Barre de fond en haut */
        .top-bar-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 50px; /* ajuste la hauteur comme tu veux */
            background-color: #20548e; /* couleur de fond */
            z-index: 0; /* envoie derrière les autres éléments */
        }

        /* Exemple de bouton au-dessus de la barre */
        .button-top {
            position: relative;
            z-index: 1; /* plus élevé que la barre */
            margin: 20px;
            padding: 10px 20px;
            background-color: #ffffff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body style="background-color: #c5d8d3;">

<!-- Menu gauche -->
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu-vert.png" alt="Menu">
</div>

<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content" id="menu-links"></div>
</div>

<div class="top-bar-background"></div>

<!-- Logo au centre -->
<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo-transparent.png" alt="Logo" style="width: 30px;">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-page">Nos Ports</h1>
    </div>
</div>

<!-- Haut droit -->
<!-- Haut droit -->
<div class="top-right">
    <div style="display: flex; align-items: center; gap: 15px;">
        <?php if ($estConnecte): ?>
            <a href="MonCompte.php" style="color: #577550; font-weight: bold; white-space: nowrap; font-family: 'DM Serif Display', cursive; text-decoration: none;">
                <?= htmlspecialchars($_SESSION['first_name']) ?>
            </a>
        <?php endif; ?>

        <div class="language-selector">
            <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
            <div id="lang-dropdown" class="lang-dropdown"></div>
        </div>

        <a id="lien-apropos" href="a-propos.php" style="color: #577550; text-decoration: none; white-space: nowrap;">À propos</a>

        <?php if (!$estConnecte): ?>
            <a id="lien-compte" href="Connexion.php" style="color: #577550; text-decoration: none; white-space: nowrap;">Mon Compte</a>
        <?php endif; ?>

        <a href="favoris.php">
            <img src="images/panier.png" alt="Panier" style="min-width: 20px;">
        </a>
    </div>
</div>
<!-- Contenu principal -->
<main>
    <div class="carte-container">
        <div id="map"></div>
    </div>
</main>

<!-- Bas de page -->
<div class="bouton-bas">
    <a href="MentionsLegales.php" id="lien-mentions" class="bottom-text" style="color: #577550"></a>
    <span class="bottom-text" style="color: #577550">•</span>
    <a href="Contact.php" id="lien-contact" class="bottom-text" style="color: #577550"></a>
</div>

<script>
    const lienCompte = "<?= $estConnecte ? 'MonCompte' : 'Connexion' ?>";

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
        const texte = langue === "en" ? PortsEN : PortsFR;
        const texteCommun = langue === "en" ? CommunEN : CommunFR;



        const menuContent = document.getElementById("menu-links");
        const liens = ["location", "ports", lienCompte, "historique", "faq", "avis"];
        menuContent.innerHTML = texteCommun.menu.map((item, index) => {
            return `<a class="lien-langue" href="${liens[index]}.php">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';

        const dropdown = document.getElementById("lang-dropdown");
        dropdown.innerHTML = langue === "en"
            ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;
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