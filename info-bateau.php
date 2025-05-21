<?php
session_start();
$estConnecte = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail du Bateau</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="offre.css">
    <link rel="stylesheet" href="info-bateau.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="ports-info.js"></script>
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
<body>
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu.png" alt="Menu">
</div>
<div class="top-left retour-offre">
    <a href="offre.php" id="retour-offre" style="color: rgba(224,224,213,0.65)"></a>
    <label id="titre-bateau-label" style="font-size: 0.85em; color: rgba(224,224,213,0.65); font-family: 'DM Serif Display', serif;"></label>
</div>
<div class="top-bar-background"></div>

<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo.png" alt="Logo">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-bateau" style="color: #e0e0d5;"></h1>
    </div>
</div>
<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content">
        <a href="location.php" id="menu-location">LOCATION</a>
        <a href="ports.php" id="menu-ports">NOS PORTS</a>
        <a href="MonCompte.php" id="menu-compte">MON COMPTE</a>
        <a href="historique.php" id="menu-historique">HISTORIQUE</a>
        <a href="faq.php" id="menu-faq">FAQ</a>
        <a href="Avis.php" id="menu-avis">AVIS</a>
        <span onclick="toggleMenu()" class="close-menu">✕</span>
    </div>
</div>
<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a href="a-propos.php" id="menu-apropos" style="color: #e0e0d5; text-decoration: none;">À propos</a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos">Mon Compte</a>
    <a href="favoris.php"><img src="images/panier.png" alt="Panier"></a>
</div>

<div class="double-carrousel" id="contenu"></div>

<div class="bouton-bas" style="background-color: transparent;">
    <a href="MentionsLegales.php" id="footer-mentions" class="bottom-text">Mentions légales</a>
    <span class="bottom-text">•</span>
    <a href="Contact.php" id="footer-contact" class="bottom-text">Contact</a>
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
    function getLangue() {
        return localStorage.getItem("langue") || "fr";
    }

    window.onload = function () {
        const langue = getLangue();
        const commun = langue === 'en' ? CommunEN : CommunFR;
        const listeBateaux = langue === 'en' ? bateauxEN : bateaux;
        const texte = langue === 'en' ? InfoBateauEN : InfoBateauFR;
        const lienCompte = document.getElementById("compte-link");
        if (lienCompte && commun && commun.compte) {
            lienCompte.textContent = commun.compte;
        }

        document.getElementById("current-lang").src = langue === 'en' ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        document.getElementById("lang-dropdown").innerHTML = langue === 'en'
            ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;

        document.getElementById("menu-location").textContent = commun.menu[0];
        document.getElementById("menu-ports").textContent = commun.menu[1];
        document.getElementById("menu-compte").textContent = commun.menu[2];
        document.getElementById("menu-historique").textContent = commun.menu[3];
        document.getElementById("menu-faq").textContent = commun.menu[4];
        document.getElementById("menu-avis").textContent = commun.menu[5];
        document.getElementById("menu-apropos").textContent = commun.info;
        document.getElementById("footer-mentions").textContent = commun.mentions;
        document.getElementById("footer-contact").textContent = commun.contact;
        document.getElementById("retour-offre").textContent = texte.retour + " >";


        const params = new URLSearchParams(window.location.search);
        const id = parseInt(params.get("id"));
        const bateau = listeBateaux.find(b => b.id === id);
        if (!bateau) return;

        const avisList = JSON.parse(localStorage.getItem("avis") || "[]");
        const avisBateau = avisList.filter(a => a.titre === bateau.titre && !isNaN(a.etoiles));
        const moyenne = avisBateau.length ? (avisBateau.reduce((acc, a) => acc + Number(a.etoiles), 0) / avisBateau.length).toFixed(1) : 0;
        const avisText = avisBateau.length ? `⭐ ${moyenne}/5 (${avisBateau.length} ${langue === 'en' ? 'reviews' : 'avis'})` : `⭐ - (0 ${langue === 'en' ? 'reviews' : 'avis'})`;

        document.getElementById("titre-bateau").textContent = bateau.titre;
        document.getElementById("titre-bateau-label").textContent = bateau.titre;

        document.getElementById("contenu").innerHTML = `
        <div class="detail-container">
          <div class="bloc-gauche">
            <div class="carrousel" data-id="${bateau.id}">
              <button class="prev" onclick="changeSlide(this, -1)">❮</button>
              <img src="${bateau.image1}" class="slide active">
              <img src="${bateau.image2}" class="slide">
              <button class="next" onclick="changeSlide(this, 1)">❯</button>
            </div>
            <div class="texte-photo" style="color: floralwhite">
              <p><span>${avisText}</span> <strong><a href="ports.php?port=${encodeURIComponent(bateau.port)}" style="margin-left: 80px; color: floralwhite;">${bateau.port}</a></strong></p>
            </div>
          </div>
          <div class="background">
            <p class="infos">${bateau.personnes}</p>
            <p class="infos">${bateau.cabines}</p>
            <p class="infos">${bateau.longueur}</p>
          </div>
          <div class="detail-infos">
            <div class="carte-container"><div id="map"></div></div>
          </div>
        </div>
        <div class="detail-footer">
          <p class="detail-prix">${bateau.prix}</p>
          <div class="boutons-actions">
            <a href="payement.php?id=${bateau.id}" class="btn-reserver">${texte.reserver}</a>
            <button class="btn-favoris" onclick="ajouterAuxFavoris(${bateau.id})">${texte.favori}</button>
          </div>
        </div>`;

        setTimeout(() => {
            const port = ports.find(p => p.name.toLowerCase() === bateau.port.toLowerCase());
            if (port) {
                const map = L.map('map').setView(port.coords, 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
                const popupContent = `
            <div class="popup-content">
              <h3>${port.name}</h3>
              <p>${port.boats} ${langue === 'en' ? 'boats available' : 'bateaux disponibles'}</p>
              <img src="${port.image}" alt="${port.name}" style="width:100%; border-radius: 8px; margin-top:5px;">
              <br><br>
              <a href="ports.php?port=${encodeURIComponent(port.name)}&lat=${port.coords[0]}&lng=${port.coords[1]}&zoom=13" style="color:#577550; text-decoration:underline;">
                ${langue === 'en' ? 'See offers' : 'Voir les offres'}
              </a>
            </div>`;
                const marker = L.marker(port.coords).addTo(map);
                marker.bindPopup(popupContent);

// 👉 Ajoute ceci pour rediriger au clic
                marker.on('click', () => {
                    window.location.href = `ports.php?port=${encodeURIComponent(port.name)}&lat=${port.coords[0]}&lng=${port.coords[1]}&zoom=13`;
                });

            }
        }, 0);
    };

    function changeSlide(button, direction) {
        const carrousel = button.closest('.carrousel');
        const slides = carrousel.querySelectorAll('.slide');
        let currentIndex = Array.from(slides).findIndex(slide => slide.classList.contains('active'));
        slides[currentIndex].classList.remove('active');
        let newIndex = (currentIndex + direction + slides.length) % slides.length;
        slides[newIndex].classList.add('active');
    }

    function ajouterAuxFavoris(id) {
        fetch('favoris.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id_bateau=${id}`
        })
            .then(response => response.text())
            .then(data => alert(data)) // ici tu peux afficher "Ajouté" ou "Déjà ajouté"
            .catch(error => console.error('Erreur AJAX:', error));
    }

</script>

</body>
</html>
