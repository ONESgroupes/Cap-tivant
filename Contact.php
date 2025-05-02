<?php
session_start();
require_once 'config.php';

$success = $error = '';

// ✅ Traitement du formulaire si méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tel = trim($_POST['telephone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$nom || !$email || !$message) {
        $error = "Merci de remplir tous les champs obligatoires.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$nom, $email, $tel, $message]);
        $success = "Votre message a bien été envoyé.";
    }
}

session_start();
$estConnecte = isset($_SESSION['user_id']);


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Contact</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="Contact.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <script src="info-bateau.js" defer></script>
</head>
<body>
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
    <a id="lien-compte" class="lien-langue" data-page="Connexion" style="color: #577550;">Mon Compte</a>
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
        <h1 class="page-title" id="titre-page">Nous contacter</h1>
        <p class="texte" id="contact-texte" style="text-shadow: none;">
            Pour toute question, veuillez nous contacter par mail à captivant@gmail.com ou via le formulaire de contact ci-dessous.
        </p>
    </div>

    <?php if ($success): ?>
        <p style="color:green; text-align:center;"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p style="color:red; text-align:center;"><?= $error ?></p>
    <?php endif; ?>

    <form class="formulaire-connexion" method="POST" action="Contact.php">
        <div class="champ-double">
            <input type="text" name="nom" id="nom" placeholder="Nom" required>
            <input type="text" name="email" id="mail" placeholder="E-mail" required>
        </div>
        <div class="champ">
            <input type="text" name="telephone" id="tel" placeholder="Entrez votre téléphone">
            <br>
            <label for="msg" id="label-msg">Message</label>
        </div>
        <div class="champ-msg">
            <textarea id="msg" name="message" placeholder="Entrez votre message" required></textarea>
        </div>
        <div class="connexion" style="text-align: center;">
            <button type="submit" id="btn-envoyer" class="connexion">Envoyer</button>
        </div>
    </form>
</div>

<div class="bouton-bas">
    <a id="lien-mentions" class="bottom-text lien-langue" data-page="MentionsLegales" style="color: #577550">Mentions légales</a>
    <span class="bottom-text" style="color: #577550">•</span>
    <a id="lien-contact" class="bottom-text lien-langue" data-page="Contact" style="color: #577550">Contact</a>
</div>
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
        const texte = langue === "en" ? ContactEN : ContactFR;
        const commun = langue === "en" ? CommunEN : CommunFR;

        document.title = texte.titre;
        document.getElementById("page-title").textContent = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        document.getElementById("contact-texte").textContent = texte.texte;
        document.getElementById("nom").placeholder = texte.nom;
        document.getElementById("mail").placeholder = texte.email;
        document.getElementById("tel").placeholder = texte.telephone;
        document.getElementById("label-msg").textContent = texte.label;
        document.getElementById("msg").placeholder = texte.msg;
        document.getElementById("btn-envoyer").textContent = texte.bouton;

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
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a class="lien-langue" data-page="${liens[index]}">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';

        document.querySelectorAll(".lien-langue").forEach(lien => {
            const page = lien.getAttribute("data-page");
            lien.setAttribute("href", `${page}.php`);
        });

        const lienApropos = document.getElementById("lien-apropos");
        if (lienApropos) {
            lienApropos.textContent = commun.info;
            lienApropos.setAttribute("href", "a-propos.php");
        }
    });
</script>
</body>
</html>
