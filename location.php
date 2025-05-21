<?php
session_start();
$estConnecte = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Location</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="location.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <script src="info-bateau.js"></script>
    <script src="ports-info.js"></script>
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
            background-color: #ffffff;
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

<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content" id="menu-links"></div>
</div>

<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a id="a-propos-link" href="a-propos.php" class="top-infos">À propos</a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos">Mon Compte</a>
    <a href="favoris.php">
        <img src="images/panier.png" alt="Panier">
    </a>
</div>

<div class="top-bar-background"></div>

<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo.png" alt="Logo">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-page" style="color: #e0e0d5"></h1>
    </div>

    <div class="formulaire-connexion">
        <div class="background">
            <div class="champ-double" style="gap: 20px">
                <div class="champ-date" >
                    <label for="départ" id="depart"></label>
                    <input type="date" id="départ">
                </div>
                <div class="champ-date" >
                    <label for="arrivée" id="arrive"></label>
                    <input type="date" id="arrivée">
                </div>
            </div>
            <div class="champ-trois">
                <button class="bouton-type" id="btn-moteur" onclick="selectType(this)"></button>
                <button class="bouton-type" id="btn-voile" onclick="selectType(this)"></button>
            </div>
            <div class="champ-double" style="gap: 20px;">
                <div class="champ-date">
                    <input type="text" id="lieu" placeholder="">
                    <div id="suggestions-lieu" class="suggestions-box"></div>
                </div>
                <div class="champ-date">
                    <input type="number" id="personnes" placeholder="">
                </div>
            </div>

            <div class="champ" style="padding: 22px;">
                <br>
                <button id="lien-recherche" class="connexion" onclick="verifierEtRediriger()">Rechercher</button>
            </div>
        </div>
    </div>
</div>

<div class="bouton-bas" style="background: transparent">
    <a id="lien-mentions" href="MentionsLegales.php" class="bottom-text">Mentions légales</a>
    <span class="bottom-text">•</span>
    <a id="lien-contact" href="Contact.php" class="bottom-text">Contact</a>
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
        const texte = langue === "en" ? LocationEN : LocationFR;
        const commun = langue === "en" ? CommunEN : CommunFR;

        document.getElementById("page-title").textContent = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        document.getElementById("depart").textContent = texte.depart;
        document.getElementById("arrive").textContent = texte.arrive;
        document.getElementById("lieu").placeholder = texte.lieu;
        document.getElementById("personnes").placeholder = texte.personnes;
        document.getElementById("btn-moteur").textContent = texte.moteur;
        document.getElementById("btn-voile").textContent = texte.voile;
        document.getElementById("lien-recherche").textContent = texte.recherche;

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
    });

    let typeSelectionne = "";
    function selectType(button) {
        const boutonClique = button.textContent.trim().toLowerCase();
        const lienRecherche = document.getElementById("lien-recherche");

        if (button.classList.contains("active")) {
            button.classList.remove("active");
            typeSelectionne = "";
            lienRecherche.href = "offre.php";
        } else {
            document.querySelectorAll('.bouton-type').forEach(btn => btn.classList.remove('active'));
            button.classList.add("active");
            typeSelectionne = boutonClique;

            if (typeSelectionne === "moteur" || typeSelectionne === "motor") {
                lienRecherche.href = "offre.php?type=moteur";
            } else if (typeSelectionne === "à voile" || typeSelectionne === "sailing") {
                lienRecherche.href = "offre.php?type=voile";
            } else {
                lienRecherche.href = "offre.php";
            }
        }
        mettreAJourLienRecherche();
    }
    function mettreAJourLienRecherche() {
        const lieu = document.getElementById("lieu").value.trim();
        const personnes = document.getElementById("personnes").value.trim();
        const lienRecherche = document.getElementById("lien-recherche");

        let url = "offre.php?";
        if (lieu !== "") {
            url += `port=${encodeURIComponent(lieu)}&`;
        }
        if (typeSelectionne) {
            url += `type=${typeSelectionne}&`;
        }
        if (personnes !== "") {
            url += `personnes=${encodeURIComponent(personnes)}&`;
        }

        lienRecherche.href = url;
    }


    document.getElementById("lieu").addEventListener("input", mettreAJourLienRecherche);
    document.getElementById("btn-moteur").addEventListener("click", mettreAJourLienRecherche);
    document.getElementById("btn-voile").addEventListener("click", mettreAJourLienRecherche);
    function verifierEtRediriger() {
        const depart = document.getElementById("départ").value;
        const arrivee = document.getElementById("arrivée").value;
        const lieu = document.getElementById("lieu").value.trim();
        const personnes = document.getElementById("personnes").value.trim();

        let url = "offre.php?";
        if (lieu !== "") {
            url += `port=${encodeURIComponent(lieu)}&`;
        }
        if (typeSelectionne) {
            url += `type=${typeSelectionne}&`;
        }
        if (personnes !== "") {
            url += `personnes=${encodeURIComponent(personnes)}&`;
        }

        url += `depart=${depart}&arrivee=${arrivee}`;
        window.location.href = url;
    }
    document.getElementById("lieu").addEventListener("input", function () {
        const input = this.value.toLowerCase();
        const suggestionsBox = document.getElementById("suggestions-lieu");
        suggestionsBox.innerHTML = ""; // On vide les anciennes suggestions

        // Si rien n'est tapé : on affiche tous les ports
        const suggestions = input === ""
            ? ports
            : ports.filter(port => port.name.toLowerCase().startsWith(input));

        suggestions.forEach(port => {
            const div = document.createElement("div");
            div.textContent = port.name;
            div.style.cursor = "pointer";
            div.onclick = function () {
                document.getElementById("lieu").value = port.name;
                suggestionsBox.innerHTML = "";
                mettreAJourLienRecherche();
            };
            suggestionsBox.appendChild(div);
        });
    });

    document.addEventListener("click", function(event) {
        const suggestionsBox = document.getElementById("suggestions-lieu");
        if (!document.getElementById("lieu").contains(event.target)) {
            suggestionsBox.innerHTML = "";
        }
    });

</script>

</body>
</html>