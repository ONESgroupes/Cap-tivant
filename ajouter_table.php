<?php
require_once 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table'])) {
    $table = $_POST['table'];
    unset($_POST['table']);

    $data = [];
    $placeholders = [];
    $missingFields = [];

    foreach ($_POST as $column => $value) {
        if ($column === 'created_at') continue;

        // Hash automatique du mot de passe si c'est le bon champ
        if ($column === 'password_hash') {
            $value = password_hash($value, PASSWORD_DEFAULT);
        }

        if ($value === '') {
            $missingFields[] = $column;
        }

        $data[$column] = $value;
        $placeholders[] = ':' . $column;
    }


    // Affiche un message d’erreur si des champs sont vides
    if (!empty($missingFields)) {
        echo "Veuillez remplir les champs suivants : <strong>" . implode(', ', $missingFields) . "</strong>";
        echo '<br><a href="admin.php">Retour</a>';
        exit;
    }

    $columns = array_keys($data);

    $sql = "INSERT INTO `$table` (" . implode(", ", $columns) . ")
            VALUES (" . implode(", ", $placeholders) . ")";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        header("Location: admin.php");
        exit;
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    echo "Requête invalide.";
}
