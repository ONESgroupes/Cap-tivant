<?php
session_start();
$estConnecte = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Réinitialiser votre mot de passe</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="mdp-oublier.css">
    <link rel="stylesheet" href="nav-barre.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <script src="info-bateau.js"></script>
</head>
<body>
<div class="navbar-barre"></div>
<?php
if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['email'])) {
    $email = htmlspecialchars($_GET['email']);
    echo "<div style='display: flex; flex-direction: column; align-items: center; justify-content: center; margin-top: 150px'>";
    echo "<p id='lien' style='color: rgb(87,117,80); font-weight: bold; font-size: 1.3em; margin-bottom: 10px;'></p>";
    echo "<p style='font-size: 1.1em;'>$email</p>";
    echo "</div>";
}
?>

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
    <a id="a-propos-link" href="a-propos.php" style="color: #e0e0d5; text-decoration: none;">À propos</a>
    <a href="favoris.php">
        <img src="images/panier.png" alt="Panier">
    </a>
</div>

<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo.png" alt="Logo">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-page"></h1>
    </div>

    <form action="traitement-mdp.php" method="POST" class="formulaire-connexion">
        <div class="champ">
            <img src="images/email.png" alt="email">
            <input type="email" id="email" name="email" placeholder="E-mail lié au compte" required>
        </div>
        <div class="logo-block">
            <div class="connexion">
                <button type="submit" style="color: white; text-decoration: none; background:none; border:none; font-size: 17px; font-family: 'DM Serif Display'; cursor: pointer" id="bouton">
                    Envoyer un lien
                </button>
            </div>
            <div class="retour" style="margin-top: 10px;">
                <a href="Connexion.php" style="color: #ee9c72; text-decoration: none; font-size: 17px;" id="retour">Retour à la connexion</a>
            </div>
        </div>

    </form>
</div>

<div class="bouton-bas">
    <a id="lien-mentions" href="MentionsLegales.php" class="bottom-text" style="color: #577550">Mentions légales</a>
    <span class="bottom-text" style="color: #577550">&bull;</span>
    <a id="lien-contact" href="Contact.php" class="bottom-text" style="color: #577550">Contact</a>
</div>

<script>
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
        const langue = localStorage.getItem("langue") || "fr";
        const texte = langue === "en" ? MdpEN : MdpFR;
        const commun = langue === "en" ? CommunEN : CommunFR;

        document.getElementById("page-title").textContent = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        document.getElementById("email").placeholder = texte.placeholder;
        document.getElementById("bouton").textContent = texte.bouton;
        document.getElementById("retour").textContent = texte.retour;
        document.getElementById("lien").textContent = texte.lien;

        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("a-propos-link").textContent = commun.info;

        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        document.getElementById("lang-dropdown").innerHTML = langue === "en"
            ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;

        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        const menuContent = document.getElementById("menu-links");
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a href="${liens[index]}.php">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">\u2715</span>';
    });
</script>
</body>
</html>
