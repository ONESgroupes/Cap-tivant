<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'] ?? '';
    $primaryKey = $_POST['primary_key'] ?? '';
    $id = $_POST['id'] ?? '';

    if ($table === 'reviews' && $primaryKey && $id) {
        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$primaryKey` = :id");
        $stmt->execute(['id' => $id]);
    }
}

header('Location: admin.php');
exit;
?>
