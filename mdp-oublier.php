<?php
session_start();
$estConnecte = isset($_SESSION['user_id']);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Réinitialiser votre mot de passe</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="mdp-oublier.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <script src="info-bateau.js"></script>
</head>
<body>
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu-vert.png" alt="Menu">
</div>

<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content" id="menu-links"></div>
</div>

<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a id="a-propos-link" href="a-propos.php" style="color: #577550; text-decoration: none;">Aà propos</a>
    <a href="favoris.php">
        <img src="images/panier.png" alt="Panier">
    </a>
</div>

<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo-transparent.png" alt="Logo" style="width: 30px;">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-page"></h1>
    </div>

    <div class="formulaire-connexion">
        <div class="champ">
            <img src="images/email.png" alt="email">
            <input type="email" id="email" placeholder="E-mail lié au compte">
        </div>
    </div>
    <div class="logo-block">
        <div class="connexion" id="btn-recevoir-lien">
            <a href="#" style="color: white; text-decoration: none;"></a>
        </div>
        <div class="retour">
            <a id="retour-connexion" href="Connexion.php" style="color: #ee9c72; text-decoration: none;"></a>
        </div>
    </div>
</div>

<div class="bouton-bas">
    <a id="lien-mentions" href="MentionsLegales.php" class="bottom-text" style="color: #577550">Mentions légales</a>
    <span class="bottom-text" style="color: #577550">&bull;</span>
    <a id="lien-contact" href="Contact.php" class="bottom-text" style="color: #577550">Contact</a>
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

    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("lang-dropdown");
        const icon = document.getElementById("current-lang");
        if (!dropdown.contains(event.target) && !icon.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const langue = localStorage.getItem("langue") || "fr";
        const texte = langue === "en" ? MdpEN : MdpFR;
        const commun = langue === "en" ? CommunEN : CommunFR;

        document.getElementById("page-title").textContent = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        document.getElementById("email").placeholder = texte.placeholder;
        document.getElementById("btn-recevoir-lien").innerHTML = `<a href=\"#\" style=\"color: white; text-decoration: none;\">${texte.bouton}</a>`;
        document.getElementById("retour-connexion").textContent = texte.retour;

        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-mentions").href = "MentionsLegales.php";
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("lien-contact").href = "Contact.php";
        document.getElementById("a-propos-link").textContent = commun.info;

        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        document.getElementById("lang-dropdown").innerHTML = langue === "en"
            ? `<img src=\"images/drapeau-francais.png\" alt=\"Français\" class=\"drapeau-option\" onclick=\"changerLangue('fr')\">`
            : `<img src=\"images/drapeau-anglais.png\" alt=\"Anglais\" class=\"drapeau-option\" onclick=\"changerLangue('en')\">`;

        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        const menuContent = document.getElementById("menu-links");
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a href=\"${liens[index]}.php\">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';
    });
</script>
</body>
</html>
