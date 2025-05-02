<?php
/**
 * Fichier de Configuration Principal pour Cap'Tivant
 *
 * Contient les paramètres de connexion BDD, les chemins pour les uploads,
 * et d'autres configurations globales.
 */

// --- PARAMÈTRES DE CONNEXION À LA BASE DE DONNÉES ---
// !! À MODIFIER avec vos informations réelles !!
define('DB_HOST', 'localhost');           // Hôte MySQL (souvent localhost ou 127.0.0.1)
define('DB_NAME', 'bdd_app');        // *** REMPLACEZ par le nom de votre BDD ***
define('DB_USER', 'root');                // *** REMPLACEZ par votre utilisateur BDD ***
define('DB_PASS', '');                    // *** REMPLACEZ par le mot de passe BDD (peut être vide en local avec XAMPP/WAMP) ***
define('DB_CHARSET', 'utf8mb4');          // Jeu de caractères (recommandé)

// --- Configuration de l'Application (Optionnel) ---
/**
 * Chemin de base de l'URL si votre application est dans un sous-dossier.
 * Laissez vide ('') si l'application est à la racine du serveur web.
 * Exemple: '/captivant/' si l'URL est http://localhost/captivant/
 */
// define('APP_BASE_URL_PATH', ''); // Décommentez et ajustez si nécessaire

// --- Configuration des Uploads (pour les images des bateaux) ---
/**
 * Chemin système ABSOLU vers le dossier où les images des bateaux seront stockées.
 * IMPORTANT: Ce dossier DOIT exister et être accessible en ÉCRITURE par le serveur web (Apache/Nginx).
 * Utilisez des slashs / même sous Windows pour la compatibilité.
 * Exemple Windows: 'C:/xampp/htdocs/captivant/uploads/boat_images/'
 * Exemple Linux/Mac: '/var/www/html/captivant/uploads/boat_images/'
 */
define('BOAT_UPLOAD_DIR', __DIR__ . '/uploads/boat_images/'); // __DIR__ pointe vers le dossier où se trouve config.php

/**
 * Chemin RELATIF utilisé pour enregistrer dans la BDD (colonnes image1, image2)
 * et pour construire les URL dans les balises <img src="...">.
 * Doit correspondre à la partie accessible via le web de BOAT_UPLOAD_DIR.
 */
define('BOAT_UPLOAD_DIR_RELATIVE', 'uploads/boat_images/');

/**
 * Taille maximale autorisée pour l'upload de chaque image de bateau (en octets).
 * Exemples: 2 * 1024 * 1024 = 2 Mo, 5 * 1024 * 1024 = 5 Mo
 */
define('MAX_BOAT_IMAGE_SIZE', 5 * 1024 * 1024); // 5 Mo par image

/**
 * Types MIME autorisés pour les images des bateaux.
 * Vérification de sécurité importante.
 */
define('ALLOWED_BOAT_IMAGE_MIME_TYPES', [
    'image/jpeg', // .jpg, .jpeg
    'image/png',  // .png
    'image/gif',  // .gif (si vous les autorisez)
    'image/webp'  // Format moderne et efficace
]);

/**
 * Extensions de fichiers autorisées (en minuscules) pour les images des bateaux.
 * Double vérification.
 */
define('ALLOWED_BOAT_IMAGE_EXTENSIONS', [
    'jpg',
    'jpeg',
    'png',
    'gif',
    'webp'
]);


// --- Connexion à la Base de Données (PDO) ---
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lancer les erreurs SQL comme exceptions PHP
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Récupérer les résultats en tableaux associatifs par défaut
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Utiliser les vraies requêtes préparées MySQL (plus sûr)
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options); // <- Cette ligne DOIT exister et s'exécuter
} catch (PDOException $e) {
    // Enregistrer l'erreur détaillée dans les logs serveur (NE PAS l'afficher directement en production)
    error_log("ERREUR DE CONNEXION BDD [Captivant]: " . $e->getMessage());

    // Afficher un message d'erreur générique à l'utilisateur et arrêter l'exécution
    // Personnalisez ce message si nécessaire
    die("Une erreur technique empêche l'accès au service. Veuillez réessayer plus tard.");

    // Pendant le développement, pour voir l'erreur exacte, vous pouvez décommenter la ligne suivante :
    // die("Erreur de connexion BDD: " . $e->getMessage());
}

// --- Inclusion de Fichiers d'Aide (Optionnel) ---
/**
 * Si vous avez des fonctions utilitaires globales (ex: pour valider des données,
 * formater des dates, gérer les sessions...), vous pouvez les inclure ici.
 * Assurez-vous que le chemin est correct. Il est souvent préférable de gérer
 * cela via un autoloader ou un fichier 'bootstrap' principal.
 */
// require_once __DIR__ . '/includes/functions.php'; // Exemple, adaptez ou supprimez si non utilisé


// La variable $pdo est maintenant prête.
// Pas de balise PHP fermante ?>