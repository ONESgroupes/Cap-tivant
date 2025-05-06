<?php
session_start();
require_once 'config.php';

// Vérifier la connexion à la base de données
if (!isset($pdo)) {
    error_log("ERREUR CRITIQUE: PDO non défini dans enregistrer_avis.php");
    header("Location: PageAccueil.php?error=config");
    exit;
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Erreur PDO setAttribute : " . $e->getMessage());
}

// Vérifier que la requête est en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Requête non POST vers enregistrer_avis.php");
    header("Location: PageAccueil.php");
    exit;
}

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    error_log("Tentative d'avis par utilisateur non connecté");
    header("Location: PageAccueil.php?error=not_logged_in");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les données du formulaire
$titre_bateau = $_POST['titre'] ?? '';
$commentaire = trim($_POST['commentaire'] ?? '');
$etoiles = filter_input(INPUT_POST, 'etoiles', FILTER_VALIDATE_INT);

// Vérifier les champs
if (!$titre_bateau || !$commentaire || !$etoiles || $etoiles < 1 || $etoiles > 5) {
    error_log("Données invalides pour l'avis : " . print_r($_POST, true));
    header("Location: historique.php?error=invalid_data");
    exit;
}

// Chercher l'ID du bateau
try {
    $stmt = $pdo->prepare("SELECT id FROM bateaux WHERE titre = :titre");
    $stmt->bindParam(':titre', $titre_bateau);
    $stmt->execute();
    $bateau = $stmt->fetch();

    if (!$bateau) {
        error_log("Bateau introuvable pour le titre : $titre_bateau");
        header("Location: historique.php?error=boat_not_found");
        exit;
    }

    $boat_id = $bateau['id'];

    // Vérifier si un avis existe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = :user_id AND boat_id = :boat_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':boat_id', $boat_id, PDO::PARAM_INT);
    $stmt->execute();
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        error_log("Avis déjà existant pour user ID $user_id et bateau ID $boat_id");
        header("Location: historique.php?error=already_reviewed");
        exit;
    }

    // Insérer l'avis
    $stmt = $pdo->prepare("INSERT INTO reviews (user_id, boat_id, rating, comment, created_at) VALUES (:user_id, :boat_id, :rating, :comment, NOW())");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':boat_id', $boat_id, PDO::PARAM_INT);
    $stmt->bindParam(':rating', $etoiles, PDO::PARAM_INT);
    $stmt->bindParam(':comment', $commentaire, PDO::PARAM_STR);
    $stmt->execute();

    header("Location: historique.php?success=review_added");
    exit;

} catch (PDOException $e) {
    error_log("Erreur lors de l'enregistrement de l'avis : " . $e->getMessage());
    header("Location: historique.php?error=db_error");
    exit;
}
?>
