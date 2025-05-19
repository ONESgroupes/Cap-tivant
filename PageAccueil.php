<?php
session_start();
$estConnecte = isset($_SESSION['user_id']);
$accessibilityMode = isset($_SESSION['accessibility_mode']) ? $_SESSION['accessibility_mode'] : false;
?>
<!DOCTYPE html>
<html lang="fr" class="<?= $accessibilityMode ? 'accessibility-mode' : '' ?>">
<head>
    <meta charset="UTF-8">
    <title id="page-title"></title>
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        body.accessibility-mode {
            font-size: 18px;
            line-height: 1.6;
        }

        body.accessibility-mode a,
        body.accessibility-mode button {
            padding: 10px;
            border: 2px solid #000;
            font-size: 16px;
        }

        body.accessibility-mode .menu-content a {
            padding: 15px;
            font-size: 20px;
        }

        #access-control, #music-control {
            position: fixed;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            padding: 12px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            z-index: 1000;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 2px solid #4a4a4a;
        }

        #music-control {
            bottom: 20px;
        }

        #access-control {
            bottom: 80px;
        }

        #access-control img {
            width: 24px;
            height: 24px;
        }

        #access-control:hover, #music-control:hover {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.1);
        }

        #access-control::after, #music-control::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }

        #access-control:hover::after, #music-control:hover::after {
            opacity: 1;
        }
    </style>
    <style>
        /* Barre de fond en haut */
        .top-bar-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 50px; /* ajuste la hauteur comme tu veux */
            background-color: #20548e; /* couleur de fond */
            z-index: 0; /* envoie derrière les autres éléments */
        }

        /* Exemple de bouton au-dessus de la barre */
        .button-top {
            position: relative;
            z-index: 1; /* plus élevé que la barre */
            margin: 20px;
            padding: 10px 20px;
            background-color: #c5d8d3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>

</head>
<body>
</div>
<audio id="background-music" loop>
    <source src="/Cap-tivant/son/piano.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="menu-sound">
    <source src="/Cap-tivant/son/interface menu copie.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="ports-hover-sound">
    <source src="/Cap-tivant/son/nos ports.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="location-hover-sound">
    <source src="/Cap-tivant/son/location.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="historique-hover-sound">
    <source src="/Cap-tivant/son/historique.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="faq-hover-sound">
    <source src="/Cap-tivant/son/faq.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="avis-hover-sound">
    <source src="/Cap-tivant/son/avis.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="historique-hover-sound">
    <source src="/Cap-tivant/son/historique.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="moncompte-hover-sound">
    <source src="/Cap-tivant/son/mon compte.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="contact-hover-sound">
    <source src="/Cap-tivant/son/contact.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="mentions-hover-sound">
    <source src="/Cap-tivant/son/mention legale.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="apropos-hover-sound">
    <source src="/Cap-tivant/son/a-propos.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>

<audio id="lang-fr-sound">
    <source src="/Cap-tivant/son/francais.mp3" type="audio/mpeg">
    Votre navigateur ne supporte pas l’audio.
</audio>





<div class="background-fixe"></div>
<div class="top-bar-background"></div>

<!-- Menu -->
<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu.png" alt="Menu">
</div>

<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content" id="menu-links"></div>
</div>

<!-- Haut de page -->
<div class="top-right">
    <div class="language-selector">
        <img id="current-lang" src="images/drapeau-francais.png" alt="Langue" onclick="toggleLangDropdown()" class="drapeau-icon">
        <div id="lang-dropdown" class="lang-dropdown"></div>
    </div>
    <a id="a-propos-link" href="a-propos.php" class="top-infos"></a>
    <a id="compte-link" href="<?= $estConnecte ? 'MonCompte.php' : 'Connexion.php' ?>" class="top-infos"></a>
    <a href="favoris.php">
        <img src="images/panier.png" alt="Panier">
    </a>
</div>

<div class="top-center">
    <img src="images/logo.png" alt="Logo">
</div>

<!-- Accueil -->
<section class="section-accueil">
    <div class="texte-wrapper">
        <div class="texte principal" id="texte-principal">Cap'Tivant</div>
        <div class="texte secondaire" id="texte-secondaire">Location de bateau</div>
    </div>
</section>

<!-- Carrousel -->
<section class="section-bateau">
    <div class="bloc-bateau-carre">
        <div class="carousel">
            <div class="contenu-bateau">
                <a href="ports.php" class="carte" id="carte-link">
                    <div id="carte"></div>
                </a>
                <div class="carre-carousel-slide">
                    <div class="carre-track-slide" id="carouselTrack">
                        <a href="info-bateau.php?id=3"><img src="images/bateau-voile1.png" alt="Bateau 3"></a>
                        <a href="info-bateau.php?id=1"><img src="images/bateau1.png" alt="Bateau 1"></a>
                        <a href="info-bateau.php?id=2"><img src="images/catamaran1.png" alt="Bateau 2"></a>
                        <a href="info-bateau.php?id=4"><img src="images/moteur1.png" alt="Bateau 4"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pied de page -->
<div class="bottom-center">
    <a id="lien-mentions" href="MentionsLegales.php" class="bottom-text"></a>
    <span class="bottom-text">•</span>
    <a id="lien-contact" href="Contact.php" class="bottom-text"></a>
</div>

<!-- Contrôles -->
<div id="access-control" data-tooltip="<?= $accessibilityMode ? 'Désactiver accessibilité' : 'Activer accessibilité' ?>">
    <img src="images/aveugle.jpeg" alt="Mode accessibilité">
</div>
<div id="music-control" data-tooltip="Activer la musique">🔇</div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="info-bateau.js"></script>
<script>
    function toggleMenu() {
        const overlay = document.getElementById("menu-overlay");
        overlay.classList.toggle("active");

        // Si accessibilité activée, jouer le son du menu
        if (document.body.classList.contains('accessibility-mode')) {
            const menuAudio = document.getElementById('menu-sound');
            menuAudio.currentTime = 0;
            menuAudio.play().catch(() => {});
        }
    }

    function handleCompteClick() {
        const estConnecte = <?= json_encode($estConnecte) ?>;

        if (!estConnecte) {
            window.location.href = 'Connexion.php';
        } else {
            toggleMoncompte();
        }
    }

    // 🔊 Son au survol du bouton "Mon compte"
    const compteLink = document.getElementById("compte-link");
    const hoverAudio = document.getElementById("compte-hover-sound");

    if (compteLink && hoverAudio) {
        compteLink.addEventListener("mouseenter", () => {
            if (document.body.classList.contains('accessibility-mode')) {
                hoverAudio.currentTime = 0;
                hoverAudio.play().catch(() => {});
            }
        });
    }

    function toggleLangDropdown() {
        const dropdown = document.getElementById("lang-dropdown");
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    }

    function changerLangue(langue) {
        localStorage.setItem("langue", langue);
        location.reload();
    }

    function toggleAccessibility() {
        const isActive = document.body.classList.toggle('accessibility-mode');
        document.getElementById('access-control').setAttribute('data-tooltip', isActive ? 'Désactiver accessibilité' : 'Activer accessibilité');
        fetch('set_accessibility.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'mode=' + (isActive ? '1' : '0')
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        const langue = localStorage.getItem("langue") || "fr";
        const commun = langue === "en" ? CommunEN : CommunFR;
        const texte = langue === "en" ? AccueilEN : AccueilFR;

        document.getElementById("page-title").textContent = texte.titre;
        document.getElementById("texte-principal").textContent = texte.textePrincipal;
        document.getElementById("texte-secondaire").textContent = texte.texteSecondaire;

        document.getElementById("lien-mentions").textContent = commun.mentions;
        document.getElementById("lien-contact").textContent = commun.contact;
        document.getElementById("a-propos-link").textContent = commun.info;
        document.getElementById("compte-link").textContent = commun.compte;

        document.getElementById("current-lang").src = langue === "en" ? "images/drapeau-anglais.png" : "images/drapeau-francais.png";
        document.getElementById("lang-dropdown").innerHTML = langue === "en"
            ? `<img src="images/drapeau-francais.png" alt="Français" class="drapeau-option" onclick="changerLangue('fr')">`
            : `<img src="images/drapeau-anglais.png" alt="Anglais" class="drapeau-option" onclick="changerLangue('en')">`;

        const liens = ["location", "ports", "MonCompte", "historique", "faq", "avis"];
        const menuContent = document.getElementById("menu-links");
        menuContent.innerHTML = commun.menu.map((item, index) => `<a href="${liens[index]}.php">${item}</a>`).join('') + '<span onclick="toggleMenu()" class="close-menu">✕</span>';
        // Lecture du son quand on survole "Nos ports"
        const portsLink = Array.from(document.querySelectorAll('#menu-links a')).find(a => a.getAttribute('href') === 'ports.php');
        const portsAudio = document.getElementById('ports-hover-sound');

        if (portsLink && portsAudio) {
            portsLink.addEventListener('mouseenter', () => {
                if (document.body.classList.contains('accessibility-mode')) {
                    portsAudio.currentTime = 0;
                    portsAudio.play().catch(err => {
                        console.warn("Erreur lecture audio (ports) :", err);
                    });
                }
            });
        }
        const locationLink = Array.from(document.querySelectorAll('#menu-links a')).find(a => a.getAttribute('href') === 'location.php');
        const locationAudio = document.getElementById('location-hover-sound');

        if (locationLink && locationAudio) {
            locationLink.addEventListener('mouseenter', () => {
                if (document.body.classList.contains('accessibility-mode')) {
                    locationAudio.currentTime = 0;
                    locationAudio.play().catch(err => {
                        console.warn("Erreur lecture audio (location) :", err);
                    });
                }
            });
        }
        const faqLink = Array.from(document.querySelectorAll('#menu-links a')).find(a => a.getAttribute('href') === 'faq.php');
        const faqAudio = document.getElementById('faq-hover-sound');

        if (faqLink && faqAudio) {
            faqLink.addEventListener('mouseenter', () => {
                if (document.body.classList.contains('accessibility-mode')) {
                    faqAudio.currentTime = 0;
                    faqAudio.play().catch(err => {
                        console.warn("Erreur lecture audio (faq) :", err);
                    });
                }
            });
        }
        const avisLink = Array.from(document.querySelectorAll('#menu-links a')).find(a => a.getAttribute('href') === 'avis.php');
        const avisAudio = document.getElementById('avis-hover-sound');

        if (avisLink && avisAudio) {
            avisLink.addEventListener('mouseenter', () => {
                if (document.body.classList.contains('accessibility-mode')) {
                    avisAudio.currentTime = 0;
                    avisAudio.play().catch(err => {
                        console.warn("Erreur lecture audio (avis) :", err);
                    });
                }
            });
        }
        const historiqueLink = Array.from(document.querySelectorAll('#menu-links a')).find(a => a.getAttribute('href') === 'historique.php');
        const historiqueAudio = document.getElementById('historique-hover-sound');

        if (historiqueLink && historiqueAudio) {
            historiqueLink.addEventListener('mouseenter', () => {
                if (document.body.classList.contains('accessibility-mode')) {
                    historiqueAudio.currentTime = 0;
                    historiqueAudio.play().catch(err => {
                        console.warn("Erreur lecture audio (historique) :", err);
                    });
                }
            });
        }
        const moncompteLink = Array.from(document.querySelectorAll('#menu-links a')).find(a => a.getAttribute('href') === 'MonCompte.php');
        const moncompteAudio = document.getElementById('moncompte-hover-sound');

        if (moncompteLink && moncompteAudio) {
            moncompteLink.addEventListener('mouseenter', () => {
                if (document.body.classList.contains('accessibility-mode')) {
                    moncompteAudio.currentTime = 0;
                    moncompteAudio.play().catch(err => {
                        console.warn("Erreur lecture audio (MonCompte) :", err);
                    });
                }
            });
        }
        const contactLink = document.getElementById("lien-contact");
        const contactAudio = document.getElementById("contact-hover-sound");

        if (contactLink && contactAudio) {
            contactLink.addEventListener("mouseenter", () => {
                if (document.body.classList.contains('accessibility-mode')) {
                    contactAudio.currentTime = 0;
                    contactAudio.play().catch(err => {
                        console.warn("Erreur lecture audio (contact) :", err);
                    });
                }
            });
        }
        const mentionsLink = document.getElementById("lien-mentions");
        const mentionsAudio = document.getElementById("mentions-hover-sound");

        if (mentionsLink && mentionsAudio) {
            mentionsLink.addEventListener("mouseenter", () => {
                if (document.body.classList.contains('accessibility-mode')) {
                    mentionsAudio.currentTime = 0;
                    mentionsAudio.play().catch(err => {
                        console.warn("Erreur lecture audio (mentions légales) :", err);
                    });
                }
            });
        }
        const aproposLink = document.getElementById("a-propos-link");
        const aproposAudio = document.getElementById("apropos-hover-sound");

        if (aproposLink && aproposAudio) {
            aproposLink.addEventListener("mouseenter", () => {
                if (document.body.classList.contains('accessibility-mode')) {
                    aproposAudio.currentTime = 0;
                    aproposAudio.play().catch(err => {
                        console.warn("Erreur lecture audio (à propos) :", err);
                    });
                }
            });
        }
        const frLangIcon = document.querySelector('img[alt="Français"]');
        const frLangAudio = document.getElementById("lang-fr-sound");


        if (frLangIcon && frLangAudio) {
            frLangIcon.addEventListener("click", () => {
                if (document.body.classList.contains('accessibility-mode')) {
                    frLangAudio.currentTime = 0;
                    frLangAudio.play().catch(err => {
                        console.warn("Erreur lecture audio (langue FR) :", err);
                    });
                }
            });
        }







        const track = document.querySelector(".carre-track-slide");
        const images = document.querySelectorAll(".carre-track-slide img");
        const imageWidth = 400;
        let index = 0;
        setInterval(() => {
            index = (index + 1) % images.length;
            track.style.transform = `translateX(-${index * imageWidth}px)`;
        }, 2000);

        const map = L.map('carte').setView([47.2186, -1.5536], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Musique
        const music = document.getElementById('background-music');
        const control = document.getElementById('music-control');
        music.volume = 0.4;

        if (localStorage.getItem('musicEnabled') === 'true') {
            music.play().catch(() => {});
            control.textContent = '🔊';
        }

        control.addEventListener('click', function () {
            if (music.paused) {
                music.play().then(() => {
                    control.textContent = '🔊';
                    control.setAttribute('data-tooltip', langue === 'fr' ? 'Couper la musique' : 'Mute music');
                    localStorage.setItem('musicEnabled', 'true');
                }).catch(() => {
                    alert(langue === 'fr' ? 'Cliquez sur la page pour activer le son' : 'Click on the page to enable sound');
                });
            } else {
                music.pause();
                control.textContent = '🔇';
                control.setAttribute('data-tooltip', langue === 'fr' ? 'Activer la musique' : 'Enable music');
                localStorage.setItem('musicEnabled', 'false');
            }
        });

        document.addEventListener('click', function initAudio() {
            if (localStorage.getItem('musicEnabled') === 'true') {
                music.play().catch(() => {});
                control.textContent = '🔊';
            }
            document.removeEventListener('click', initAudio);
        });

        control.addEventListener('wheel', function (e) {
            e.preventDefault();
            music.volume = Math.max(0, Math.min(1, music.volume + (e.deltaY < 0 ? 0.1 : -0.1)));
        });

        document.getElementById('access-control').addEventListener('click', toggleAccessibility);
    });
</script>
</body>
</html>
