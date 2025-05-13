<?php
session_start();
$estConnecte = isset($_SESSION['user_id']);


if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php");
    exit;
}


require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Connexion.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// 🎯 Mise à jour des données utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    $postal = trim($_POST['code_postal']);
    $ville = trim($_POST['ville']);
    $pays = trim($_POST['pays']);
    $mdp = $_POST['mdp'];
    $telephone = $_POST['telephone'];


    if (!$nom || !$prenom || !$email) {
        $error = "Nom, prénom et email sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET last_name=?, first_name=?, email=?, address=?, postal_code=?, city=?, country=? WHERE id=?");
        $stmt->execute([$nom, $prenom, $email, $adresse, $postal, $ville, $pays,$telephone, $user_id]);

        if (!empty($mdp)) {
            $hashed = password_hash($mdp, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hashed, $user_id]);
        }

        $_SESSION['first_name'] = $prenom;
        $success = "Informations mises à jour.";
    }
}

// 🔄 Récupération des données pour affichage
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$nom = htmlspecialchars($user['last_name']);
$prenom = htmlspecialchars($user['first_name']);
$email = htmlspecialchars($user['email']);
$adresse = htmlspecialchars($user['address']);
$postal = htmlspecialchars($user['postal_code']);
$ville = htmlspecialchars($user['city']);
$pays = htmlspecialchars($user['country']);
$telephone = htmlspecialchars($user['telephone']);



$estConnecte = isset($_SESSION['user_id']);


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title id="page-title">Mon Compte</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="MonCompte.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <script src="info-bateau.js" defer></script>
</head>
<body>
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu-vert.png" alt="Menu">
</div>

<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content">
        <a href="location.php">LOCATION</a>
        <a href="ports.php">NOS PORTS</a>
        <a href="MonCompte.php">MON COMPTE</a>
        <a href="historique.php">HISTORIQUE</a>
        <a href="faq.php">FAQ</a>
        <a href="Avis.php">AVIS</a>
        <span onclick="toggleMenu()" class="close-menu">✕</span>
    </div>
</div>

<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a id="a-propos-link" href="a-propos.php" style="color: #577550; text-decoration: none;">À propos</a>
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
        <h1 class="page-title" id="titre-page">Mon Compte</h1>
    </div>

    <?php if ($success): ?>
        <p style="color:green; text-align:center;"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p style="color:red; text-align:center;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" action="MonCompte.php" class="formulaire-connexion">
        <div class="champ-msg" style="margin-top: 40px;">
            <label id="label-infos" for="nom">Mes informations</label>
            <label id="label-adresse" for="adresse">Mon adresse</label>
        </div>
        <div class="champ-double">
            <input type="text" name="nom" id="nom" value="<?= $nom ?>" placeholder="Nom" required>
            <input type="text" name="code_postal" id="code-postal" value="<?= $postal ?>" placeholder="Code postal">
        </div>
        <div class="champ-double">
            <input type="text" name="prenom" id="prenom" value="<?= $prenom ?>" placeholder="Prénom" required>
            <input type="text" name="ville" id="ville" value="<?= $ville ?>" placeholder="Ville">
        </div>
        <div class="champ-double">
            <input type="email" name="email" id="mail" value="<?= $email ?>" placeholder="E-mail" required>
            <input type="text" name="pays" id="pays" value="<?= $pays ?>" placeholder="Pays">
        </div>
        <div class="champ-double">
            <input type="telephone" name="telephone" id="telephone" value="<?= $telephone ?>" placeholder="Numéro de téléphone" >
            <input type="text" style="visibility: hidden;" disabled>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <button type="submit" class="connexion" id="btn-modifier">Modifier</button>
        </div>
    </form>
    <?php if ($estConnecte): ?>
        <div style="text-align: center; margin-top: 20px;">
            <form method="post" action="deconnexion.php">
                <strong><button type="submit" style="color: #ee9c72; font-family: 'DM Serif Display', cursive; cursor: pointer; background: none; border: none; outline: none; font-size: 15px;">Se déconnecter</button></strong>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="bouton-bas">
    <a id="lien-mentions" href="MentionsLegales.php" class="bottom-text" style=" color: #577550">Mentions légales</a>
    <span class="bottom-text" style=" color: #577550">•</span>
    <a id="lien-contact" href="Contact.php" class="bottom-text" style=" color: #577550">Contact</a>
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
        const texte = langue === "en" ? CompteEN : CompteFR;
        const commun = langue === "en" ? CommunEN : CommunFR;

        document.getElementById("page-title").textContent = texte.titre;
        document.getElementById("titre-page").textContent = texte.titre;
        document.getElementById("label-infos").textContent = texte.labelInfos;
        document.getElementById("label-adresse").textContent = texte.labelAdresse;
        document.getElementById("nom").placeholder = texte.nom;
        document.getElementById("adresse").placeholder = texte.adresse;
        document.getElementById("prenom").placeholder = texte.prenom;
        document.getElementById("code-postal").placeholder = texte.codePostal;
        document.getElementById("mail").placeholder = texte.email;
        document.getElementById("ville").placeholder = texte.ville;
        document.getElementById("telephone").placeholder = texte.telephone;
        document.getElementById("pays").placeholder = texte.pays;
        document.getElementById("btn-modifier").textContent = texte.bouton;

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
        }).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';
    });
    function togglePassword() {
        const input = document.getElementById("mdp");
        input.type = input.type === "password" ? "text" : "password";
    }

</script>
</body>
</html>
