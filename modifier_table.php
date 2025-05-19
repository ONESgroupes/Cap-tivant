<?php
require_once 'config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $table = $_POST['table'] ?? '';
    $primaryKey = $_POST['primary_key'] ?? '';
    $id = $_POST['id'] ?? '';

    // Validation simple
    if (!$table || !$primaryKey || !$id) {
        die("Erreur : informations manquantes pour la mise à jour.");
    }

    // Enlever les champs techniques
    $data = $_POST;
    unset($data['table'], $data['primary_key'], $data['id']);

    if (empty($data)) {
        die("Erreur : Aucun champ à mettre à jour.");
    }

    // Construction de la requête SQL
    $updates = [];
    foreach ($data as $column => $value) {
        $updates[] = "`$column` = :$column";
    }

    $sql = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE `$primaryKey` = :id";

    try {
        $stmt = $pdo->prepare($sql);

        foreach ($data as $column => $value) {
            $stmt->bindValue(":$column", $value);
        }
        $stmt->bindValue(':id', $id);

        if ($stmt->execute()) {
            header("Location: admin.php");
            exit;
        } else {
            echo "Erreur lors de la mise à jour.";
        }

    } catch (PDOException $e) {
        echo "Erreur SQL : " . $e->getMessage();
    }
}
