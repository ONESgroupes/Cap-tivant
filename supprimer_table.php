<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table'], $_POST['primary_key'], $_POST['id'])) {
    $table = $_POST['table'];
    $primaryKey = $_POST['primary_key'];
    $id = $_POST['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$primaryKey` = :id");
        $stmt->execute(['id' => $id]);
        header("Location: admin.php");
        exit;
    } catch (PDOException $e) {
        echo "Erreur lors de la suppression : " . $e->getMessage();
    }
} else {
    echo "Requête invalide.";
}
?>
