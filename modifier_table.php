<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'];
    $primaryKey = $_POST['primary_key'];
    $id = $_POST['id'];

    // Enlever les champs techniques
    unset($_POST['table'], $_POST['primary_key'], $_POST['id']);

    // Construction de la requête
    $updates = [];
    foreach ($_POST as $column => $value) {
        $updates[] = "`$column` = :$column";
    }
    $sql = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE `$primaryKey` = :id";

    $stmt = $pdo->prepare($sql);

    foreach ($_POST as $column => $value) {
        $stmt->bindValue(":$column", $value);
    }
    $stmt->bindValue(':id', $id);

    if ($stmt->execute()) {
        header("Location: admin.php");
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}
