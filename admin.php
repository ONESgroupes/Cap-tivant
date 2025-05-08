<?php
session_start(); // important pour lire les messages d'erreur
require_once 'config.php';

$errorMessage = $_SESSION['error'] ?? null;
$lastTable = $_SESSION['last_table'] ?? null;
$lastValues = $_SESSION['last_values'] ?? [];
unset($_SESSION['error'], $_SESSION['last_table'], $_SESSION['last_values']);

$tables = [];
try {
    // Obtenir la liste des tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Cap'Tivant</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="PageAccueil.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
</head>
<body>
<div class="top-center">
    <div class="logo-block">
        <img src="images/logo.png" alt="Logo">
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-page" style="color: #e0e0d5">Admin</h1>
    </div>
</div>
<div class="tables-container">
    <?php foreach ($tables as $tableName): ?>
        <h2><?= htmlspecialchars($tableName) ?></h2>
        <table class="admin-table">
            <thead>
            <tr>
                <?php
                $columns = $pdo->query("DESCRIBE `$tableName`")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($columns as $col) {
                    echo "<th>" . htmlspecialchars($col) . "</th>";
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            $rows = $pdo->query("SELECT * FROM `$tableName`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                echo '<form method="POST" action="modifier_table.php">';
                echo "<tr>";
                foreach ($columns as $col) {
                    echo '<td><input type="text" name="' . htmlspecialchars($col) . '" value="' . htmlspecialchars($row[$col]) . '"></td>';
                }
                echo '<td><input type="hidden" name="table" value="' . htmlspecialchars($tableName) . '">';
                echo '<input type="hidden" name="primary_key" value="' . htmlspecialchars($columns[0]) . '">';
                echo '<input type="hidden" name="id" value="' . htmlspecialchars($row[$columns[0]]) . '">';
                echo '<button type="submit">Modifier</button>';
                echo '</form>';
                echo '<form method="POST" action="supprimer_table.php" onsubmit="return confirm(\'Confirmer la suppression ?\');">';
                echo '<input type="hidden" name="table" value="' . htmlspecialchars($tableName) . '">';
                echo '<input type="hidden" name="primary_key" value="' . htmlspecialchars($columns[0]) . '">';
                echo '<input type="hidden" name="id" value="' . htmlspecialchars($row[$columns[0]]) . '">';
                echo '<button type="submit" style="margin-left: 5px; background-color: #f29066; color: white;">Supprimer</button>';
                echo '</form></td>';

                echo "</tr>";
                echo '</form>';

            }
            ?>
            <?php if ($errorMessage && $lastTable === $tableName): ?>
                <tr>
                    <td colspan="<?= count($columns) + 1 ?>" style="color: red; font-weight: bold; text-align: center;">
                        <?= htmlspecialchars($errorMessage) ?>
                    </td>
                </tr>
            <?php endif; ?>
            <form method="POST" action="ajouter_table.php">
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <td>
                            <?php if ($col === $columns[0] || $col === "created_at"): ?>
                                <input type="text" name="<?= htmlspecialchars($col) ?>" value="AUTO" disabled>
                            <?php else: ?>
                                <input type="text" name="<?= htmlspecialchars($col) ?>">
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                    <td>
                        <input type="hidden" name="table" value="<?= htmlspecialchars($tableName) ?>">
                        <button type="submit">Ajouter</button>
                    </td>
                </tr>
            </form>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
<div style="text-align: center; margin-top: 20px;">
    <form method="post" action="deconnexion.php">
        <strong><button type="submit" style="color: #ee9c72; font-family: 'DM Serif Display', cursive; cursor: pointer; background: none; border: none; outline: none; font-size: 15px;">Se déconnecter</button></strong>
    </form>
</div>
</body>
</html>
