<?php
global $pdo;
session_start();
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$success = $error = '';
$estConnecte = isset($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mdp'] ?? '';
    $mdp_confirm = $_POST['mdp_confirm'] ?? '';
    $mentions = isset($_POST['mentions']);
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    $recaptchaSecret = '6LfJqjErAAAAABxFJ1B916BkIyvUe2S5CyvNjdbl';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $recaptchaVerified = false;

    if ($recaptchaResponse) {
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
        $responseData = json_decode($verify);
        $recaptchaVerified = $responseData->success;
    }

    if (!$recaptchaVerified) {
        $error = "Veuillez confirmer que vous n'êtes pas un robot.";
    }

    if (!$nom || !$prenom || !$email || !$mdp || !$mdp_confirm || !$mentions) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($mdp !== $mdp_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mdp) < 12 || !preg_match('/[A-Z]/', $mdp) || !preg_match('/[a-z]/', $mdp)) {
        $error = "Le mot de passe doit contenir au moins 12 caractères, une majuscule et une minuscule.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = "Cet e-mail est déjà utilisé.";
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash, newsletter) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$prenom, $nom, $email, $hash, $newsletter]);

            if ($newsletter) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'paule.rochette2004@gmail.com';
                    $mail->Password = 'cgzteciubsyxsxmv';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('paule.rochette2004@gmail.com', 'Cap\'Tivant');
                    $mail->addAddress($email, "$prenom $nom");

                    $mail->isHTML(true);
                    $mail->Subject = 'Bienvenue à la newsletter Cap\'Tivant';
                    $mail->Body = "<p>Bonjour $prenom,</p><p>Merci pour votre inscription à notre newsletter ! Vous recevrez bientôt nos meilleures offres de location ⛵.</p>";

                    $mail->send();
                    error_log("✅ Email envoyé à $email");
                } catch (Exception $e) {
                    error_log("❌ Erreur envoi mail : " . $mail->ErrorInfo);
                    $error = "L'inscription est réussie, mais l'email n'a pas pu être envoyé.";
                }
            }

            // ✅ Redirection uniquement si pas d'erreur
            if (!$error) {
                header("Location: Connexion.php?inscription=ok");
                exit;
            }
        }
    }
}
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
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

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
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos" style="color: #577550">Mon Compte</a>
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
        <h1 class="page-title" id="titre-page">Inscription</h1>
    </div>
</div>

<main class="contenu-scrollable" >
    <form method="POST" action="Inscription.php" class="formulaire-inscription">
        <div class="champ">
            <br>
            <div class="champ-obligatoire">
                <span class="etoile">*</span>
                <input type="text" id="nom" name="nom" placeholder="Last name" required>
            </div>

            <div class="champ-obligatoire">
                <span class="etoile">*</span>
                <input type="text" id="prenom" name="prenom" placeholder="First name" required>
            </div>

            <img src="images/email.png" alt="email">

            <div class="champ-obligatoire">
                <span class="etoile">*</span>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            </div>

            <img src="images/mdp.png" alt="mot de passe">

            <div class="champ-obligatoire">
                <span class="etoile">*</span>
                <div class="password-container">
                    <input type="password" id="mdp" name="mdp" placeholder="Enter your password" required>
                    <img src="images/eye-closed.png" alt="Afficher mot de passe" class="toggle-password" onclick="togglePasswordVisibility('mdp')">
                </div>
            </div>

            <div class="champ-obligatoire">
                <span class="etoile">*</span>
                <div class="password-container">
                    <input type="password" id="mdp-confirm" name="mdp_confirm" placeholder="Confirm your password" required>
                    <img src="images/eye-closed.png" alt="Afficher mot de passe" class="toggle-password" onclick="togglePasswordVisibility('mdp-confirm')">
                </div>
            </div>
        </div>

        <div class="conditions-general">
            <div class="champ-obligatoire">
                <span class="etoile">*</span>
                <label class="checkbox-container">
                    <input type="checkbox" id="mentions" name="mentions" required>
                    <span class="checkmark"></span>
                    <span class="conditions" id="conditions-text">Accepter les conditions d'utilisations</span>
                </label>
            </div>
        </div>
        <div class="conditions-general">
            <label class="checkbox-container">
                <input type="checkbox" id="newsletter" name="newsletter">
                <span class="checkmark"></span>
                <span class="conditions">Je souhaite m'inscrire à la newsletter</span>
            </label>
        </div>
        <div class="conditions-general">
            <div class="champ-obligatoire">
                <span class="etoile">*</span>
                <div class="g-recaptcha" data-sitekey="6LfJqjErAAAAAChIifxO3-ht7cVOz2HVz03aWZSF"></div>
            </div>
        </div>

        <div class="conditions-general">
            <br>
            <button type="submit" class="inscription" id="btn-inscription">S'inscrire</button>
        </div>
    </form>

    <strong>
        <?php if ($error): ?>
            <p style="color: red; text-align: center;"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p style="color: green; text-align: center;"><?= $success ?></p>
        <?php endif; ?>
    </strong>

    <div class="logo-block" style="font-size: 1.2em">
        <a href="Connexion.php" class="connexion" id="lien-connexion">Se connecter</a>
    </div>
    <div class="conditions-general">
        <div class="champ-obligatoire">
            <span class="etoile">*</span>
            <span class="conditions">Ce champ est obligatoire</span>
        </div>
    </div>
</main>

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
        const lienCompte = document.getElementById("compte-link");
        if (lienCompte && commun && commun.compte) {
            lienCompte.textContent = commun.compte;
        }

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