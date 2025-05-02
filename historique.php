<?php
session_start();
require_once 'config.php';  // Inclure le fichier de configuration pour la connexion à la base

$estConnecte = isset($_SESSION['user_id']);
$historique = [];

if ($estConnecte) {
    $user_id = $_SESSION['user_id'];

    // Récupérer l'historique des réservations de l'utilisateur
    $stmt = $pdo->prepare("SELECT h.date_reservation, b.title, b.image1, b.image2, b.capacity, b.cabins, b.length, b.price_per_week, b.port_name 
                           FROM historique h 
                           JOIN boats b ON h.bateau_id = b.id 
                           WHERE h.user_id = ?");
    $stmt->execute([$user_id]);
    $historique = $stmt->fetchAll();

    if (count($historique) > 0) {
        // Afficher l'historique
        foreach ($historique as $reservation) {
            echo "<div class='historique-card'>";
            echo "<h2>" . htmlspecialchars($reservation['title']) . "</h2>";
            echo "<p><strong>Port :</strong> " . htmlspecialchars($reservation['port_name']) . "</p>";
            echo "<p><strong>Nombre de personnes :</strong> " . htmlspecialchars($reservation['capacity']) . "</p>";
            echo "<p><strong>Cabines :</strong> " . htmlspecialchars($reservation['cabins']) . "</p>";
            echo "<p><strong>Longueur :</strong> " . htmlspecialchars($reservation['length']) . "</p>";
            echo "<p><strong>Prix par semaine :</strong> " . htmlspecialchars($reservation['price_per_week']) . "</p>";
            echo "<img src='" . htmlspecialchars($reservation['image1']) . "' alt='" . htmlspecialchars($reservation['title']) . "'>";
            echo "<button>Voir les détails</button>";  // Vous pouvez ajouter un lien pour voir plus de détails sur le bateau
            echo "</div>";
        }
    } else {
        echo "<p>Aucune réservation trouvée.</p>";
    }
} else {
    echo "<p>Vous devez être connecté pour voir votre historique.</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
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
        <h1 class="page-title" id="titre-page">Historique</h1>
    </div>
</div>
<div id="historique-container" class="historique-container"></div>
<div class="bouton-bas">
    <button id="btn-clear" onclick="viderHistorique()" class="btn-vider-historique">Vider l'historique</button>
    <a id="lien-mentions" class="bottom-text lien-langue" data-page="MentionsLegales" style="color: #577550">Mentions légales</a>
    <span class="bottom-text" style="color: #577550">&bull;</span>
    <a id="lien-contact" class="bottom-text lien-langue" data-page="Contact" style="color: #577550">Contact</a>
</div>

<script>
    function toggleMenu() {
        const menu = document.getElementById("menu-overlay");
        menu.classList.toggle("active");
    }

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

    document.addEventListener("DOMContentLoaded", function () {
        const langue = getLangue();
        const texte = langue === "en" ? HistoriqueEN : HistoriqueFR;
        const commun = langue === "en" ? CommunEN : CommunFR;
        const bateauxSource = langue === "en" ? boatsEN : boats;

        document.title = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        document.getElementById("btn-clear").textContent = texte.vider;
        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";

        const langDropdown = document.getElementById("lang-dropdown");
        langDropdown.innerHTML = langue === "en"
            ? `<img src="images/drapeau-francais.png" alt="French" class="drapeau-option" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="English" class="drapeau-option" onclick="changerLangue('en')">`;

        const menuContent = document.getElementById("menu-links");
        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a href="${liens[index]}.php" class="lien-langue">${item}</a>`; // Remplacer data-page par href
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">&times;</span>';

        document.getElementById("lien-apropos").textContent = commun.info;
        document.getElementById("lien-compte").textContent = commun.compte;
        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-contact").textContent = commun.contact;

        document.querySelectorAll(".lien-langue").forEach(lien => {
            const page = lien.getAttribute("data-page");
            lien.setAttribute("href", `${page}.php`);
        });

        const historique = JSON.parse(localStorage.getItem("historique") || "[]");
        const container = document.getElementById("historique-container");

        if (historique.length === 0) {
            container.innerHTML = `<p style='text-align:center;'>${texte.vide}</p>`;
        } else {
            historique.forEach(bateau => {
                const original = boats.find(b => b.titre === bateau.titre);
                const traduit = bateauxSource.find(b => b.id === original?.id);

                if (traduit) {
                    container.innerHTML += `
                        <div class="historique-card">
                            <h2>${traduit.titre}</h2>
                            <p><strong>${texte.port}:</strong> ${traduit.port}</p>
                            <p><strong>${texte.personnes}:</strong> ${traduit.personnes}</p>
                            <p><strong>${texte.cabines}:</strong> ${traduit.cabines}</p>
                            <p><strong>${texte.longueur}:</strong> ${traduit.longueur}</p>
                            <p><strong>${texte.prix}:</strong> ${traduit.prix}</p>
                            <img src="${traduit.image1}" alt="${traduit.titre}">
                            <button onclick="laisserAvis('${traduit.titre}')">${texte.avis}</button>
                        </div>
                    `;
                }
            });
        }
    });

    function viderHistorique() {
        if (confirm("Êtes-vous sûr de vouloir vider votre historique ?")) {
            localStorage.removeItem("historique");
            location.reload();
        }
    }

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
