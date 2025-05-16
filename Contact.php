<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
$estConnecte = isset($_SESSION['user_id']);
$success = $error = '';

$user = null;
if ($estConnecte) {
    $stmt = $pdo->prepare("SELECT last_name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    if ($estConnecte && $user) {
        $nom = $user['last_name'];
        $email = $user['email'];
        $tel = $user['phone'];
    }
    else {
        $nom = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tel = trim($_POST['phone'] ?? '');
    }


    if (!$nom || !$email || !$message) {
        if (!$nom || !$email ) {
            $error = "Nom et email sont obligatoires";
        }if (!message) {
            $error = "Le message est vide";
        }
    } else {
        // Sauvegarde en base
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$nom, $email, $tel, $message]);

        // Envoi de l'email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'paule.rochette2004@gmail.com';
            $mail->Password = 'vnwumyeurcexvkwh';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom($email, $nom);
            $mail->addReplyTo($email, $nom);

            $mail->addAddress('paule.rochette2004@gmail.com', 'Cap-Tivant');

            $mail->isHTML(true);
            $mail->Subject = '📩 Nouveau message depuis le formulaire de contact';
            $mail->Body = "
                <strong>Nom :</strong> $nom<br>
                <strong>Email :</strong> $email<br>
                <strong>Téléphone :</strong> $tel<br><br>
                <strong>Message :</strong><br>" . nl2br(htmlspecialchars($message));

            $mail->send();
            $success = "Votre message a bien été envoyé.";
        } catch (Exception $e) {
            $error = "Erreur lors de l'envoi du mail : " . $mail->ErrorInfo;
        }
    }
}




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
    <script>
        const estConnecte = <?= json_encode($estConnecte) ?>;
    </script>



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
    <a id="lien-apropos" class="lien-langue" data-page="a-propos" style="color: #577550; text-decoration: none;">À propos</a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos" style="color: #577550;">Mon Compte</a>
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
        <?php if (!empty($success)): ?>
            <p style="color: green; text-align: center; font-weight: bold;"><?= htmlspecialchars($success) ?></p>
        <?php elseif (!empty($error)): ?>
            <p style="color: red; text-align: center; font-weight: bold;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <p class="texte" id="contact-texte" style="text-shadow: none;">
            Pour toute question, veuillez nous contacter par mail à captivant@gmail.com ou via le formulaire de contact ci-dessous.
        </p>
    </div>
    <?php if ($estConnecte): ?>
        <form method="POST" action="Contact.php" class="formulaire-connexion">
            <div class="champ-msg">
                <label for="msg" id="label-msg">Message</label>
            </div>
            <div class="champ-msg">
                <textarea name="message" id="msg" placeholder="Entrez votre message" required></textarea>
            </div>
            <button type="submit" id="btn-envoyer" class="connexion">Envoyer</button>
        </form>

    <?php else: ?>


    <form method="POST" action="Contact.php" class="formulaire-connexion">
        <div class="champ-double">
            <input type="text" name="name" id="nom" placeholder="Nom" required>
            <input type="email" name="email" id="mail" placeholder="E-mail" required>
        </div>
        <div class="champ">
            <input type="text" name="phone" id="tel" placeholder="Entrez votre téléphone">
            <br>
            <label for="msg" id="label-msg">Message</label>
        </div>
        <div class="champ-msg">
            <textarea name="message" id="msg" placeholder="Entrez votre message" required></textarea>
        </div>
        <button type="submit" id="btn-envoyer" class="connexion">Envoyer</button>
    </form>
    <?php endif; ?>
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

        const compteLink = document.getElementById("compte-link");
        if (compteLink) {
            compteLink.textContent = commun.compte;
        }

        document.title = texte.titre;
        document.getElementById("page-title").textContent = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        document.getElementById("contact-texte").textContent = texte.texte;
        if (estConnecte) {
            $nom = trim($_POST['last_name'] ?? '');
            $mail = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['phone'] ?? '');
        } else {
            document.getElementById("nom").placeholder = texte.nom;
            document.getElementById("mail").placeholder = texte.email;
            document.getElementById("tel").placeholder = texte.telephone;
        }


        document.getElementById("label-msg").textContent = texte.label;
        document.getElementById("msg").placeholder = texte.msg;
        document.getElementById("btn-envoyer").textContent = texte.bouton;

        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-mentions").setAttribute("data-page", "MentionsLegales");
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("lien-contact").setAttribute("data-page", "Contact");

        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        document.getElementById("lang-dropdown").innerHTML = langue === "en"
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