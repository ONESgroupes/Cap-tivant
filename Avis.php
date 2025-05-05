<?php
session_start();
require_once 'config.php';

// 🔐 Vérification de session utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php");
    exit;
}

$estConnecte = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Avis</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="Avis.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <script src="info-bateau.js" defer></script>
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
    <a id="lien-apropos" class="lien-langue" data-page="a-propos" style="color: #577550; text-decoration: none;">À propos</a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos" style="color: #577550;">Mon Compte</a>
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
        <h1 class="page-title">Avis</h1>
    </div>
</div>

<div class="background" id="avis-container">
    <!-- Avis affichés dynamiquement ici -->
</div>

<div class="bouton-bas">
    <a id="lien-mentions" class="bottom-text lien-langue" data-page="MentionsLegales" style="color: #577550;">Mentions légales</a>
    <span class="bottom-text" style="color: #577550;">•</span>
    <a id="lien-contact" class="bottom-text lien-langue" data-page="Contact" style="color: #577550;">Contact</a>
</div>

<script>
    function getLangue() {
        return localStorage.getItem("langue") || "fr";
    }

    function toggleLangDropdown() {
        const dropdown = document.getElementById("lang-dropdown");
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    }

    function changerLangue(langue) {
        localStorage.setItem("langue", langue);
        location.reload();
    }

    function toggleMenu() {
        document.getElementById("menu-overlay").classList.toggle("active");
    }

    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("lang-dropdown");
        const icon = document.getElementById("current-lang");
        if (!dropdown.contains(event.target) && !icon.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const langue = getLangue();
        const texte = langue === "en" ? AvisEN : AvisFR;
        const commun = langue === "en" ? CommunEN : CommunFR;

        // Titre et texte de la page
        document.title = texte.titre;
        document.querySelector(".page-title").textContent = texte.titre;

        // Drapeau
        const currentLang = document.getElementById("current-lang");
        currentLang.src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";

        const langDropdown = document.getElementById("lang-dropdown");
        langDropdown.innerHTML = "";
        const drapeau = document.createElement("img");
        drapeau.src = langue === "en" ? "images/drapeau-francais.png" : "images/drapeau-anglais.png";
        drapeau.alt = langue === "en" ? "Français" : "Anglais";
        drapeau.className = "drapeau-option";
        drapeau.style.cursor = "pointer";
        drapeau.addEventListener("click", function () {
            changerLangue(langue === "en" ? "fr" : "en");
        });
        langDropdown.appendChild(drapeau);

        // Menu dynamique
        const menuContent = document.getElementById("menu-links");
        const compteLien = "<?= $estConnecte ? 'MonCompte' : 'Connexion' ?>";
        const liens = ["location", "ports", compteLien, "historique", "faq", "avis"];
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a class="lien-langue" data-page="${liens[index]}">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';

        document.querySelectorAll(".lien-langue").forEach(lien => {
            const page = lien.getAttribute("data-page");
            lien.setAttribute("href", `${page}.php`);
        });

        // Liens fixes
        document.getElementById("lien-apropos").textContent = commun.info;
        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("compte-link").textContent = commun.compte;

        // Avis (depuis localStorage)
        const avis = JSON.parse(localStorage.getItem("avis") || "[]");
        const container = document.getElementById("avis-container");

        if (avis.length === 0) {
            container.innerHTML = `<p style='text-align:center;'>${texte.aucunAvis}</p>`;
        } else {
            container.innerHTML = avis.map(entry => `
                <div class="avis-card">
                    <h3>${entry.titre}</h3>
                    <p class="etoiles">${'⭐'.repeat(entry.etoiles)}</p>
                    <p class="commentaire">"${entry.commentaire}"</p>
                    <p class="date">${entry.date}</p>
                </div>
            `).join('');
        }
    });
</script>
</body>
</html>
