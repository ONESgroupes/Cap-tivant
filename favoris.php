<?php
session_start();
require_once 'config.php';

$estConnecte = isset($_SESSION['user_id']);
$favoris = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_bateau'])) {
        $idToAdd = (int) $_POST['id_bateau'];

        if ($estConnecte) {
            $user_id = $_SESSION['user_id'];

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_id = ? AND bateau_id = ?");
            $stmt->execute([$user_id, $idToAdd]);
            $existe = $stmt->fetchColumn();

            if ($existe == 0) {
                $stmt = $pdo->prepare("INSERT INTO favoris (user_id, bateau_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $idToAdd]);
                echo "Ajouté aux favoris.";
            } else {
                echo "Déjà dans vos favoris.";
            }
        } else {
            $favoris = json_decode($_COOKIE['favoris'] ?? '[]', true);
            if (!in_array($idToAdd, $favoris)) {
                $favoris[] = $idToAdd;
                setcookie('favoris', json_encode($favoris), time() + 3600 * 24 * 30, "/");
                echo "Ajouté localement.";
            } else {
                echo "Déjà dans vos favoris.";
            }
        }
        exit;
    } elseif (isset($_POST['retirer_id'])) {
        $idToRemove = (int) $_POST['retirer_id'];

        if ($estConnecte) {
            $user_id = $_SESSION['user_id'];
            $stmt = $pdo->prepare("DELETE FROM favoris WHERE user_id = ? AND bateau_id = ?");
            $stmt->execute([$user_id, $idToRemove]);
            header("Location: favoris.php");
            exit;
        } else {
            $favoris = json_decode($_COOKIE['favoris'] ?? '[]', true);
            if (($key = array_search($idToRemove, $favoris)) !== false) {
                unset($favoris[$key]);
                $favoris = array_values($favoris); // Réindexer le tableau
                setcookie('favoris', json_encode($favoris), time() + 3600 * 24 * 30, "/");
                $_COOKIE['favoris'] = json_encode($favoris); // Met à jour la variable superglobale
            }
            header("Location: favoris.php");
            exit;
        }
    }
}

if ($estConnecte) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT b.* FROM bateaux b JOIN favoris f ON b.id = f.bateau_id WHERE f.user_id = ?");
    $stmt->execute([$user_id]);
    $favoris = $stmt->fetchAll();
} else {
    $favorisIds = json_decode($_COOKIE['favoris'] ?? '[]', true);
    if (is_array($favorisIds) && count($favorisIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($favorisIds), '?'));
        $stmt = $pdo->prepare("SELECT * FROM bateaux WHERE id IN ($placeholders)");
        $stmt->execute($favorisIds);
        $favoris = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Mon panier</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="favoris.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <script src="info-bateau.js" defer></script>
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
<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a id="lien-apropos" class="lien-langue" data-page="a-propos" style="color: #577550; text-decoration: none;">À propos</a>
    <a id="lien-compte" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos" style="color: #577550;">Mon Compte</a>
</div>
<div class="top-bar-background"></div>

<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo-transparent.png" alt="Logo" style="width: 30px;">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-page">Mon panier</h1>
    </div>
</div>
<div class="favoris-container" id="favoris-container">
    <?php if (count($favoris) === 0): ?>
        <p id="texte-aucun-favori" style="text-align:center; width: 100%; font-family: DM Serif Display;">Aucun favori enregistré.</p>
    <?php else: ?>
        <?php foreach ($favoris as $bateau): ?>
            <div class="favori-card" data-id="<?= $bateau['id'] ?>">
                <img class="image-bateau" src="images/<?= htmlspecialchars($bateau['image1']) ?>" alt="<?= htmlspecialchars($bateau['titre']) ?>">
                <h3 class="titre-bateau"><?= htmlspecialchars($bateau['titre']) ?></h3>
                <p class="infos-bateau"><?= htmlspecialchars($bateau['personnes']) ?> &bull; <?= htmlspecialchars($bateau['cabines']) ?></p>
                <a href="info-bateau.php?id=<?= $bateau['id'] ?>" class="btn-voir">Voir l'offre</a>
                <?php if ($estConnecte): ?>
                    <form method="post" action="favoris.php" onsubmit="return confirm('Retirer ce bateau des favoris ?');">
                        <input type="hidden" name="retirer_id" value="<?= $bateau['id'] ?>">
                        <button type="submit" class="btn-retirer lien-langue" data-key="retirer">Retirer</button>
                    </form>
                <?php else: ?>
                    <button class="btn-retirer lien-langue" data-key="retirer" onclick="retirerFavoriLocal(<?= $bateau['id'] ?>)">Retirer</button>
                <?php endif; ?>
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
        const commun = langue === "en" ? CommunEN : CommunFR;
        const favorisTxt = langue === "en" ? FavorisEN : FavorisFR;
        const bateauxData = langue === "en" ? bateauxEN : bateaux;

        document.title = favorisTxt.titre;
        document.getElementById("page-title").textContent = favorisTxt.titre;
        document.getElementById("titre-page").textContent = favorisTxt.titre;

        const aucunFavori = document.getElementById("texte-aucun-favori");
        if (aucunFavori) aucunFavori.textContent = favorisTxt.aucun;

        document.querySelectorAll(".favori-card").forEach(card => {
            const id = parseInt(card.dataset.id);
            const bateau = bateauxData.find(b => b.id === id);
            if (bateau) {
                card.querySelector(".image-bateau").src = bateau.image1;
                card.querySelector(".image-bateau").alt = bateau.titre;
                card.querySelector(".titre-bateau").textContent = bateau.titre;
                card.querySelector(".infos-bateau").textContent = `${bateau.personnes} • ${bateau.cabines}`;
            }
        });

        document.querySelectorAll(".btn-voir").forEach(el => {
            el.textContent = favorisTxt.voir;
        });
        document.querySelectorAll(".btn-retirer").forEach(el => {
            el.textContent = favorisTxt.retirer;
        });

        document.getElementById("lien-apropos").textContent = commun.info;
        document.getElementById("lien-compte").textContent = commun.compte;
        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-contact").textContent = commun.contact;
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

        if (!<?= json_encode($estConnecte) ?>) {
            const favorisIds = JSON.parse(localStorage.getItem("favoris") || "[]");
            if (favorisIds.length > 0) {
                const container = document.getElementById("favoris-non-connecte");
                const texte = langue === "en" ? FavorisEN : FavorisFR;
                favorisIds.forEach(id => {
                    const bateau = bateauxData.find(b => b.id === id);
                    if (!bateau) return;

                    const card = document.createElement("div");
                    card.className = "favori-card";
                    card.innerHTML = `
                    <img class="image-bateau" src="${bateau.image1}" alt="${bateau.titre}">
                    <h3 class="titre-bateau">${bateau.titre}</h3>
                    <p class="infos-bateau">${bateau.personnes} • ${bateau.cabines}</p>
                    <a href="info-bateau.php?id=${bateau.id}" class="btn-voir">${texte.voir}</a>
                    <button class="btn-retirer" onclick="retirerFavoriLocal(${bateau.id})">${texte.retirer}</button>
                `;
                    container.appendChild(card);
                });
            }
        }

        if (!<?= json_encode($estConnecte) ?>) {
            const favoris = localStorage.getItem("favoris");
            if (favoris) {
                document.cookie = "favoris=" + encodeURIComponent(favoris) + "; path=/";
            }
        }
    });

    function retirerFavoriLocal(id) {
        let favoris = JSON.parse(localStorage.getItem("favoris") || "[]");

        // Supprimer uniquement l'ID ciblé
        favoris = favoris.filter(fav => fav !== id);

        // Met à jour localStorage et cookie AVANT rechargement
        localStorage.setItem("favoris", JSON.stringify(favoris));
        document.cookie = "favoris=" + encodeURIComponent(JSON.stringify(favoris)) + "; path=/";

        // Supprime visuellement la carte sans rechargement
        const card = document.querySelector(`.favori-card[data-id="${id}"]`);
        if (card) card.remove();
    }
</script>
</body>
</html>
