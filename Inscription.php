<?php
require_once 'config.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mdp'] ?? '';
    $mdp_confirm = $_POST['mdp_confirm'] ?? '';
    $mentions = isset($_POST['mentions']);

    if (!$nom || !$prenom || !$email || !$mdp || !$mdp_confirm || !$mentions) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($mdp !== $mdp_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "Cet e-mail est déjà utilisé.";
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
            $insert->execute([$prenom, $nom, $email, $hash]);
            $success = "✅ Inscription réussie ! <a href='Connexion.php'>Connectez-vous</a>";
        }
    }
}

session_start();
$estConnecte = isset($_SESSION['user_id']);


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Inscription</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="inscription.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <script src="info-bateau.js"></script>
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
    <a id="a-propos-link" href="a-propos.php" style="color: #577550; text-decoration: none;">À propos</a>
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
        <h1 class="page-title" id="titre-page"></h1>
    </div>

    <form method="POST" action="Inscription.php" class="formulaire-inscription">
        <div class="champ">
            <br>
            <input type="text" id="nom" name="nom" placeholder="Nom" required>
            <input type="text" id="prenom" name="prenom" placeholder="Prénom" required>
            <img src="images/email.png" alt="email">
            <input type="email" id="email" name="email" placeholder="Entrez votre adresse mail" required>
            <img src="images/mdp.png" alt="mot de passe">
            <input type="password" id="mdp" name="mdp" placeholder="Entrez votre mot de passe" required>
            <input type="password" id="mdp-confirm" name="mdp_confirm" placeholder="Confirmez votre mot de passe" required>
        </div>

        <div class="conditions-general">
            <label class="checkbox-container">
                <input type="checkbox" id="mentions" name="mentions" required>
                <span class="checkmark"></span>
                <span class="conditions" id="conditions-text">Accepter les conditions d'utilisations</span>
            </label>
        </div>

        <button type="submit" class="inscription" id="btn-inscription">S'inscrire</button>
    </form>

    <?php if ($error): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
        <p style="color: green; text-align: center;"><?= $success ?></p>
    <?php endif; ?>

    <div class="logo-block">
        <a href="Connexion.php" class="connexion" id="lien-connexion">Se connecter</a>
    </div>
</div>

<div class="bouton-bas">
    <a id="lien-mentions" href="MentionsLegales.php" class="bottom-text" style="color: #577550">Mentions légales</a>
    <span class="bottom-text" style="color: #577550">•</span>
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
        const texte = langue === "en" ? InscriptionEN : InscriptionFR;
        const commun = langue === "en" ? CommunEN : CommunFR;

        document.getElementById("page-title").textContent = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        document.getElementById("nom").placeholder = texte.nom;
        document.getElementById("prenom").placeholder = texte.prenom;
        document.getElementById("email").placeholder = texte.email;
        document.getElementById("mdp").placeholder = texte.mdp;
        document.getElementById("mdp-confirm").placeholder = texte.confirmerMdp;
        document.getElementById("conditions-text").textContent = texte.conditions;
        document.getElementById("btn-inscription").textContent = texte.bouton;
        document.getElementById("lien-connexion").textContent = texte.lienConnexion;

        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-mentions").href = "MentionsLegales.php";
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("lien-contact").href = "Contact.php";

        document.getElementById("a-propos-link").textContent = commun.info;
        document.getElementById("mon-compte-link").textContent = commun.compte;

        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        const langDropdown = document.getElementById("lang-dropdown");
        langDropdown.innerHTML = langue === "en"
            ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;

        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        const menuContent = document.getElementById("menu-links");
        menuContent.innerHTML = commun.menu.map((item, index) => {
            return `<a href="${liens[index]}.php">${item}</a>`;
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';
    });
</script>
</body>
</html>
