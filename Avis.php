<?php
session_start();
require_once 'config.php';


$estConnecte = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;


// 🔍 Récupération des avis de l'utilisateur avec les titres de bateau$stmt = $pdo->query("SELECT r.*, b.titre, u.first_name FROM reviews r JOIN bateaux b ON r.boat_id = b.id JOIN users u ON r.user_id = u.id");
$stmt = $pdo->query("SELECT r.*, b.titre, u.first_name FROM reviews r JOIN bateaux b ON r.boat_id = b.id JOIN users u ON r.user_id = u.id");
$avis = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Avis</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="Avis.css">
    <link rel="stylesheet" href="nav-barre.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <script src="info-bateau.js" defer></script>
</head>
<body>
<div class="navbar-barre"></div>
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
            <img src="images/logo.png" alt="Logo" >
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title">Avis</h1>
    </div>
    <div class="background" id="avis-container">
        <?php if (empty($avis)): ?>
            <p style='text-align:center;'>Aucun avis enregistré.</p>
        <?php else: ?>
            <?php foreach ($avis as $a): ?>
                <a class="avis-card" href="info-bateau.php?id=<?= $a['boat_id'] ?>">
                    <h3><?= htmlspecialchars($a['titre']) ?></h3>
                    <p class="etoiles"><?= str_repeat('⭐', (int)$a['rating']) ?></p>
                    <p class="commentaire">"<?= htmlspecialchars($a['comment']) ?>"</p>
                    <p class="date"><?= date('d/m/Y', strtotime($a['created_at'])) ?></p>
                </a>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- 💬 Section des avis depuis la base -->


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
        const compteLink = document.getElementById("lien-compte");
        if (compteLink) {
            compteLink.textContent = commun.compte;
        }

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
    });
</script>
</body>
</html>
