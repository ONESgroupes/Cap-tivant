<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT id, first_name, password_hash, admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['is_admin'] = $user['admin']; // ← enregistre le rôle

            if ($user['admin']) {
                header('Location: admin.php');
            } else {
                header('Location: MonCompte.php');
            }
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Connexion</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="Connexion.css">
    <link rel="stylesheet" href="nav-barre.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
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
            const commun = langue === "en" ? CommunEN : CommunFR;
            const texte = langue === "en" ? ConnexionEN : ConnexionFR;

            document.title = texte.titre;
            document.getElementById("page-title").textContent = texte.titre;
            document.getElementById("titre-page").textContent = texte.titre;
            document.getElementById("email").placeholder = texte.email;
            document.getElementById("password").placeholder = texte.mdp;
            document.getElementById("btn-connexion").textContent = texte.bouton;
            document.getElementById("inscription-lien").textContent = texte.inscription;
            document.getElementById("mdp-lien").textContent = texte.mdpOublie;

            document.getElementById("lien-mentions").textContent = commun.mentions;
            document.getElementById("lien-contact").textContent = commun.contact;

            const currentLang = document.getElementById("current-lang");
            currentLang.src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";

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

            const lienApropos = document.getElementById("lien-apropos");
            lienApropos.textContent = commun.info;
            lienApropos.setAttribute("href", "a-propos.php");
        });
    </script>
</head>
<body>
<div class="navbar-barre"></div>
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
    <a id="lien-apropos" class="lien-langue" data-page="a-propos" style="color: #e0e0d5; text-decoration: none;">À propos</a>
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
        <h1 class="page-title" id="titre-page">Connexion</h1>
    </div>

    <form method="POST" action="Connexion.php" class="formulaire-connexion">
        <div class="champ">
            <img src="images/email.png" alt="email">
            <input type="email" id="email" name="email" placeholder="Entrez votre adresse mail" required>
        </div>
        <div class="champ">
            <img src="images/mdp.png" alt="mot de passe">
            <div class="input-container">
                <input  style="margin-left: 30px" type="password" id="password" name="password" placeholder="Enter your password" required>
                <img src="images/eye-closed.png" alt="Afficher mot de passe" class="eye-icon" onclick="togglePasswordVisibility('password')">
            </div>
        </div>
        <button type="submit" class="connexion" id="btn-connexion">Se connecter</button>
    </form>

    <?php if ($error): ?>
        <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="logo-block">
        <a id="inscription-lien" href="Inscription.php" class="inscription" style="text-decoration: none; font-size: 1.2em">Inscription</a>
        <a id="mdp-lien" href="mdp-oublier.php" class="mdp-oublier" style="text-decoration: none; font-size: 1.2em">Mot de passe oublié</a>
    </div>
</div>

<div class="bouton-bas">
    <a id="lien-mentions" class="bottom-text lien-langue" data-page="MentionsLegales" style="color: #577550">Mentions légales</a>
    <span class="bottom-text" style="color: #577550">•</span>
    <a id="lien-contact" class="bottom-text lien-langue" data-page="Contact" style="color: #577550">Contact</a>
</div>
<script>
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.querySelector(`[onclick="togglePasswordVisibility('${inputId}')"]`);

        if (input.type === "password") {
            input.type = "text";
            icon.src = "images/eye-open.png";
            icon.alt = "Masquer mot de passe";
        } else {
            input.type = "password";
            icon.src = "images/eye-closed.png";
            icon.alt = "Afficher mot de passe";
        }
    }
</script>

</body>
</html>