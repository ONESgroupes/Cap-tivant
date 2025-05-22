<?php
session_start();
require_once 'config.php';

$faq_data = [];
try {
    $stmt = $pdo->query("SELECT question AS question_fr, reponse AS reponse_fr, question_en, reponse_en FROM faq");
    $faq_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $faq_data = [];
}


$estConnecte = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">FAQ</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="faq.css">
    <link rel="stylesheet" href="nav-barre.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <script src="info-bateau.js" defer></script>
    <script>
        function getLangue() {
            return localStorage.getItem("langue") || "fr";
        }

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
            const langue = getLangue();
            const texte = langue === "en" ? FAQEN : FAQFR;
            const commun = langue === "en" ? CommunEN : CommunFR;

            document.title = texte.titre;
            document.querySelector(".page-title").textContent = texte.titre;

            // Mise à jour du drapeau langue
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

            // Menu
            const menuContent = document.getElementById("menu-links");
            const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
            menuContent.innerHTML = commun.menu.map((item, index) => {
                return `<a class="lien-langue" data-page="${liens[index]}">${item}</a>`;
            }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';

            document.querySelectorAll(".lien-langue").forEach(lien => {
                const page = lien.getAttribute("data-page");
                lien.setAttribute("href", `${page}.php`);
            });

            const compteLink = document.getElementById("lien-compte");
            if (compteLink) {
                compteLink.textContent = commun.compte;
            }

            // Lien À propos
            const lienApropos = document.getElementById("lien-apropos");
            if (lienApropos) {
                lienApropos.textContent = commun.info;
                lienApropos.setAttribute("href", "a-propos.php");
            }

            // Mentions légales et contact
            document.getElementById("lien-mentions").textContent = commun.mentions;
            document.getElementById("lien-contact").textContent = commun.contact;

            // Données FAQ
            const faqData = <?= json_encode($faq_data) ?>;
            const container = document.querySelector(".faq-container");
            container.innerHTML = faqData.map(item => {
                const q = langue === "en" ? item.question_en : item.question_fr;
                const a = langue === "en" ? item.reponse_en : item.reponse_fr;
                return `
                    <div class="faq-item">
                        <button class="faq-question">${q}</button>
                        <div class="faq-answer"><p>${a}</p></div>
                    </div>`;
            }).join("");

            // Toggle affichage réponses
            document.querySelectorAll('.faq-question').forEach(q => {
                q.addEventListener('click', () => {
                    const answer = q.nextElementSibling;
                    document.querySelectorAll('.faq-answer').forEach(a => {
                        if (a !== answer) a.style.maxHeight = null;
                    });
                    answer.style.maxHeight = answer.style.maxHeight ? null : answer.scrollHeight + "px";
                });
            });
        });
    </script>
</head>
<div class="navbar-barre"></div>
<body style="background-color: #c5d8d3;">
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
    <a id="lien-apropos" class="lien-langue" data-page="a-propos" style="color: #e0e0d5; text-decoration: none">À propos</a>
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

<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo.png" alt="Logo">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title">Foire aux questions</h1>
    </div>
</div>

<div class="faq-container" style="margin-top: 200px;">
    <!-- Questions injectées dynamiquement -->
</div>

<div class="bouton-bas">
    <a id="lien-mentions" class="bottom-text lien-langue" data-page="MentionsLegales" style="color : #577550;">Mentions légales</a>
    <span class="bottom-text" style="color: #577550;">•</span>
    <a id="lien-contact" class="bottom-text lien-langue" data-page="Contact" style="color: #577550;">Contact</a>
</div>
</body>
</html>
