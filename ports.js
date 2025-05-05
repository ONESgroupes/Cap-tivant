// Initialisation par défaut de la carte sur la France
var map = L.map('map').setView([46.603354, 1.888334], 6);

// Fond de carte
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Liste des ports
const ports = [
    { name: "Lorient", coords: [47.7489, -3.3666], image: "/images/lorient.jpg" },
    { name: "Concarneau", coords: [47.875, -3.9183], image: "/images/concarneau.jpeg" },
    { name: "Bordeaux", coords: [44.8378, -0.5792], image: "/images/bordeaux.jpg" },
    { name: "Vannes", coords: [47.6559, -2.7603], image: "/images/vannes.jpeg" },
    { name: "Brest", coords: [48.3904, -4.4861], image: "/images/brest" },
    { name: "Marseille", coords: [43.2965, 5.3698], image: "/images/marseille.jpg" }
];

// Utilisé pour retrouver le marqueur d’un port donné
const marqueursParNom = {};

// Création des marqueurs
ports.forEach(port => {
    let boatCount = 0;
    if (typeof bateaux !== 'undefined' && Array.isArray(bateaux)) {
        boatCount = bateaux.filter(b => b.port && b.port.toLowerCase() === port.name.toLowerCase()).length;
    }

    const marker = L.marker(port.coords, {
        icon: L.icon({
            iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        })
    }).addTo(map);

    marqueursParNom[port.name.toLowerCase()] = marker;

    const tooltipContent = `
        <div style="text-align:center; max-width: 200px;">
            <strong>${port.name}</strong><br>
            <img src="${port.image}" alt="${port.name}" style="width: 100%; border-radius: 8px; margin: 5px 0;">
            <p>${boatCount} bateau${boatCount > 1 ? 'x' : ''} disponible${boatCount > 1 ? 's' : ''}</p>
            <a href="offre.php?port=${encodeURIComponent(port.name)}" 
               style="display: inline-block; margin-top: 8px; padding: 8px 16px; background-color: #f29066; 
               color: white; border-radius: 8px; text-decoration: none; font-family: 'DM Serif Display', serif;">
               Voir les offres
            </a>
        </div>
    `;

    marker.bindTooltip(tooltipContent, {
        direction: 'top',
        offset: [0, -20],
        permanent: false,
        sticky: true,
        opacity: 0.95,
        className: 'custom-tooltip'
    });

    marker.bindPopup(`
        <div class="popup-content" style="text-align: center;">
            <h3>${port.name}</h3>
            <img src="${port.image}" alt="${port.name}" style="max-width: 100%; margin: 0 auto;">
            <p>${boatCount} bateau${boatCount > 1 ? 'x' : ''} disponible${boatCount > 1 ? 's' : ''}</p>
        </div>
    `);

    marker.on('click', () => {
        window.location.href = `offre.php?port=${encodeURIComponent(port.name)}`;
    });
});

// 📌 Fonction utilitaire pour lire les paramètres URL
function getParametreURL(nom) {
    const params = new URLSearchParams(window.location.search);
    return params.get(nom);
}

// 🔍 Vérifie si l'URL contient des coordonnées
const lat = parseFloat(getParametreURL("lat"));
const lng = parseFloat(getParametreURL("lng"));
const zoom = parseInt(getParametreURL("zoom")) || 10;
const portDemande = getParametreURL("port");

// 📍 Si coordonnées valides → zoom sur le port
if (!isNaN(lat) && !isNaN(lng)) {
    map.setView([lat, lng], zoom);

    if (portDemande && marqueursParNom[portDemande.toLowerCase()]) {
        marqueursParNom[portDemande.toLowerCase()].openPopup();
    }
}
