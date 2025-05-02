<?php
session_start();
require_once 'config.php';


$faq_data = [
    [
        'question_fr' => "Comment réserver un bateau ?",
        'reponse_fr'  => "Il vous suffit de vous connecter, choisir un port et sélectionner un bateau disponible.",
        'question_en' => "How to book a boat?",
        'reponse_en'  => "Just log in, choose a port, and pick an available boat.",
    ],
    [
        'question_fr' => "Puis-je annuler ma réservation ?",
        'reponse_fr'  => "Oui, vous pouvez annuler 24h avant sans frais.",
        'question_en' => "Can I cancel my booking?",
        'reponse_en'  => "Yes, you can cancel up to 24h in advance free of charge.",
    ],
    [
        'question_fr' => "Y a-t-il une assistance en cas de problème ?",
        'reponse_fr'  => "Oui, un service d’assistance est disponible 24/7.",
        'question_en' => "Is there support in case of issues?",
        'reponse_en'  => "Yes, support is available 24/7.",
    ]
];

session_start();
$estConnecte = isset($_SESSION['user_id']);


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">FAQ</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="faq.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <script src="info-bateau.js" defer></script>
    <script>
        function getLangue() {
            return localStorage.getItem("langue") || "fr";
        }

        function toggleMenu() {
            const menu = document.getElementById("menu-overlay");
            menu.classList.toggle("active");
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
            const texte = langue === "en" ? FAQEN : FAQFR;
            const commun = langue === "en" ? CommunEN : CommunFR;

            document.title = texte.titre;
            document.querySelector(".page-title").textContent = texte.titre;

            document.getElementById("current-lang").src = langue === "en"
                ? "images/drapeau-anglais.png"
                : "images/drapeau-francais.png";

            const langDropdown = document.getElementById("lang-dropdown");
            langDropdown.innerHTML = langue === "en"
                ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
                : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;

            const menuContent = document.getElementById("menu-links");
            const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
            menuContent.innerHTML = commun.menu.map((item, index) => {
                return `<a class="lien-langue" data-page="${liens[index]}">${item}</a>`;
            }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';

            document.querySelectorAll(".lien-langue").forEach(lien => {
                const page = lien.getAttribute("data-page");
                lien.setAttribute("href", `${page}.php`);
            });

            const faqData = <?= json_encode($faq_data) ?>;
            const container = document.querySelector(".faq-container");
            container.innerHTML = faqData.map(item => {
                const q = langue === "en" ? item.question_en : item.question_fr;
                const a = langue === "en" ? item.reponse_en : item.reponse_fr;
                return `
          <div class="faq-item">
            <button class="faq-question">${q}</button>
            <div class="faq-answer"><p>${a}</p></div>
          </div>`;
            }).join("");

            // gestion du toggle des questions
            const questions = document.querySelectorAll('.faq-question');
            questions.forEach(q => {
                q.addEventListener('click', () => {
                    const answer = q.nextElementSibling;

                    document.querySelectorAll('.faq-answer').forEach(a => {
                        if (a !== answer) {
                            a.style.maxHeight = null;
                        }
                    });

                    if (answer.style.maxHeight) {
                        answer.style.maxHeight = null;
                    } else {
                        answer.style.maxHeight = answer.scrollHeight + "px";
                    }
                });
            });

            document.getElementById("lien-mentions").textContent = commun.mentions;
            document.getElementById("lien-mentions").setAttribute("data-page", "MentionsLegales");
            document.getElementById("lien-contact").textContent = commun.contact;
            document.getElementById("lien-contact").setAttribute("data-page", "Contact");

            const lienCompte = document.getElementById("lien-compte");
            if (lienCompte) {
                lienCompte.textContent = commun.compte;
                lienCompte.setAttribute("data-page", "Connexion");
            }

            const lienApropos = document.getElementById("lien-apropos");
            if (lienApropos) {
                lienApropos.textContent = commun.info;
                lienApropos.setAttribute("href", "a-propos.php");
            }
        });
    </script>
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
    <a id="lien-apropos" class="lien-langue" data-page="a-propos" style="color: #577550;">À propos</a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos">Mon Compte</a>
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
        <h1 class="page-title">Foire aux questions</h1>
    </div>
</div>

<div class="faq-container" style="margin-top: 200px;">
    <!-- Les questions dynamiques sont injectées ici par JS -->
</div>

<div class="bouton-bas">
    <a id="lien-mentions" class="bottom-text lien-langue" data-page="MentionsLegales" style="color : #577550;">Mentions légales</a>
    <span class="bottom-text" style="color: #577550;">•</span>
    <a id="lien-contact" class="bottom-text lien-langue" data-page="Contact" style="color: #577550;">Contact</a>
</div>
</body>
</html>
