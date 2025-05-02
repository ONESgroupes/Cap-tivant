<?php
// Connexion à la base de données
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DB_HOST', 'localhost');
define('DB_NAME', 'bdd_app');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;  // Récupérer l'ID du bateau depuis l'URL
$bateau = null;

if ($id > 0) {
    // On récupère les informations du bateau
    $stmt = $pdo->prepare("SELECT * FROM bateaux WHERE id = ?");
    $stmt->execute([$id]);
    $bateau = $stmt->fetch();
    if (!$bateau) {
        die("Bateau introuvable.");
    }
} else {
    die("ID manquant.");
}

session_start();
$estConnecte = isset($_SESSION['user_id']);
$user_id = $estConnecte ? $_SESSION['user_id'] : null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title id="page-title"></title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="payement.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <script src="info-bateau.js"></script>
</head>
<body>
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu.png" alt="Menu">
</div>

<div class="top-left retour-offre">
    <a id="lien-retour-offre">Offre &gt;</a>
    <a id="lien-info-bateau"><label id="titre-bateau-label" style="cursor: pointer;"></label><span> &gt;</span></a>
    <label style="font-size: 0.85em; color: rgba(224,224,213,0.65); font-family: 'DM Serif Display', serif;">Reservation</label>
</div>

<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo.png" alt="Logo">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-bateau" style="color: #E0E0D5">Paiement</h1>
    </div>
</div>
<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content">
        <a href="location.php">LOCATION</a>
        <a href="ports.php">NOS PORTS</a>
        <a href="MonCompte.php">MON COMPTE</a>
        <a href="historique.php">HISTORIQUE</a>
        <a href="faq.php">FAQ</a>
        <a href="avis.php">AVIS</a>
        <span onclick="toggleMenu()" class="close-menu">✕</span>
    </div>
</div>
<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown">
            <img id="fr-option" src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">
            <img id="en-option" src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">
        </div>
    </div>
    <a href="a-propos.php" style="color: #E0E0D5; text-decoration: none;">À propos</a>
    <a href="Connexion.php" style="color: #E0E0D5; text-decoration: none;">Mon Compte</a>
    <a href="favoris.php">
        <img src="images/panier.png" alt="panier">
    </a>
</div>
<div class="background">
    <div class="conteneur-info">
        <div class="carte-bateau" id="carte-bateau">
            <h2 id="titre-fiche"><?php echo htmlspecialchars($bateau['titre']); ?></h2>
            <p><strong>Port :</strong> <span id="port"><?php echo htmlspecialchars($bateau['port']); ?></span></p>
            <p><strong>Nombre de personnes maximum :</strong> <span id="personnes"><?php echo htmlspecialchars($bateau['personnes']); ?></span></p>
            <p><strong>Cabines :</strong> <span id="cabines"><?php echo htmlspecialchars($bateau['cabines']); ?></span></p>
            <p><strong>Longueur :</strong> <span id="longueur"><?php echo htmlspecialchars($bateau['longueur']); ?></span></p>
            <p><strong>Prix :</strong> <span id="prix"><?php echo htmlspecialchars($bateau['prix']); ?></span></p>
            <p><strong>Avis :</strong> <span id="avis"></span></p>
        </div>

        <div>
            <div class="select-personnes-container">
                <label for="select-personnes"><strong>Sélectionnez le nombre de personnes :</strong></label>
                <select id="select-personnes"></select>
            </div>
            <div class="enfant-a-bord">
                <input type="checkbox" id="enfant-checkbox" />
                <label for="enfant-checkbox">Enfant à bord</label>
            </div>
            <div class="options-bateau">
                <h3>Options supplémentaires</h3>
                <br>
                <label><input type="checkbox" class="option-checkbox" data-prix="200"> Skipper (+200€)</label><br>
                <label><input type="checkbox" class="option-checkbox" data-prix="80"> Nettoyage final (+80€)</label><br>
                <label><input type="checkbox" class="option-checkbox" data-prix="50"> Literie et serviettes (+50€)</label>
            </div>
        </div>
    </div>
</div>
<div class="total-et-paiement" id="zone-paiement">
    <br>
    <p><strong>Total à payer :</strong> <span id="total-prix">0€</span></p>
    <button id="btn-payer">Payer</button>
</div>
<div class="bouton-bas" style="background: transparent">
    <a href="MentionsLegales.php" class="bottom-text">Mentions légales</a>
    <span class="bottom-text">•</span>
    <a href="Contact.php" class="bottom-text">Contact</a>
</div>
<script src="info-bateau.js"></script>
<script>
    function toggleMenu() {
        const menu = document.getElementById("menu-overlay");
        menu.classList.toggle("active");
    }

    const avisList = JSON.parse(localStorage.getItem("avis") || "[]");

    function calculerMoyenneAvis(titre) {
        const avisBateau = avisList.filter(a => a.titre === titre && !isNaN(a.etoiles));
        const nbAvis = avisBateau.length;
        if (nbAvis === 0) return { moyenne: 0, total: 0 };
        const somme = avisBateau.reduce((acc, cur) => acc + Number(cur.etoiles), 0);
        const moyenne = (somme / nbAvis).toFixed(1);
        return { moyenne, total: nbAvis };
    }

    function genererAvisTexte(note, totalAvis) {
        if (totalAvis === 0) return `⭐ - (0 avis)`;
        return `⭐ ${parseFloat(note).toFixed(1)}/5 (${totalAvis} avis)`;
    }

    const params = new URLSearchParams(window.location.search);
    const id = parseInt(params.get("id"));
    const categorie = params.get("categorie");
    const lienRetour = document.getElementById("lien-retour-offre");
    lienRetour.href = categorie ? `offre.php?categorie=${categorie}` : "offre.php";

    const langue = localStorage.getItem("langue") || "fr";
    const dataBateaux = langue === "en" ? bateauxEN : bateaux;
    const texte = langue === "en" ? PaiementEN : PaiementFR;
    const texteCommun = langue === "en" ? CommunEN : CommunFR;

    // Mise à jour des textes communs
    document.querySelector('a[href="a-propos.php"]').textContent = texteCommun.info;
    document.querySelector('a[href="Connexion.php"]').textContent = texteCommun.compte;
    document.querySelector('a[href="MentionsLegales.php"]').textContent = texteCommun.mentions;
    document.querySelector('a[href="Contact.php"]').textContent = texteCommun.contact;

    // Mise à jour du menu gauche si tu l'as
    const menuItems = document.querySelectorAll('#menu-overlay .menu-content a');
    menuItems.forEach((link, index) => {
        if (texteCommun.menu[index]) {
            link.textContent = texteCommun.menu[index];
        }
    });

    localStorage.setItem("langue", langue);

    document.title = texte.titre;
    document.getElementById("titre-bateau").textContent = texte.titre;
    document.querySelector(".retour-offre a").textContent = texte.offre + " >";
    document.querySelector(".retour-offre label").textContent = texte.reservation;


    if (typeof dataBateaux !== "undefined") {
        const bateau = dataBateaux.find(b => b.id === id);
        if (bateau) {
            const { moyenne, total } = calculerMoyenneAvis(bateau.titre);
            document.getElementById("titre-bateau-label").textContent = bateau.titre;
            document.getElementById("titre-fiche").textContent = bateau.titre;
            document.getElementById("port").textContent = bateau.port;
            document.getElementById("personnes").textContent = bateau.personnes;
            document.getElementById("cabines").textContent = bateau.cabines;
            document.getElementById("longueur").textContent = bateau.longueur;
            document.getElementById("prix").textContent = bateau.prix;
            document.getElementById("avis").textContent = genererAvisTexte(moyenne, total);
            document.getElementById("lien-info-bateau").href = `info-bateau.php?id=${bateau.id}`;


            const chiffres = bateau.prix.match(/\d+/g);
            const prixBase = chiffres ? parseInt(chiffres.join("")) : 0;
            const totalPrixElem = document.getElementById("total-prix");
            totalPrixElem.textContent = prixBase + "€";

            const options = document.querySelectorAll('.option-checkbox');
            options.forEach(option => {
                option.addEventListener('change', () => {
                    let total = prixBase;
                    options.forEach(opt => {
                        if (opt.checked) {
                            total += parseInt(opt.getAttribute("data-prix"));
                        }
                    });
                    totalPrixElem.textContent = total + "€";
                });
            });

            const nbPersonnesMax = parseInt(bateau.personnes.match(/\d+/));
            const select = document.getElementById("select-personnes");
            select.innerHTML = "";
            const defaultOption = document.createElement("option");
            defaultOption.disabled = true;
            defaultOption.selected = true;
            defaultOption.textContent = texte.choisirNombre;
            select.appendChild(defaultOption);
            for (let i = 1; i <= nbPersonnesMax; i++) {
                const option = document.createElement("option");
                option.value = i;
                option.textContent = i + (i === 1 ? " personne" : " personnes");
                select.appendChild(option);
            }

            document.querySelector('label[for="select-personnes"]').innerHTML = `<strong>${texte.selectLabel}</strong>`;
            document.querySelector('label[for="enfant-checkbox"]').textContent = texte.enfantLabel;
            document.querySelector('.options-bateau h3').textContent = texte.optionsTitre;

            const optionLabels = document.querySelectorAll('.option-checkbox');
            optionLabels[0].nextSibling.textContent = " " + texte.skipper;
            optionLabels[1].nextSibling.textContent = " " + texte.nettoyage;
            optionLabels[2].nextSibling.textContent = " " + texte.literie;

            document.querySelector('.total-et-paiement p strong').textContent = texte.total;
            document.getElementById("btn-payer").textContent = texte.payer;

            document.getElementById("btn-payer").addEventListener("click", () => {
                payer(id);
            });
        }
    }

    function payer(id) {
        const estConnecte = <?php echo json_encode($estConnecte); ?>; // Récupère la valeur de PHP pour savoir si l'utilisateur est connecté

        if (!estConnecte) {
            alert("Vous devez être connecté pour effectuer une réservation.");
            window.location.href = "Connexion.php"; // Redirige vers la page de connexion si non connecté
            return; // Empêche l'exécution du reste du code si non connecté
        }

        const data = langue === "en" ? bateauxEN : bateaux;
        const bateau = data.find(b => b.id === id);
        if (!bateau) return;

        let historique = JSON.parse(localStorage.getItem("historique") || "[]");

        // Vérifier si l'offre est déjà dans l'historique
        if (!historique.some(h => h.id === bateau.id)) {
            historique.push(bateau);  // Ajouter le bateau à l'historique
            localStorage.setItem("historique", JSON.stringify(historique));  // Enregistrer l'historique
            alert("Merci pour votre réservation !");
            window.location.href = "historique.php";  // Rediriger vers l'historique
        } else {
            alert("Ce bateau est déjà dans votre historique.");
        }
    }

</script>
<script>
    function toggleLangDropdown() {
        const dropdown = document.getElementById("lang-dropdown");
        dropdown.innerHTML = "";

        const currentLang = localStorage.getItem("langue") || "fr";
        const newLang = currentLang === "fr" ? "en" : "fr";
        const newFlag = document.createElement("img");
        newFlag.src = newLang === "fr" ? "images/drapeau-francais.png" : "images/drapeau-anglais.png";
        newFlag.alt = newLang === "fr" ? "Français" : "Anglais";
        newFlag.classList.add("drapeau-option");
        newFlag.onclick = () => changerLangue(newLang);

        dropdown.appendChild(newFlag);
        dropdown.style.display = "block";
    }

    function changerLangue(langue) {
        localStorage.setItem("langue", langue);
        location.reload();
    }

    window.onload = function () {
        const currentLang = document.getElementById("current-lang");
        const langue = localStorage.getItem("langue") || "fr";
        currentLang.src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
    };

    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("lang-dropdown");
        const icon = document.getElementById("current-lang");
        if (!dropdown.contains(event.target) && !icon.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
</script>
</body>
</html>
