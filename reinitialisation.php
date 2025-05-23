<?php
// Affiche les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php'; // S'assurer que ce fichier existe et fonctionne

$token = $_GET['token'] ?? '';
$tokenPath = __DIR__ . "/tokens/$token.txt";
$message = '';
$email = '';

// Si le lien est cliqué (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!file_exists($tokenPath)) {
        $message = "❌ Lien invalide ou expiré.";
    } else {
        $email = trim(file_get_contents($tokenPath));
    }
}

// Si le formulaire est soumis (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $tokenPath = __DIR__ . "/tokens/$token.txt";
    $nouveau_mdp = $_POST['new_password'] ?? '';

    if (!file_exists($tokenPath)) {
        $message = "❌ Lien invalide ou expiré.";
    } elseif (empty($nouveau_mdp)) {
        $message = "❌ Le mot de passe ne peut pas être vide.";
    } elseif (strlen($nouveau_mdp) < 12 || !preg_match('/[A-Z]/', $nouveau_mdp) || !preg_match('/[a-z]/', $nouveau_mdp)) {
        $message = "❌ Le mot de passe doit contenir au moins 12 caractères, une majuscule et une minuscule.";
    } else {
        $email = trim(file_get_contents($tokenPath));
        $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$hash, $email]);

        unlink($tokenPath);
        $message = "✅ Mot de passe mis à jour avec succès. Redirection...";
        $redirect = true;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="mdp-oublier.css">
    <link rel="stylesheet" href="nav-barre.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
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
    <a href="a-propos.php" id="lien-apropos" style="color: #e0e0d5; text-decoration: none;"></a>
    <a href="favoris.php">
        <img src="images/panier.png" alt="Panier">
    </a>
</div>
<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo.png" alt="Logo" >
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre">Réinitialiser votre mot de passe</h1>
    </div>

    <div class="formulaire-connexion" id="confirmation">
        <?php if (!empty($message)): ?>
            <p style="text-align:center; margin-bottom:20px; color:<?= strpos($message, '✅') === 0 ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <?php if (empty($message) || strpos($message, '✅') !== 0): ?>
        <?php if (!empty($email)): ?>
            <p style="text-align:center; margin-bottom:20px;">Réinitialisation pour : <strong><?= htmlspecialchars($email) ?></strong></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="champ">
                <div class="input-container">
                    <input style="margin-left: 30px" type="password" id="nv" name="new_password" placeholder="Nouveau mot de passe" required>
                    <img src="images/eye-closed.png" alt="Afficher mot de passe" class="eye-icon" onclick="togglePasswordVisibility('nv')">
                </div>
            </div>
            <div class="logo-block">
                <div class="connexion">
                    <button type="submit" id="valider" style="color: white; background: none; border: none; font-size: 17px; font-family: 'DM Serif Display', cursive; cursor: pointer>Valider</button>
                            </div>
                            </div>
                            </form>
                    <?php else: ?>
                            <div style="text-align:center; margin-top:20px;">
                    <a href="Connexion.php" class="btn" style="color:white; background-color:#577550; padding:10px 20px; border-radius:10px; text-decoration:none;">Retour à la connexion</a>
                </div>
                <?php endif; ?>
            </div>
    </div>

    <div class="bouton-bas">
        <a href="MentionsLegales.php" class="bottom-text" id="lien-mentions" style="color: #577550">Mentions légales</a>
        <span class="bottom-text" style="color: #577550">&bull;</span>
        <a href="Contact.php" class="bottom-text" id="lien-contact" style="color: #577550">Contact</a>
    </div>

    <?php if (!empty($redirect)): ?>
        <script>
            setTimeout(function () {
                window.location.href = 'Connexion.php';
            }, 3000); // Redirection après 3 secondes
        </script>
    <?php endif; ?>
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

        window.onload = function () {
            const langue = localStorage.getItem("langue") || "fr";
            const texte = langue === "en" ? ReinitialisationEN : ReinitialisationFR;
            const texteCommun = langue === "en" ? CommunEN : CommunFR;

            document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
            document.getElementById("titre").textContent = texte.titre;

            document.getElementById("nv").placeholder = texte.nv;
            document.getElementById("valider").textContent = texte.valider;
            document.getElementById("lien-apropos").textContent = texteCommun.info;
            document.getElementById("lien-mentions").textContent = texteCommun.mentions;
            document.getElementById("lien-contact").textContent = texteCommun.contact;

            const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
            const menuContent = document.getElementById("menu-links");
            menuContent.innerHTML = texteCommun.menu.map((item, index) => {
                return `<a href="${liens[index]}.php">${item}</a>`;
            }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';

            const dropdown = document.getElementById("lang-dropdown");
            dropdown.innerHTML = langue === "en"
                ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
                : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;
        };

        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("lang-dropdown");
            const icon = document.getElementById("current-lang");
            if (!dropdown.contains(event.target) && !icon.contains(event.target)) {
                dropdown.style.display = "none";
            }
        });
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
    <script src="info-bateau.js"></script>
</body>
</html>
