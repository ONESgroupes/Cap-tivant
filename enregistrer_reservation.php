<?php
// TOUJOURS commencer par session_start() avant toute sortie HTML ou autre code
session_start();

// Inclure la configuration de la base de données
require_once 'config.php'; // Assurez-vous que ce chemin est correct

// --- Vérification initiale de la connexion PDO ---
if (!isset($pdo)) {
    // Si $pdo n'est pas défini dans config.php, c'est une erreur critique
    error_log("ERREUR CRITIQUE: L'objet PDO n'est pas défini dans config.php (appel depuis enregistrer_reservation.php)");
    // Rediriger vers une page d'erreur générique ou la page d'accueil
    header("Location: index.php?error=config"); // Ou une page d'erreur dédiée
    exit;
}

// Activer les exceptions PDO pour un meilleur rapport d'erreurs (optionnel mais recommandé)
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur PDO lors de setAttribute dans enregistrer_reservation.php: " . $e->getMessage());
    // Gérer l'erreur de configuration si nécessaire, mais on continue si possible
}


// --- Vérifier si la requête est bien en POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si quelqu'un accède à ce script directement ou par une autre méthode
    error_log("Accès non-POST refusé à enregistrer_reservation.php");
    header("Location: PageAccueil.php"); // Rediriger vers l'accueil
    exit;
}

// --- Récupérer et Valider l'ID du bateau ---
// Utiliser filter_input pour plus de sécurité
$id_bateau = filter_input(INPUT_POST, 'id_bateau', FILTER_VALIDATE_INT);

// Vérifier si l'ID est un entier positif valide
if ($id_bateau === false || $id_bateau <= 0) {
    error_log("ID Bateau invalide reçu via POST: " . print_r($_POST['id_bateau'] ?? 'non défini', true));
    // Rediriger vers la page de paiement avec une erreur spécifique
    // Essayer de renvoyer l'ID tenté si possible, sinon rediriger sans ID ou vers une page générique
    $redirect_url = "payement.php?error=invalid_id";
    if (isset($_POST['id_bateau'])) {
        // Ajouter l'ID tenté (même s'il est invalide) pour contexte potentiel sur la page de paiement
        $redirect_url .= "&id=" . urlencode($_POST['id_bateau']);
    }
    header("Location: " . $redirect_url);
    exit;
}

// --- Vérifier si l'utilisateur est connecté ---
if (!isset($_SESSION['user_id'])) {
    error_log("Tentative de réservation par utilisateur non connecté pour bateau ID: " . $id_bateau);
    // Rediriger vers la page de paiement (ou connexion) avec un message d'erreur
    // Inclure l'ID du bateau pour que la page de paiement puisse se recharger correctement après connexion
    header("Location: payement.php?id=" . $id_bateau . "&error=not_logged_in");
    // Alternative : rediriger vers la page de connexion en passant la page de retour
    // header("Location: Connexion.php?redirect_url=" . urlencode("payement.php?id=" . $id_bateau));
    exit;
}

// Si on arrive ici, l'utilisateur est connecté et l'ID est valide
$user_id = $_SESSION['user_id'];
$date_debut = $_POST['date_debut'] ?? null;
$date_fin = $_POST['date_fin'] ?? null;

if (!$date_debut || !$date_fin || $date_debut > $date_fin) {
    error_log("Dates invalides : date_debut = $date_debut, date_fin = $date_fin");
    header("Location: payement.php?id=" . $id_bateau . "&error=invalid_dates");
    exit;
}

// --- Traitement de la réservation ---
try {
    // 1. Vérifier si une réservation existe déjà pour cet utilisateur et ce bateau
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM historique WHERE user_id = :user_id AND bateau_id = :bateau_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':bateau_id', $id_bateau, PDO::PARAM_INT);
    $stmt->execute();
    $reservationExists = $stmt->fetchColumn(); // fetchColumn() récupère la première colonne (COUNT(*))

    if ($reservationExists > 0) {
        // La réservation existe déjà
        error_log("Réservation déjà existante pour user ID: $user_id, bateau ID: $id_bateau");
        // Rediriger vers la page de paiement avec un message d'erreur
        header("Location: payement.php?id=" . $id_bateau . "&error=already_reserved");
        exit;

    } else {
        $stmt = $pdo->prepare("INSERT INTO historique (user_id, bateau_id, date_reservation, date_debut, date_fin)
                       VALUES (:user_id, :bateau_id, NOW(), :date_debut, :date_fin)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':bateau_id', $id_bateau, PDO::PARAM_INT);
        $stmt->bindParam(':date_debut', $date_debut);
        $stmt->bindParam(':date_fin', $date_fin);

        $stmt->execute();

        // Vérifier si l'insertion a réussi (optionnel, execute() lèverait une exception en cas d'erreur PDO)
        // if ($stmt->rowCount() > 0) { ... }

        // Redirection vers la page d'historique avec un message de succès
        header("Location: historique.php?success=reservation_added");
        exit;
    }

} catch (PDOException $e) {
    // En cas d'erreur lors de l'interaction avec la base de données
    error_log("Erreur PDO dans enregistrer_reservation.php pour user ID: $user_id, bateau ID: $id_bateau - Erreur: " . $e->getMessage());

    // Rediriger vers la page de paiement avec un message d'erreur générique
    header("Location: payement.php?id=" . $id_bateau . "&error=db_error");
    exit;
}

// Normalement, le script ne devrait jamais arriver ici car toutes les branches se terminent par exit;
// Mais par sécurité :
header("Location: PageAccueil.php?error=unexpected");
exit;

?>