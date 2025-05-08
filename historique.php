<?php
session_start();
require_once 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$estConnecte = isset($_SESSION['user_id']);
$historique = [];
$avisParBateau = [];

if ($estConnecte) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT b.* FROM historique h JOIN bateaux b ON h.bateau_id = b.id WHERE h.user_id = ? ORDER BY h.created_at DESC");
    $stmt->execute([$user_id]);
    $historique = $stmt->fetchAll();

    $stmtAvis = $pdo->prepare("SELECT boat_id, rating, comment, created_at FROM reviews WHERE user_id = ?");
    $stmtAvis->execute([$user_id]);
    $avisParBateau = $stmtAvis->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
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
    <div class="menu-content" id="menu-links"></div>
</div>

<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a id="lien-apropos" class="lien-langue" data-page="a-propos" style="color: #577550; text-decoration: none;">A propos</a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos lien-langue" data-key="compte" style="color: #577550">Mon Compte</a>
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
<div class="historique-container" id="historique-container">
    <?php if (count($historique) === 0): ?>
        <p style="text-align:center;">Aucune réservation.</p>
    <?php else: ?>
        <?php foreach ($historique as $bateau): ?>
            <div class="historique-card">
                <h2><?= htmlspecialchars($bateau['titre']) ?></h2>
                <p><strong class="label-port">Port :</strong> <?= htmlspecialchars($bateau['port']) ?></p>
                <p><strong class="label-personnes">Personnes :</strong> <?= htmlspecialchars($bateau['personnes']) ?></p>
                <p><strong class="label-cabines">Cabines :</strong> <?= htmlspecialchars($bateau['cabines']) ?></p>
                <p><strong class="label-longueur">Longueur :</strong> <?= htmlspecialchars($bateau['longueur']) ?></p>
                <p><strong class="label-prix">Prix :</strong> <?= htmlspecialchars($bateau['prix']) ?></p>
                <img src="images/<?= htmlspecialchars($bateau['image1']) ?>" alt="<?= htmlspecialchars($bateau['titre']) ?>">
                <?php if (isset($avisParBateau[$bateau['id']])): ?>
                    <!-- Pas de bouton si l'avis existe -->
                <?php else: ?>
                    <button class="btn-avis" onclick="laisserAvis('<?= htmlspecialchars($bateau['titre']) ?>')">Avis</button>
                <?php endif; ?>
                <?php
                $idBateau = $bateau['id'];
                if (isset($avisParBateau[$idBateau])) {
                    foreach ($avisParBateau[$idBateau] as $avis) {
                        echo "<div class='avis'>";
                        echo "<p><strong class='avis-comment-label'>Commentaire :</strong> <span class='avis-comment'>" . htmlspecialchars($avis['comment']) . "</span></p>";
                        echo "<p><strong class='avis-note-label'>Note :</strong> <span class='avis-note'>" . str_repeat('⭐', (int)$avis['rating']) . "</span></p>";
                        echo "<p><em class='avis-poste-label'>Posté le </em><em class='avis-date'>" . date('d/m/Y', strtotime($avis['created_at'])) . "</em></p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='no-review'><em>Vous n'avez pas encore laissé d'avis pour ce bateau.</em></p>";
                }
                ?>
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

        document.title = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";

        document.getElementById("lien-apropos").textContent = commun.info;
        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("compte-link").textContent = commun.compte;

        document.querySelectorAll(".label-port").forEach(el => el.textContent = texte.port + " :");
        document.querySelectorAll(".label-personnes").forEach(el => el.textContent = texte.personnes + " :");
        document.querySelectorAll(".label-cabines").forEach(el => el.textContent = texte.cabines + " :");
        document.querySelectorAll(".label-longueur").forEach(el => el.textContent = texte.longueur + " :");
        document.querySelectorAll(".label-prix").forEach(el => el.textContent = texte.prix + " :");

        document.querySelectorAll(".btn-avis").forEach(btn => {btn.textContent = texte.avis;});

        document.querySelectorAll(".avis-comment-label").forEach(el => el.textContent = texte.commentaire + " :");
        document.querySelectorAll(".avis-note-label").forEach(el => el.textContent = texte.note + " :");
        document.querySelectorAll(".avis-poste-label").forEach(el => el.textContent = texte.poste + " ");

        const langDropdown = document.getElementById("lang-dropdown");
        langDropdown.innerHTML = langue === "en"
            ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;

        const menuContent = document.getElementById("menu-links");
        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a class="lien-langue" data-page="${liens[index]}">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">&times;</span>';

        document.querySelectorAll(".lien-langue").forEach(lien => {
            const page = lien.getAttribute("data-page");
            lien.setAttribute("href", `${page}.php`);
        });
    });

    function laisserAvis(titreBateau) {
        const langue = getLangue();
        const texte = langue === "en" ? HistoriqueEN : HistoriqueFR;

        const popup = document.createElement("div");
        popup.className = "avis-popup-overlay";
        popup.innerHTML = `
        <div class="avis-popup-bulle">
            <h2>${texte.popup_titre}</h2>
            <p>${texte.popup_texte}</p>
            <textarea id="commentaire" placeholder="${texte.popup_commentaire}"></textarea><br><br>
            <label>${texte.popup_label_note} :</label>
            <select id="etoiles">
                <option value="1">⭐</option>
                <option value="2">⭐⭐</option>
                <option value="3">⭐⭐⭐</option>
                <option value="4">⭐⭐⭐⭐</option>
                <option value="5">⭐⭐⭐⭐⭐</option>
            </select><br><br>
            <button onclick="validerAvis('${titreBateau}')">${texte.popup_bouton_valider}</button>
            <button onclick="fermerAvisPopup()">${texte.popup_bouton_fermer}</button>
        </div>
    `;
        document.body.appendChild(popup);
    }


    function validerAvis(titreBateau) {
        const commentaire = document.getElementById("commentaire").value.trim();
        const etoiles = document.getElementById("etoiles").value;

        if (!commentaire || !etoiles) {
            alert("Merci de remplir tous les champs.");
            return;
        }

        const form = new FormData();
        form.append('titre', titreBateau);
        form.append('commentaire', commentaire);
        form.append('etoiles', etoiles);

        fetch('enregistrer_avis.php', {
            method: 'POST',
            body: form
        })
            .then(() => {
                alert("Merci ! Votre avis a été enregistré.");
                fermerAvisPopup();
                location.reload(); // Recharge la page pour afficher l’avis si besoin
            })
            .catch(() => {
                alert("Une erreur est survenue lors de l’envoi de l’avis.");
            });
    }

    function fermerAvisPopup() {
        const popup = document.querySelector(".avis-popup-overlay");
        if (popup) popup.remove();
    }
</script>
</body>
</html>