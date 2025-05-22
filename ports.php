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
    <link rel="stylesheet" href="nav-barre.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>
<div class="navbar-barre"></div>
<body style="background-color: #c5d8d3;">

<!-- Menu gauche -->
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu.png" alt="Menu">
</div>

<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content" id="menu-links"></div>
</div>

<!-- Logo au centre -->
<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo.png" alt="Logo" >
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-page">Nos Ports</h1>
    </div>
</div>

<!-- Haut droit -->
<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a href="a-propos.php" id="lien-apropos" style="color: #e0e0d5; text-decoration: none;"></a>
    <?php if ($estConnecte): ?>
        <span style="color: #e0e0d5; font-weight: bold; margin-right: 15px;">
        <?= htmlspecialchars($_SESSION['first_name']) ?>
    </span>
    <?php else: ?>
        <a id="lien-compte" href="Connexion.php" style="color: #e0e0d5; text-decoration: none;">Mon Compte</a>
    <?php endif; ?>
    <a href="favoris.php">
        <img src="images/panier.png" alt="Panier">
    </a>
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

        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        document.getElementById("titre-page").textContent = texte.titre;

        document.getElementById("lien-apropos").textContent = texteCommun.info;
        document.getElementById("lien-mentions").textContent = texteCommun.mentions;
        document.getElementById("lien-contact").textContent = texteCommun.contact;
        const lienCompte = document.getElementById("lien-compte");
        if (lienCompte) {
            lienCompte.textContent = texteCommun.compte;
        }


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