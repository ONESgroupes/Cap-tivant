<?php
session_start();
require_once 'config.php';

$estConnecte = isset($_SESSION['user_id']);

// Langue choisie dans localStorage (passée en cookie ou défaut fr)
$langue = $_COOKIE['langue'] ?? 'fr';

$contenu = [
    'titre' => 'À propos',
    'texte' => 'Contenu non disponible.'
];

try {
    $stmt = $pdo->prepare("SELECT titre_$langue AS titre, texte_$langue AS texte FROM a_propos LIMIT 1");
    $stmt->execute();
    $contenu = $stmt->fetch(PDO::FETCH_ASSOC) ?: $contenu;
} catch (PDOException $e) {
    // En cas d'erreur, le contenu par défaut sera affiché
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($contenu['titre']) ?></title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="a-propos.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Questrial&display=swap" rel="stylesheet">
    <script src="info-bateau.js"></script>
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
            document.cookie = "langue=" + langue + "; path=/"; // <-- Cette ligne est essentielle
            localStorage.setItem("langue", langue);
            location.reload();
        }

        document.addEventListener("DOMContentLoaded", function () {
            const langue = getLangue();
            const commun = langue === "en" ? CommunEN : CommunFR;

            const currentLang = document.getElementById("current-lang");
            currentLang.src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";

            const langDropdown = document.getElementById("lang-dropdown");
            langDropdown.innerHTML = langue === "en"
                ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
                : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;

            document.getElementById("lien-apropos").textContent = commun.info;
            document.getElementById("lien-mentions").textContent = commun.mentions;
            document.getElementById("lien-contact").textContent = commun.contact;

            const menuContent = document.getElementById("menu-links");
            const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
            menuContent.innerHTML = commun.menu.map((item, index) => {
                return `<a class="lien-langue" data-page="${liens[index]}">${item}</a>`;
            }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';

            document.querySelectorAll(".lien-langue").forEach(lien => {
                const page = lien.getAttribute("data-page");
                lien.setAttribute("href", `${page}.php`);
            });
        });

        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("lang-dropdown");
            const icon = document.getElementById("current-lang");
            if (!dropdown.contains(event.target) && !icon.contains(event.target)) {
                dropdown.style.display = "none";
            }
        });
    </script>
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
            background-color: #c5d8d3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body style="background-color: #c5d8d3;">
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu-vert.png" alt="Menu">
</div>





<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content" id="menu-links"></div>
</div>


<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo-transparent.png" alt="Logo" style="width: 30px;">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title"><?= htmlspecialchars($contenu['titre']) ?></h1>
    </div>
</div>

<div class="banniere">
    <div class="texte banniere1">Équipage Cap'Tivant</div>
</div>

<div class="top-bar-background"></div>

<div class="description" style="max-width: 800px; margin: 0 auto; padding: 20px; font-family: 'Questrial', sans-serif; font-size: 1.1em;">
    <?= nl2br($contenu['texte']) ?>
</div>

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

        <a id="lien-apropos" class="lien-langue" data-page="a-propos" style="color: #577550; text-decoration: none; white-space: nowrap;">À propos</a>

        <?php if (!$estConnecte): ?>
            <a id="lien-compte" href="Connexion.php" style="color: #577550; text-decoration: none; white-space: nowrap;">Mon Compte</a>
        <?php endif; ?>

        <a href="favoris.php">
            <img src="images/panier.png" alt="Panier" style="min-width: 20px;">
        </a>
    </div>
</div>

<div class="bouton-bas">
    <a id="lien-mentions" href="MentionsLegales.php" class="bottom-text lien-langue" data-page="MentionsLegales" style="color: #577550">Mentions légales</a>
    <span class="bottom-text" style="color: #577550">•</span>
    <a id="lien-contact" href="Contact.php" class="bottom-text lien-langue" data-page="Contact" style="color: #577550">Contact</a>
</div>
</body>
</html>