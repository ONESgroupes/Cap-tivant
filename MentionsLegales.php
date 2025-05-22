<?php
session_start();
require_once 'config.php';

$estConnecte = isset($_SESSION['user_id']);
$langue = $_COOKIE['langue'] ?? 'fr';

$contenuMentions = [
    'titre' => 'Mentions légales',
    'texte' => 'Contenu non disponible.'
];

try {
    $stmt = $pdo->prepare("SELECT titre_$langue AS titre, texte_$langue AS texte FROM cgu LIMIT 1");
    $stmt->execute();
    $contenuMentions = $stmt->fetch(PDO::FETCH_ASSOC) ?: $contenuMentions;
} catch (PDOException $e) {
    $contenu = ['titre' => 'Mentions légales', 'texte' => 'Erreur de chargement du contenu.'];
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($contenuMentions['titre']) ?></title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="MentionsLegales.css">
    <link rel="stylesheet" href="nav-barre.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <script src="info-bateau.js"></script>
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
    <div style="display: flex; align-items: center; gap: 10px;">
        <div class="language-selector">
            <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon" style="cursor: pointer;">
            <div id="lang-dropdown" class="lang-dropdown" style="position: absolute;"></div>
        </div>

        <a id="a-propos-link" href="a-propos.php" style="color: #e0e0d5; text-decoration: none; white-space: nowrap;">A propos</a>
        <?php if ($estConnecte): ?>
            <span style="color: #e0e0d5; font-weight: bold; margin-right: 15px;">
        <?= htmlspecialchars($_SESSION['first_name']) ?>
    </span>
        <?php else: ?>
            <a id="lien-compte" href="Connexion.php" style="color: #e0e0d5; text-decoration: none;">Mon Compte</a>
        <?php endif; ?>

        <a href="favoris.php">
            <img src="images/panier.png" alt="Panier" style="min-width: 20px;">
        </a>
    </div>
</div>

<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo.png" alt="Logo">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title"><?= htmlspecialchars($contenuMentions['titre']) ?></h1>
    </div>

    <div id="texte-legale" class="texte-legale" style="max-width: 800px; margin-top: 100px; padding: 20px; font-family: 'DM Serif Display', serif; font-size: 0.95em; line-height: 1.6em; color: #335533; text-align: justify;">
        <?= $contenuMentions['texte'] ?>
    </div>
</div>

<div class="bouton-bas">
    <span class="bottom-text" style="color: #577550">&bull;</span>
    <a id="lien-contact" href="Contact.php" class="bottom-text" style="color: #577550">Contact</a>
    <span class="bottom-text" style="color: #577550">&bull;</span>
</div>
<script>
    function toggleMenu() {
        const menu = document.getElementById("menu-overlay");
        menu.classList.toggle("active");
    }

    function toggleLangDropdown() {
        const dropdown = document.getElementById("lang-dropdown");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    function changerLangue(langue) {
        localStorage.setItem("langue", langue);
        // Ajoute aussi un cookie côté PHP si besoin :
        document.cookie = "langue=" + langue + ";path=/";
        location.reload();

    }

    document.addEventListener("DOMContentLoaded", function () {
        const langue = localStorage.getItem("langue") || "fr";
        const drapeau = document.getElementById("current-lang");
        const dropdown = document.getElementById("lang-dropdown");
        const commun = langue === "en" ? CommunEN : CommunFR;

        const compteLink = document.getElementById("compte-link");
        if (compteLink) {
            compteLink.textContent = commun.compte;
        }
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("a-propos-link").textContent = commun.info;
        // Met le bon drapeau
        drapeau.src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";

        // Crée l'autre langue en choix
        dropdown.innerHTML = langue === "en"
            ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" style="cursor:pointer;" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" style="cursor:pointer;" onclick="changerLangue('en')">`;
        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        const menuContent = document.getElementById("menu-links");
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a href="${liens[index]}.php">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';
    });

    // Fermer le dropdown si clic en dehors
    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("lang-dropdown");
        const icon = document.getElementById("current-lang");
        if (!dropdown.contains(event.target) && !icon.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
</script>

</body>
</html>
