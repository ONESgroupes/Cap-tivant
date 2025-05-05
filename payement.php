<?php
session_start();
$estConnecte = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title id="page-title">Paiement</title> <!-- Titre par défaut -->
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="payement.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <script src="info-bateau.js"></script>
</head>
<script>
    const estConnecte = <?= json_encode($estConnecte) ?>;
</script>

<body>
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu.png" alt="Menu">
</div>

<!-- Fil d’Ariane -->
<div class="top-left retour-offre">
    <a id="lien-retour-offre">Offre ></a>
    <a id="lien-info-bateau"><label id="titre-bateau-label" style="cursor: pointer;"></label><span> ></span></a>
    <label style="font-size: 0.85em; color: rgba(224,224,213,0.65); font-family: 'DM Serif Display', serif;">Reservation</label>
</div>

<!-- Logo et titre -->
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

<!-- Lien langue / compte -->
<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown">
            <!-- Options ajoutées dynamiquement par JS -->
        </div>
    </div>
    <a href="a-propos.php" style="color: #E0E0D5; text-decoration: none;">À propos</a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos">Mon Compte</a>
    <a href="favoris.php">
        <img src="images/panier.png" alt="panier">
    </a>
</div>
<div class="background">
    <div class="conteneur-info">
        <div class="carte-bateau" id="carte-bateau">
            <h2 id="titre-fiche">Chargement...</h2>
            <p><strong>Port :</strong> <span id="port"></span></p>
            <p><strong>Nombre de personnes maximum :</strong> <span id="personnes"></span></p>
            <p><strong>Cabines :</strong> <span id="cabines"></span></p>
            <p><strong>Longueur :</strong> <span id="longueur"></span></p>
            <p><strong>Prix :</strong> <span id="prix"></span></p>
            <p><strong>Avis :</strong> <span id="avis"></span></p>
        </div>

        <div>
            <div class="select-personnes-container">
                <label for="select-personnes"><strong>Sélectionnez le nombre de personnes :</strong></label>
                <select id="select-personnes" name="nombre_personnes"> <!-- Ajout name si besoin côté serveur -->
                    <option disabled selected>Chargement...</option>
                </select>
            </div>

            <div class="enfant-a-bord">
                <!-- Ajout name si besoin côté serveur -->
                <input type="checkbox" id="enfant-checkbox" name="enfant_a_bord" value="1" />
                <label for="enfant-checkbox">Enfant à bord</label>
            </div>

            <div class="options-bateau">
                <h3>Options supplémentaires</h3>
                <br>
                <!-- Ajout name et value si besoin côté serveur -->
                <label><input type="checkbox" name="options[]" value="skipper" class="option-checkbox" data-prix="200"> Skipper (+200€)</label><br>
                <label><input type="checkbox" name="options[]" value="nettoyage" class="option-checkbox" data-prix="80"> Nettoyage final (+80€)</label><br>
                <label><input type="checkbox" name="options[]" value="literie" class="option-checkbox" data-prix="50"> Literie et serviettes (+50€)</label>
            </div>
        </div>
    </div>
    <div id="error-message" style="color: rgb(234,61,61); text-align: center; margin-top: -10px;">
        <strong>
            <?php
            if (isset($_GET['error'])) {
                $error = $_GET['error'];
                if ($error === 'already_reserved') {
                    echo "Vous avez déjà réservé ce bateau.";
                } elseif ($error === 'db_error') {
                    echo "Problème lors de l'enregistrement de la réservation.";
                } elseif ($error === 'not_logged_in') {
                    echo "Vous devez être connecté pour réserver. <a href='Connexion.php'>Se connecter</a>";
                } else {
                    echo "Une erreur inconnue est survenue.";
                }
            }
            ?>
        </strong>
    </div>
</div>

<form action="enregistrer_reservation.php" method="POST" class="total-et-paiement" id="payment-form">
    <br>
    <p><strong>Total à payer :</strong> <span id="total-prix">0€</span></p>
    <!-- Champ caché pour envoyer l'ID du bateau -->
    <input type="hidden" name="id_bateau" id="form_id_bateau" value="">
    <div class="total-et-paiement" id="zone-paiement">
        <button id="btn-payer">Payer</button>
    </div>
</form>


<div class="bouton-bas" style="background: transparent">
    <a href="MentionsLegales.php" class="bottom-text">Mentions légales</a>
    <span class="bottom-text">•</span>
    <a href="Contact.php" class="bottom-text">Contact</a>
</div>

<script>
    // --- Fonctions Utilitaires (toggleMenu, calculerMoyenneAvis, genererAvisTexte) ---
    function toggleMenu() {
        const menu = document.getElementById("menu-overlay");
        menu.classList.toggle("active");
    }

    // S'assurer que avisList est défini, même si localStorage est vide
    let avisList = [];
    try {
        avisList = JSON.parse(localStorage.getItem("avis") || "[]");
    } catch (e) {
        console.error("Erreur lors de la lecture des avis depuis localStorage:", e);
        avisList = []; // Fallback à un tableau vide
    }


    function calculerMoyenneAvis(titre) {
        // Filtrer pour s'assurer que etoiles est un nombre valide
        const avisBateau = avisList.filter(a => a && a.titre === titre && typeof a.etoiles === 'number' && !isNaN(a.etoiles));
        const nbAvis = avisBateau.length;
        if (nbAvis === 0) return { moyenne: 0, total: 0 };
        // Utiliser Number() pour être sûr, même si le filtre devrait suffire
        const somme = avisBateau.reduce((acc, cur) => acc + Number(cur.etoiles), 0);
        const moyenne = (somme / nbAvis); // Garder plus de précision avant toFixed
        return { moyenne: moyenne, total: nbAvis };
    }

    function genererAvisTexte(note, totalAvis) {
        if (totalAvis === 0) return `⭐ - (0 avis)`;
        // Afficher une décimale seulement si nécessaire et différent de .0
        const noteAffichee = (note % 1 === 0) ? note.toFixed(0) : note.toFixed(1);
        return `⭐ ${noteAffichee}/5 (${totalAvis} avis)`;
    }

    // --- Initialisation et Logique Principale ---
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const idParam = params.get("id");
        // Validation de l'ID plus robuste
        const id = (idParam !== null && !isNaN(parseInt(idParam)) && parseInt(idParam) > 0) ? parseInt(idParam) : null;
        const categorie = params.get("categorie"); // Gardé pour le fil d'Ariane

        const lienRetour = document.getElementById("lien-retour-offre");
        if (lienRetour) {
            lienRetour.href = categorie ? `offre.php?categorie=${encodeURIComponent(categorie)}` : "offre.php";
        }


        // Détection et application de la langue
        const langue = localStorage.getItem("langue") || "fr";
        // S'assurer que les variables de données existent (elles viennent de info-bateau.js)
        const dataBateaux = (typeof bateaux !== 'undefined' && langue === 'fr') ? bateaux : (typeof bateauxEN !== 'undefined' && langue === 'en' ? bateauxEN : []);
        const texte = (typeof PaiementFR !== 'undefined' && langue === 'fr') ? PaiementFR : (typeof PaiementEN !== 'undefined' && langue === 'en' ? PaiementEN : {});
        const texteCommun = (typeof CommunFR !== 'undefined' && langue === 'fr') ? CommunFR : (typeof CommunEN !== 'undefined' && langue === 'en' ? CommunEN : {});

        if (texteCommun.info) document.querySelector('a[href="a-propos.php"]').textContent = texteCommun.info;
        if (texteCommun.mentions) document.querySelector('a[href="MentionsLegales.php"]').textContent = texteCommun.mentions;
        if (texteCommun.contact) document.querySelector('a[href="Contact.php"]').textContent = texteCommun.contact;

        const menuItems = document.querySelectorAll('#menu-overlay .menu-content a');
        if (texteCommun.menu && Array.isArray(texteCommun.menu)) {
            menuItems.forEach((link, index) => {
                if (texteCommun.menu[index]) {
                    link.textContent = texteCommun.menu[index];
                }
            });
        }

        // Mise à jour du titre de la page et autres textes spécifiques
        document.title = texte.titre || "Paiement";
        if (document.getElementById("titre-bateau")) document.getElementById("titre-bateau").textContent = texte.titre || "Paiement";
        if (document.querySelector(".retour-offre a")) document.querySelector(".retour-offre a").textContent = (texte.offre || "Offre") + " >";
        if (document.querySelector(".retour-offre label")) document.querySelector(".retour-offre label").textContent = texte.reservation || "Reservation";
        if (document.querySelector('label[for="select-personnes"] strong')) document.querySelector('label[for="select-personnes"] strong').textContent = texte.selectLabel || "Sélectionnez le nombre de personnes :";
        if (document.querySelector('label[for="enfant-checkbox"]')) document.querySelector('label[for="enfant-checkbox"]').textContent = texte.enfantLabel || "Enfant à bord";
        if (document.querySelector('.options-bateau h3')) document.querySelector('.options-bateau h3').textContent = texte.optionsTitre || "Options supplémentaires";
        if (document.querySelector('.total-et-paiement p strong')) document.querySelector('.total-et-paiement p strong').textContent = texte.total || "Total à payer :";
        if (document.getElementById("btn-payer-submit")) document.getElementById("btn-payer-submit").textContent = texte.payer || "Payer";

        // --- Logique spécifique au bateau ---
        if (id === null) {
            console.error("ID de bateau manquant ou invalide dans l'URL.");
            // Afficher un message d'erreur à l'utilisateur ?
            document.getElementById("titre-fiche").textContent = "Erreur: Bateau non trouvé";
            // Désactiver le bouton payer s'il n'y a pas d'ID valide
            const submitButton = document.getElementById('btn-payer-submit');
            if (submitButton) submitButton.disabled = true;
            return; // Arrêter l'exécution si l'ID est invalide
        }

        // Trouver le bateau correspondant à l'ID
        const bateau = dataBateaux.find(b => b && typeof b.id !== 'undefined' && b.id === id);

        if (bateau) {
            // --- Affichage des informations du bateau ---
            const { moyenne, total } = calculerMoyenneAvis(bateau.titre);
            if (document.getElementById("titre-bateau-label")) document.getElementById("titre-bateau-label").textContent = bateau.titre || '';
            if (document.getElementById("titre-fiche")) document.getElementById("titre-fiche").textContent = bateau.titre || '';
            if (document.getElementById("port")) document.getElementById("port").textContent = bateau.port || 'N/A';
            if (document.getElementById("personnes")) document.getElementById("personnes").textContent = bateau.personnes || 'N/A';
            if (document.getElementById("cabines")) document.getElementById("cabines").textContent = bateau.cabines || 'N/A';
            if (document.getElementById("longueur")) document.getElementById("longueur").textContent = bateau.longueur || 'N/A';
            if (document.getElementById("prix")) document.getElementById("prix").textContent = bateau.prix || 'N/A';
            if (document.getElementById("avis")) document.getElementById("avis").textContent = genererAvisTexte(moyenne, total);

            const lienInfoBateau = document.getElementById("lien-info-bateau");
            if(lienInfoBateau) lienInfoBateau.href = `info-bateau.php?id=${bateau.id}`;

            // --- Mise à jour du formulaire caché ---
            const hiddenInputId = document.getElementById('form_id_bateau');
            if (hiddenInputId) {
                hiddenInputId.value = id; // Assigne l'ID récupéré de l'URL
            } else {
                console.error("L'élément input caché 'form_id_bateau' n'a pas été trouvé.");
                // Peut-être désactiver le bouton de soumission si l'ID ne peut être envoyé
                const submitButton = document.getElementById('btn-payer-submit');
                if (submitButton) submitButton.disabled = true;
            }


            // --- Calcul du prix et gestion des options ---
            const chiffres = bateau.prix ? String(bateau.prix).match(/\d+/g) : null; // Convertir en string avant match
            const prixBase = chiffres ? parseInt(chiffres.join(""), 10) : 0; // Spécifier la base 10
            const totalPrixElem = document.getElementById("total-prix");
            if (totalPrixElem) totalPrixElem.textContent = prixBase + "€";

            const optionsCheckboxes = document.querySelectorAll('.option-checkbox');
            function updateTotal() {
                let total = prixBase;
                optionsCheckboxes.forEach(opt => {
                    if (opt.checked) {
                        const prixOption = parseInt(opt.getAttribute("data-prix"), 10);
                        if (!isNaN(prixOption)) {
                            total += prixOption;
                        }
                    }
                });
                if (totalPrixElem) totalPrixElem.textContent = total + "€";
            }

            optionsCheckboxes.forEach(option => {
                option.addEventListener('change', updateTotal);
            });

            // --- Génération du sélecteur de personnes ---
            const nbPersonnesMatch = bateau.personnes ? String(bateau.personnes).match(/\d+/) : null;
            const nbPersonnesMax = nbPersonnesMatch ? parseInt(nbPersonnesMatch[0], 10) : 1; // Default à 1 si non trouvé
            const select = document.getElementById("select-personnes");
            if (select) {
                select.innerHTML = ""; // Vider les options précédentes
                const defaultOption = document.createElement("option");
                defaultOption.disabled = true;
                defaultOption.selected = true;
                defaultOption.textContent = texte.choisirNombre || "Choisir..."; // Utiliser texte traduit
                select.appendChild(defaultOption);
                for (let i = 1; i <= nbPersonnesMax; i++) {
                    const option = document.createElement("option");
                    option.value = i;
                    // Gérer le pluriel dans la traduction si possible, sinon simple
                    option.textContent = i + (i === 1 ? (texte.personneSingulier || " personne") : (texte.personnePluriel || " personnes"));
                    select.appendChild(option);
                }
            }

            // --- Traduction des labels d'options ---
            const optionLabels = document.querySelectorAll('.options-bateau label');
            // Vérifier que les éléments existent avant d'accéder à nextSibling
            if (optionLabels.length > 0 && optionLabels[0].firstChild && texte.skipper) optionLabels[0].firstChild.nextSibling.textContent = " " + texte.skipper;
            if (optionLabels.length > 1 && optionLabels[1].firstChild && texte.nettoyage) optionLabels[1].firstChild.nextSibling.textContent = " " + texte.nettoyage;
            if (optionLabels.length > 2 && optionLabels[2].firstChild && texte.literie) optionLabels[2].firstChild.nextSibling.textContent = " " + texte.literie;

        } else {
            // Gérer le cas où le bateau n'est pas trouvé dans les données
            console.error(`Bateau avec ID ${id} non trouvé dans dataBateaux.`);
            if (document.getElementById("titre-fiche")) document.getElementById("titre-fiche").textContent = "Erreur: Bateau non trouvé";
            // Optionnel: Masquer le formulaire ou afficher un message clair
            const form = document.getElementById('payment-form');
            if (form) form.style.display = 'none'; // Cache le formulaire si bateau non trouvé
            // Vider les infos pour éviter confusion
            if (document.getElementById("port")) document.getElementById("port").textContent = '-';
            // ... vider les autres champs ...
            if (document.getElementById("avis")) document.getElementById("avis").textContent = '-';
        }
    }); // Fin de DOMContentLoaded


    // --- Gestion du changement de langue ---
    function toggleLangDropdown() {
        const dropdown = document.getElementById("lang-dropdown");
        if (!dropdown) return;
        dropdown.innerHTML = ""; // Vider

        const currentLang = localStorage.getItem("langue") || "fr";
        const otherLang = currentLang === "fr" ? "en" : "fr"; // Basculer vers l'autre langue
        const newFlag = document.createElement("img");
        newFlag.src = otherLang === "fr" ? "images/drapeau-francais.png" : "images/drapeau-anglais.png";
        newFlag.alt = otherLang === "fr" ? "Français" : "Anglais";
        newFlag.classList.add("drapeau-option");
        newFlag.onclick = (event) => {
            event.stopPropagation(); // Empêche le clic de fermer immédiatement le dropdown
            changerLangue(otherLang);
        };

        dropdown.appendChild(newFlag);
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block"; // Basculer l'affichage
    }

    function changerLangue(langue) {
        localStorage.setItem("langue", langue);
        location.reload(); // Recharger la page pour appliquer la langue
    }

    // Mettre à jour le drapeau actuel au chargement
    window.addEventListener('load', function() { // Utiliser load pour être sûr que les images sont prêtes
        const currentLangIcon = document.getElementById("current-lang");
        const langue = localStorage.getItem("langue") || "fr";
        if (currentLangIcon) {
            currentLangIcon.src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        }
    });


    // Fermer le dropdown de langue si on clique ailleurs
    document.addEventListener("click", function(event) {
        const dropdown = document.getElementById("lang-dropdown");
        const icon = document.getElementById("current-lang");
        // Vérifier que les éléments existent avant d'appeler contains
        if (dropdown && icon && !dropdown.contains(event.target) && !icon.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });

</script>
</body>
</html>