<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php'; // ✅ ajout nécessaire pour que $pdo soit défini

session_start();
$estConnecte = isset($_SESSION['user_id']);

$depart = $_GET['depart'] ?? null;
$arrivee = $_GET['arrivee'] ?? null;
$type = $_GET['type'] ?? null;
$personnes = isset($_GET['personnes']) ? (int)$_GET['personnes'] : null;



if ($depart && $arrivee) {
    $query = "
    SELECT * FROM bateaux b
    WHERE NOT EXISTS (
        SELECT 1 FROM historique h
        WHERE h.bateau_id = b.id
          AND h.date_debut <= :date_fin
          AND h.date_fin >= :date_debut
    )
";

    $params = [
        ':date_debut' => $depart,
        ':date_fin' => $arrivee
    ];

    if ($personnes) {
        $query .= " AND b.personnes >= :personnes";
        $params[':personnes'] = $personnes;
    }

    if ($type) {
        // On simplifie pour éviter les problèmes d'accents et de majuscules
        $type = strtolower(trim($type));

        if ($type === 'moteur') {
            $query .= " AND LOWER(REPLACE(REPLACE(REPLACE(b.categorie, 'à', 'a'), 'â', 'a'), 'é', 'e')) LIKE '%moteur%'";
        } elseif ($type === 'voile') {
            $query .= " AND LOWER(REPLACE(REPLACE(REPLACE(b.categorie, 'à', 'a'), 'â', 'a'), 'é', 'e')) LIKE '%voile%'";
        }
    }




    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bateauxDisponibles = $stmt->fetchAll();


    $stmt->execute([
        ':date_debut' => $depart,
        ':date_fin' => $arrivee
    ]);
    $bateauxDisponibles = $stmt->fetchAll();
} else {
    $bateauxDisponibles = $pdo->query("SELECT * FROM bateaux")->fetchAll();
}

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Offres</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="offre.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <script src="info-bateau.js"></script>
    <script>
        const bateauxDisponibles = <?= json_encode($bateauxDisponibles, JSON_UNESCAPED_UNICODE) ?>;
    </script>
</head>
<body style="background-color: #c5d8d3;">

<!-- Menu -->
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu-vert.png" alt="Menu">
</div>

<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content" id="menu-links"></div>
</div>

<!-- En-tête -->
<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo-transparent.png" alt="Logo" style="width: 30px;">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-page">Offres</h1>
    </div>
</div>

<!-- Liens à droite -->
<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a id="a-propos-link" href="a-propos.php" style="color: #577550; text-decoration: none;">À propos</a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos" style="color: #577550;">Mon Compte</a>
    <a href="favoris.php">
        <img src="images/panier.png" alt="Panier">
    </a>
</div>

<!-- Carrousel -->
<div class="double-carrousel" id="carrousel-container"></div>

<!-- Pied de page -->
<div class="bouton-bas">
    <a id="lien-mentions" href="MentionsLegales.php" class="bottom-text" style="color : #577550;"></a>
    <span class="bottom-text" style="color: #577550;">•</span>
    <a id="lien-contact" href="Contact.php" class="bottom-text" style="color: #577550;"></a>
</div>

<script>
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
        const langue = localStorage.getItem("langue") || "fr";
        const texte = langue === "en" ? OffreEN : OffreFR;
        const commun = langue === "en" ? CommunEN : CommunFR;
        const bateauxData = bateauxDisponibles;

        const params = new URLSearchParams(window.location.search);
        const portParam = params.get('port');
        const typeParam = params.get('type');
        const nbPersonnes = params.get('personnes');

        let filteredBateaux = bateauxData;

        if (nbPersonnes) {
            filteredBateaux = filteredBateaux.filter(b => parseInt(b.personnes) >= parseInt(nbPersonnes));
        }

        if (portParam) {
            filteredBateaux = filteredBateaux.filter(b => b.port.toLowerCase() === portParam.toLowerCase());
        }

        if (typeParam === "moteur" || typeParam === "voile") {
            filteredBateaux = filteredBateaux.filter(b =>
                b.categorie && b.categorie.toLowerCase().trim() === typeParam
            );
        }

        // Remplir les textes dynamiques
        document.getElementById("page-title").textContent = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("a-propos-link").textContent = commun.info;
        document.getElementById("compte-link").textContent = commun.compte;
        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        document.getElementById("lang-dropdown").innerHTML = langue === "en"
            ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;

        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        const menuContent = document.getElementById("menu-links");
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a href="${liens[index]}.php">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';

        const container = document.getElementById('carrousel-container');

        filteredBateaux.forEach((bateau, index) => {
            const div = document.createElement("div");
            div.className = `carrousel ${bateau.categorie}`;
            div.setAttribute('data-id', index);
            div.innerHTML = `
            <button class="prev" onclick="changeSlide(this, -1)">❮</button>
            <div class="slides">
                <a href="info-bateau.php?id=${bateau.id}">
                    <img src="${bateau.image1 || 'images/default-boat.jpg'}" class="slide active">
                    ${bateau.image2 ? `<img src="${bateau.image2}" class="slide">` : ''}
                    <div class="background">
                        <a class="description">${bateau.port}, ${bateau.personnes}, ${bateau.longueur}</a>
                        <div class="ligne-info">
                            <p class="description" style="color: #000000"><strong>${bateau.prix}</strong></p>
                        </div>
                    </div>
                </a>
            </div>
            <button class="next" onclick="changeSlide(this, 1)">❯</button>
        `;
            container.appendChild(div);
        });

        if (filteredBateaux.length === 0) {
            container.innerHTML = `<p style='text-align:center; font-size:1.2em; margin-top: 100px'>${texte.aucunBateau}</p>`;
        }
    });


    const carrouselIndexes = new Map();
    function changeSlide(button, direction) {
        const carrousel = button.closest('.carrousel');
        const slides = carrousel.querySelectorAll('.slide');

        if (!carrouselIndexes.has(carrousel)) {
            carrouselIndexes.set(carrousel, 0);
        }

        let currentIndex = carrouselIndexes.get(carrousel);
        slides[currentIndex].classList.remove('active');

        currentIndex = (currentIndex + direction + slides.length) % slides.length;

        slides[currentIndex].classList.add('active');
        carrouselIndexes.set(carrousel, currentIndex);
    }
</script>
</body>
</html>
