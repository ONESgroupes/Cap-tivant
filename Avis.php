<?php
session_start();
require_once 'config.php';


$estConnecte = true;
$user_id = $_SESSION['user_id'];

// 🔍 Récupération des avis de l'utilisateur avec les titres de bateau
$stmt = $pdo->prepare("SELECT r.*, b.titre FROM reviews r JOIN bateaux b ON r.boat_id = b.id WHERE r.user_id = ?");
$stmt->execute([$user_id]);
$avis = $stmt->fetchAll();
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
    <div style="display: flex; align-items: center; gap: 15px;">
        <?php if ($estConnecte): ?>
            <!-- Affiche le prénom avec lien vers le compte -->
            <a href="MonCompte.php" style="color: #577550; font-weight: bold; white-space: nowrap; font-family: 'DM Serif Display', cursive; text-decoration: none;">
                <?= htmlspecialchars($_SESSION['first_name']) ?>
            </a>
        <?php else: ?>
            <!-- Affiche lien Mon Compte seulement si non connecté -->
            <a href="Connexion.php" class="top-infos" style="color: #577550; white-space: nowrap;">Mon Compte</a>
        <?php endif; ?>

        <!-- Sélecteur de langue -->
        <div class="language-selector">
            <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
            <div id="lang-dropdown" class="lang-dropdown"></div>
        </div>

        <!-- Lien À propos -->
        <a id="a-propos-link" href="a-propos.php" class="top-infos" style="color: #577550; white-space: nowrap;">À propos</a>

        <!-- Panier -->
        <a href="favoris.php">
            <img src="images/panier.png" alt="Panier">
        </a>
    </div>
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

<!-- 💬 Section des avis depuis la base -->
<div class="background" id="avis-container">
    <?php if (empty($avis)): ?>
        <p style='text-align:center;'>Aucun avis enregistré.</p>
    <?php else: ?>
        <?php foreach ($avis as $a): ?>
            <div class="avis-card">
                <h3><?= htmlspecialchars($a['titre']) ?></h3>
                <p class="etoiles"><?= str_repeat('⭐', (int)$a['rating']) ?></p>
                <p class="commentaire">"<?= htmlspecialchars($a['comment']) ?>"</p>
                <p class="date"><?= date('d/m/Y', strtotime($a['created_at'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
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
        const commun = langue === "en" ? CommunEN : CommunFR;
        const texte = langue === "en" ? AvisEN : AvisFR;

        document.title = texte.titre;
        document.querySelector(".page-title").textContent = texte.titre;
        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";

        const langDropdown = document.getElementById("lang-dropdown");
        langDropdown.innerHTML = langue === "en"
            ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;

        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        const menuContent = document.getElementById("menu-links");
        menuContent.innerHTML = commun.menu.map((item, index) => `<a class="lien-langue" data-page="${liens[index]}">${item}</a>`).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';

        document.querySelectorAll(".lien-langue").forEach(lien => {
            const page = lien.getAttribute("data-page");
            lien.setAttribute("href", `${page}.php`);
        });

        document.getElementById("lien-apropos").textContent = commun.info;
        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("compte-link").textContent = commun.compte;
    });
</script>
</body>
</html>
