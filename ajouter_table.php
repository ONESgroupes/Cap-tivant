<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table'])) {
    $table = $_POST['table'];
    unset($_POST['table']);

    $data = [];
    $placeholders = [];
    $missingFields = [];

    foreach ($_POST as $column => $value) {
        if ($column === 'created_at') continue;

        if ($column === 'password_hash') {
            $value = password_hash($value, PASSWORD_DEFAULT);
        }

        if (trim($value) === '') {
            $missingFields[] = $column;
        }

        $data[$column] = $value;
        $placeholders[] = ':' . $column;
    }

    if (!empty($missingFields)) {
        $_SESSION['error'] = "Champs manquants : " . implode(', ', $missingFields);
        $_SESSION['last_table'] = $table;
        header("Location: admin.php");
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
        $_SESSION['error'] = "Erreur SQL : " . $e->getMessage();
        $_SESSION['last_table'] = $table;
        header("Location: admin.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Requête invalide.";
    header("Location: admin.php");
    exit;
}
