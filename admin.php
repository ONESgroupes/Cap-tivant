<?php
session_start(); // important pour lire les messages d'erreur
require_once 'config.php';

$errorMessage = $_SESSION['error'] ?? null;
$lastTable = $_SESSION['last_table'] ?? null;
$lastValues = $_SESSION['last_values'] ?? [];
unset($_SESSION['error'], $_SESSION['last_table'], $_SESSION['last_values']);

$boats = [];
$stmtBoats = $pdo->query("SELECT id, titre FROM bateaux");
$boats = $stmtBoats->fetchAll(PDO::FETCH_KEY_PAIR);

$users = [];
$stmtUsers = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM users");
$users = $stmtUsers->fetchAll(PDO::FETCH_KEY_PAIR);


$readOnlyTables = ['contact_messages', 'historique'];
$signalableTables = ['reviews'];

$tables = [];
try {
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
    <div class="menu-tables" style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #f29066;">Choisissez une table</h2>
        <?php foreach ($tables as $tableName): ?>
            <button onclick="afficherTable('<?= htmlspecialchars($tableName) ?>')" style="margin: 5px; padding: 10px; cursor: pointer;">
                <?= htmlspecialchars($tableName) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($tables as $tableName): ?>
        <div class="table-wrapper" id="table-<?= htmlspecialchars($tableName) ?>" style="display: none;">
            <h2><?= htmlspecialchars($tableName) ?></h2>
            <table class="admin-table">
                <thead>
                <tr>
                    <?php
                    $columns = $pdo->query("DESCRIBE `$tableName`")->fetchAll(PDO::FETCH_COLUMN);
                    $ignoredColumns = [
                        'default' => ['id', 'created_at'],
                        'bateaux' => ['created_at'],
                        'users' => ['id', 'created_at', 'password_hash', 'admin', 'newsletter', 'address', 'postal_code', 'city', 'country'],
                    ];

                    $columnsToIgnore = $ignoredColumns[$tableName] ?? $ignoredColumns['default'];

                    $columns = array_filter($columns, function($col) use ($columnsToIgnore) {
                        return !in_array($col, $columnsToIgnore);
                    });
                    $columns = array_values($columns); // réindexer proprement

                    $columns = array_filter($columns, function($col) {
                        return $col !== 'id' && $col !== 'created_at';
                    });
                    $columns = array_values($columns); // Réindexer proprement
                    foreach ($columns as $col) {
                        echo "<th>" . htmlspecialchars($col) . "</th>";
                    }
                    if (!in_array($tableName, $readOnlyTables) && !in_array($tableName, $signalableTables)) {
                        echo "<th>Actions</th>";
                    } elseif (in_array($tableName, $signalableTables)) {
                        echo "<th>Signaler</th>";
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                $rows = $pdo->query("SELECT * FROM `$tableName`")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row):
                    $skipRow = false;
                    foreach ($row as $value) {
                        if ($value === 'AUTO') {
                            $skipRow = true;
                            break;
                        }
                    }
                    if ($skipRow) continue;
                    ?>
                    <tr>
                        <?php if (in_array($tableName, $readOnlyTables)): ?>
                            <?php foreach ($columns as $col): ?>
                                <td>
                                    <?php
                                    if (in_array($tableName, ['historique', 'reviews']) && ($col === 'bateau_id' || $col === 'boat_id')) {
                                        echo htmlspecialchars($boats[$row[$col]] ?? 'Bateau inconnu');
                                    } elseif (in_array($tableName, ['historique', 'reviews']) && $col === 'user_id') {
                                        echo htmlspecialchars($users[$row[$col]] ?? 'Utilisateur inconnu');
                                    } else {
                                        echo htmlspecialchars($row[$col]);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>


                        <?php elseif (in_array($tableName, $signalableTables)): ?>
                            <?php foreach ($columns as $col): ?>
                                <td>
                                    <?php
                                    if (in_array($tableName, ['historique', 'reviews']) && ($col === 'bateau_id' || $col === 'boat_id')) {
                                        echo htmlspecialchars($boats[$row[$col]] ?? 'Bateau inconnu');
                                    } elseif (in_array($tableName, ['historique', 'reviews']) && $col === 'user_id') {
                                        echo htmlspecialchars($users[$row[$col]] ?? 'Utilisateur inconnu');
                                    } else {
                                        echo htmlspecialchars($row[$col]);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>

                            <td>
                                <form method="POST" action="signaler_avis.php">
                                    <input type="hidden" name="table" value="<?= htmlspecialchars($tableName) ?>">
                                    <?php
                                    $primaryKey = $pdo->query("SHOW KEYS FROM `$tableName` WHERE Key_name = 'PRIMARY'")
                                        ->fetch(PDO::FETCH_ASSOC)['Column_name'];
                                    ?>
                                    <input type="hidden" name="primary_key" value="<?= htmlspecialchars($primaryKey) ?>">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($row[$primaryKey]) ?>">
                                    <button type="submit" style="background-color: orange; color: white;"
                                            onclick="return confirmerSignalement();">Signaler</button>
                                </form>
                            </td>

                        <?php else: ?>
                            <form method="POST" action="modifier_table.php">
                                <?php foreach ($columns as $col): ?>
                                    <td>
                                        <input type="text" name="<?= htmlspecialchars($col) ?>" value="<?= htmlspecialchars($row[$col]) ?>">
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <input type="hidden" name="table" value="<?= htmlspecialchars($tableName) ?>">
                                    <input type="hidden" name="primary_key" value="<?= htmlspecialchars($columns[0]) ?>">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($row[$columns[0]]) ?>">
                                    <button type="submit">Modifier</button>
                            </form>
                            <form method="POST" action="supprimer_table.php" onsubmit="return confirm('Confirmer la suppression ?');" style="display: inline;">
                                <input type="hidden" name="table" value="<?= htmlspecialchars($tableName) ?>">
                                <input type="hidden" name="primary_key" value="<?= htmlspecialchars($columns[0]) ?>">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($row[$columns[0]]) ?>">
                                <button type="submit" style="margin-left: 5px; background-color: #f29066; color: white;">Supprimer</button>
                            </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>

                <?php if ($errorMessage && $lastTable === $tableName): ?>
                    <tr>
                        <td colspan="<?= count($columns) + 1 ?>" style="color: red; font-weight: bold; text-align: center;">
                            <?= htmlspecialchars($errorMessage) ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php if (!in_array($tableName, $readOnlyTables) && !in_array($tableName, $signalableTables)): ?>
                    <form method="POST" action="ajouter_table.php">
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <td>
                                    <?php if ($col === 'id' || $col === 'created_at'): ?>
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
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>

<div style="text-align: center; margin-top: 20px;">
    <form method="post" action="deconnexion.php">
        <strong><button type="submit" style="color: #ee9c72; font-family: 'DM Serif Display', cursive; cursor: pointer; background: none; border: none; outline: none; font-size: 15px;">Se déconnecter</button></strong>
    </form>
</div>

<script>
    function afficherTable(tableName) {
        document.querySelectorAll('.table-wrapper').forEach(function(div) {
            div.style.display = 'none';
        });
        const tableDiv = document.getElementById('table-' + tableName);
        if (tableDiv) {
            tableDiv.style.display = 'block';
        }
    }
</script>
<script>
    function confirmerSignalement() {
        return confirm("⚠️ Voulez-vous vraiment signaler cet avis ?\n\nPS : Si cet avis est signalé, il sera immédiatement supprimé.");
    }
</script>

</body>
</html>
