<?php
session_start();
require_once 'config.php';

$estConnecte = isset($_SESSION['user_id']);
$historique = [];

if ($estConnecte) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT b.* FROM historique h JOIN bateaux b ON h.bateau_id = b.id WHERE h.user_id = ? ORDER BY h.date_reservation DESC");
    $stmt->execute([$user_id]);
    $historique = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Historique</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="historique.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <script src="info-bateau.js" defer></script>
</head>
<body style="background-color: #c5d8d3;">
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu-vert.png" alt="Menu">
</div>
<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content" id="menu-links"></div> <!-- Ajout de id="menu-links" -->
</div>

<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a id="lien-apropos" class="lien-langue" data-page="a-propos" style="color: #577550; text-decoration: none;">A propos</a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos" style="color: #577550">Mon Compte</a>
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
        <h1 class="page-title" id="titre-page">Historique</h1>
    </div>
</div>
<div class="historique-container">
    <?php if (count($historique) === 0): ?>
        <p style="text-align:center;">Aucune réservation.</p>
    <?php else: ?>
        <?php foreach ($historique as $bateau): ?>
            <div class="historique-card">
                <h2><?= htmlspecialchars($bateau['titre']) ?></h2>
                <p><strong>Port :</strong> <?= htmlspecialchars($bateau['port']) ?></p>
                <p><strong>Personnes :</strong> <?= htmlspecialchars($bateau['personnes']) ?></p>
                <p><strong>Cabines :</strong> <?= htmlspecialchars($bateau['cabines']) ?></p>
                <p><strong>Longueur :</strong> <?= htmlspecialchars($bateau['longueur']) ?></p>
                <p><strong>Prix :</strong> <?= htmlspecialchars($bateau['prix']) ?></p>
                <img src="images/<?= htmlspecialchars($bateau['image1']) ?>" alt="<?= htmlspecialchars($bateau['titre']) ?>">

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="bouton-bas">
    <a id="lien-mentions" class="bottom-text lien-langue" data-page="MentionsLegales" style="color: #577550">Mentions légales</a>
    <span class="bottom-text" style="color: #577550">&bull;</span>
    <a id="lien-contact" class="bottom-text lien-langue" data-page="Contact" style="color: #577550">Contact</a>
</div>
<script>
    function getLangue() {
        return localStorage.getItem("langue") || "fr";
    }

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
        const langue = getLangue();
        const texte = langue === "en" ? HistoriqueEN : HistoriqueFR;
        const commun = langue === "en" ? CommunEN : CommunFR;
        const bateauxSource = langue === "en" ? bateauxEN : bateaux;

        // Mise à jour de l'affichage
        document.title = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        const btnClear = document.getElementById("btn-clear");
        if (btnClear) btnClear.textContent = texte.vider;

        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";

        // Nouveau code pour drapeau
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

        // Mise à jour du menu
        const menuContent = document.getElementById("menu-links");
        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a class="lien-langue" data-page="${liens[index]}">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">&times;</span>';

        document.getElementById("lien-apropos").textContent = commun.info;
        const lienCompte = document.getElementById("lien-compte") || document.getElementById("compte-link");
        if (lienCompte) lienCompte.textContent = commun.compte;
        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-contact").textContent = commun.contact;

        document.querySelectorAll(".lien-langue").forEach(lien => {
            const page = lien.getAttribute("data-page");
            lien.setAttribute("href", `${page}.php`);
        });
    });

    function laisserAvis(titreBateau) {
        const avisExistants = JSON.parse(localStorage.getItem("avis") || "[]");

        // Vérifie si un avis existe déjà pour ce bateau
        const dejaCommente = avisExistants.some(a => a.titre === titreBateau);
        if (dejaCommente) {
            alert("Vous avez déjà laissé un avis pour ce bateau.");
            return;
        }

        const popup = document.createElement("div");
        popup.className = "avis-popup-overlay";
        popup.innerHTML = `
        <div class="avis-popup-bulle">
            <h2>Merci pour votre réservation !</h2>
            <p>Laissez-nous un avis sur votre expérience :</p>
            <textarea id="commentaire" placeholder="Votre commentaire..."></textarea><br><br>
            <label>Note :</label>
            <select id="etoiles">
                <option value="1">⭐</option>
                <option value="2">⭐⭐</option>
                <option value="3">⭐⭐⭐</option>
                <option value="4">⭐⭐⭐⭐</option>
                <option value="5">⭐⭐⭐⭐⭐</option>
            </select><br><br>
            <button onclick="validerAvis('${titreBateau}')">Valider l'avis</button>
            <button onclick="fermerAvisPopup()">Fermer</button>
        </div>
    `;
        document.body.appendChild(popup);
    }

    function validerAvis(titreBateau) {
        const commentaire = document.getElementById("commentaire").value.trim();
        const etoiles = parseInt(document.getElementById("etoiles").value);

        if (!commentaire || isNaN(etoiles)) {
            alert("Merci de remplir tous les champs.");
            return;
        }

        const avis = JSON.parse(localStorage.getItem("avis") || "[]");
        avis.push({
            titre: titreBateau,
            commentaire: commentaire,
            etoiles: etoiles,
            date: new Date().toLocaleDateString()
        });

        localStorage.setItem("avis", JSON.stringify(avis));
        alert("Merci ! Votre avis a été enregistré.");
        fermerAvisPopup();
    }

    function fermerAvisPopup() {
        const popup = document.querySelector(".avis-popup-overlay");
        if (popup) popup.remove();
    }
</script>
</body>
</html>