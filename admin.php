<?php
session_start();
require_once 'config.php';

// Erreurs & messages
$errorMessage = $_SESSION['error'] ?? null;
$lastTable = $_SESSION['last_table'] ?? null;
unset($_SESSION['error'], $_SESSION['last_table']);

// Bateaux
$boats = $pdo->query("SELECT id, titre FROM bateaux")->fetchAll(PDO::FETCH_KEY_PAIR);

// Users
$users = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) AS full_name FROM users")->fetchAll(PDO::FETCH_KEY_PAIR);


$readOnlyTables = ['contact_messages', 'historique'];
$signalableTables = ['reviews'];

// Liste des tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$colonneLabels = [
    'user_id' => 'Utilisateur',
    'boat_id' => 'Bateau',
    'bateau_id' => 'Bateau',
    'rating' => 'Note',
    'date_debut' => 'Date de debut',
    'date_fin' => 'Date de fin',
    'first_name' => 'Prénom',
    'last_name' => 'Nom',
    'name' => 'Nom',
    'phone' => 'Telephone',
    'email' => 'Adresse email',
    'comment' => 'Commentaire',
    'question_en' => 'question en anglais',
    'reponse_en' => 'réponse en anglais',
    'texte_en' => 'texte en anglais',
    'texte_fr' => 'texte',
];
$tableLabels = [
    'users' => 'Utilisateurs',
    'bateaux' => 'Bateaux sur le site',
    'reviews' => 'Commentaires utilisateurs',
    'contact_messages' => 'Messages utilisateurs',
    'faq' => 'Foire aux questions',
    'a_propos' => 'Description',
    'cgu' => 'CGU',
    'historique' => 'Historique des réservations',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Cap'Tivant</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="PageAccueil.css">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <style>
        .card { background: white; padding: 15px; margin: 10px 0; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card-header { font-weight: bold; font-size: 18px; cursor: pointer; }
        .card-actions { margin-top: 10px; }
        .card-details { display: none; margin-top: 10px; }
        .btn { padding: 5px 10px; margin-right: 5px; cursor: pointer; border: none; border-radius: 5px; }
        .btn-edit { background: #6fbf73; color: white; }
        .btn-delete { background: #f29066; color: white; }
        .btn-signal { background: orange; color: white; }
        .btn-add { background: #6495ed; color: white; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo.png" alt="Logo" >
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title" id="titre-page" style="color: #e0e0d5">Admin</h1>
    </div>
</div>

<div id="modal-ajout" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1000;">
    <div style="background:white; max-width:600px; margin:100px auto; padding:20px; border-radius:10px; position:relative;">
        <h3 id="modal-title">Ajouter</h3>
        <form id="form-ajout" method="POST" action="ajouter_table.php">
            <div id="champs-ajout"></div>
            <input type="hidden" name="table" id="table-ajout">
            <div style="margin-top:15px; text-align:right;">
                <button type="button" onclick="fermerModalAjout()" class="btn btn-delete">Annuler</button>
                <button type="submit" class="btn btn-add">Ajouter</button>
            </div>
        </form>
    </div>
</div>


<div class="tables-container" style=" margin-top: 150px">
    <div class="menu-tables" style="text-align: center;">
        <h2 style="color: #f29066;">Choisissez une catégorie</h2>
        <?php foreach ($tables as $tableName): ?>
            <button onclick="afficherTable('<?= htmlspecialchars($tableName) ?>')" class="btn">
                <?= htmlspecialchars($tableLabels[$tableName] ?? $tableName) ?>
            </button>
        <?php endforeach; ?>
    </div>
    <?php foreach ($tables as $tableName): ?>
        <?php
        $columns = $pdo->query("DESCRIBE `$tableName`")->fetchAll(PDO::FETCH_COLUMN);
        $ignored = [
            'default' => ['id', 'created_at'],
            'a_propos' => ['id', 'created_at', 'titre_fr', 'titre_en'],
            'cgu' => ['id', 'created_at', 'titre_fr', 'titre_en'],
            'bateaux' => ['id', 'created_at', 'image1', 'image2', 'type'],
            'users' => ['id', 'created_at', 'password_hash', 'admin', 'newsletter', 'address', 'postal_code', 'city', 'country'],
        ];

        $columnsToShow = array_values(array_filter($columns, fn($c) => !in_array($c, $ignored[$tableName] ?? $ignored['default'])));
        $rows = $pdo->query("SELECT * FROM `$tableName`")->fetchAll(PDO::FETCH_ASSOC);

// On récupère la clé primaire de la table (ex : id, ou autre)
        $primaryKey = $pdo->query("SHOW KEYS FROM `$tableName` WHERE Key_name = 'PRIMARY'")
            ->fetch(PDO::FETCH_ASSOC)['Column_name'] ?? 'id';
        ?>
        <div class="table-wrapper" id="table-<?= htmlspecialchars($tableName) ?>" style="display:none;">
            <h2><?= htmlspecialchars($tableLabels[$tableName] ?? $tableName) ?></h2>
            <?php if (in_array($tableName, ['bateaux', 'users', 'contact_messages', 'historique', 'reviews'])): ?>
                <div style="margin-bottom: 15px; text-align: center;">
                    <input type="text"
                           id="search-<?= $tableName ?>"
                           placeholder="Rechercher..."
                           oninput="filterBySearch('<?= $tableName ?>')"
                           style="padding: 8px; width: 60%; border-radius: 5px; border: 1px solid #ccc;">
                </div>
            <?php endif; ?>


            <?php if (in_array($tableName, ['bateaux', 'faq'])): ?>
                <button class="btn btn-add" onclick="ouvrirModalAjout('<?= $tableName ?>')">+ Ajouter</button>
            <?php endif; ?>


            <?php foreach ($rows as $row): ?>
                <div class="card">
                    <div class="card-header" onclick="toggleDetails(this)">
                        <?php
                        switch ($tableName) {
                            case 'a_propos':
                                echo htmlspecialchars($row['titre_fr']);
                                break;
                            case 'cgu':
                                echo htmlspecialchars($row['titre_fr']);
                                break;
                            case 'bateaux':
                                echo htmlspecialchars($row['titre']);
                                break;
                            case 'contact_messages':
                                echo htmlspecialchars($row['message'] ?? 'Message');
                                break;
                            case 'faq':
                                echo htmlspecialchars($row['question'] ?? 'Question');
                                break;
                            case 'historique':
                                $userName = $users[$row['user_id']] ?? 'Utilisateur inconnu';
                                $boatName = $boats[$row['bateau_id']] ?? 'Bateau inconnu';
                                echo htmlspecialchars("$userName - $boatName");
                                break;
                            case 'reviews':
                                echo htmlspecialchars($row['comment'] ?? 'Avis');
                                break;
                            case 'users':
                                echo htmlspecialchars($users[$row['id']] ?? 'Utilisateur inconnu');
                                break;
                            default:
                                echo htmlspecialchars(reset($row));
                                break;
                        }
                        ?>
                    </div>
                    <div class="card-actions">
                        <?php if (in_array($tableName, $signalableTables)): ?>
                            <form method="POST" action="signaler_avis.php" onsubmit="return confirmerSignalement();" style="display:inline;">
                                <input type="hidden" name="table" value="<?= htmlspecialchars($tableName) ?>">
                                <input type="hidden" name="primary_key" value="<?= htmlspecialchars($primaryKey) ?>">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($row[$primaryKey]) ?>">
                                <button type="submit" class="btn btn-signal">Signaler</button>
                            </form>
                        <?php elseif (!in_array($tableName, array_merge($readOnlyTables, ['a_propos', 'cgu']))): ?>
                            <form method="POST" action="supprimer_table.php" onsubmit="return confirm('Confirmer la suppression ?');" style="display:inline;">
                                <input type="hidden" name="table" value="<?= htmlspecialchars($tableName) ?>">
                                <input type="hidden" name="primary_key" value="<?= htmlspecialchars($primaryKey) ?>">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($row[$primaryKey]) ?>">
                                <button type="submit" class="btn btn-delete">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-details">
                        <?php if (in_array($tableName, ['bateaux', 'faq', 'a_propos', 'cgu'])): ?>
                            <form method="POST" action="modifier_table.php" class="edit-form">
                                <input type="hidden" name="table" value="<?= htmlspecialchars($tableName) ?>">
                                <input type="hidden" name="primary_key" value="<?= htmlspecialchars($primaryKey) ?>">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($row[$primaryKey]) ?>">

                                <?php foreach ($columnsToShow as $col): ?>
                                    <div class="detail-row">
                                        <span class="detail-label" style="font-weight: bold; display: inline-block; width: 160px;">
    <?= htmlspecialchars($colonneLabels[$col] ?? $col) ?> :
</span>

                                        <?php
                                        $isImportant = (str_starts_with($col, 'titre') || str_starts_with($col, 'texte')) && !in_array($tableName, ['cgu', 'a_propos', 'bateaux']);
                                        ?>
                                        <span
                                                class="detail-value<?= $isImportant ? ' important' : '' ?>"
                                                contenteditable="true"
                                                data-name="<?= htmlspecialchars($col) ?>"
                                                oninput="document.getElementById('hidden-<?= $col ?>').value = this.innerText"
                                        >
    <?= htmlspecialchars(($tableName === 'cgu') ? strip_tags($row[$col]) : $row[$col]) ?>
</span>


                                        <?php if (str_starts_with($col, 'texte')): ?>
                                            <textarea hidden name="<?= $col ?>" id="hidden-<?= $col ?>"><?= htmlspecialchars($row[$col]) ?></textarea>
                                        <?php else: ?>
                                            <input type="hidden" name="<?= $col ?>" id="hidden-<?= $col ?>" value="<?= htmlspecialchars($row[$col]) ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>

                                <button type="submit" class="btn btn-edit">Enregistrer les modifications</button>
                            </form>
                        <?php else: ?>
                            <?php foreach ($columnsToShow as $col): ?>
                                <div class="detail-row">
                                    <span class="detail-label"><?= htmlspecialchars($colonneLabels[$col] ?? $col) ?> :</span>
                                    <span class="detail-value">
                    <?php
                    if (($col === 'bateau_id' || $col === 'boat_id') && isset($boats[$row[$col]])) {
                        echo htmlspecialchars($boats[$row[$col]]);
                    } elseif ($col === 'user_id' && isset($users[$row[$col]])) {
                        echo htmlspecialchars($users[$row[$col]]);
                    } else {
                        echo htmlspecialchars($row[$col]);
                    }
                    ?>
                </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>


                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
<div style="text-align: center; margin-top: 20px;">
    <form action="deconnexion.php" method="POST">
        <button type="submit" class="btn btn-delete">Retour au site web</button>
    </form>
</div>
<script>
    const colonneLabels = <?= json_encode($colonneLabels) ?>;
</script>

<script>
    function afficherTable(tableName) {
        document.querySelectorAll('.table-wrapper').forEach(div => div.style.display = 'none');
        document.getElementById('table-' + tableName).style.display = 'block';
    }
    function toggleDetails(element) {
        let details = element.nextElementSibling.nextElementSibling;
        details.style.display = (details.style.display === 'block') ? 'none' : 'block';
    }
    function confirmerSignalement() {
        return confirm("⚠️ Voulez-vous vraiment signaler cet avis ?\n\nPS : Il sera immédiatement supprimé.");
    }
</script>

<script>
    const colonnesParTable = <?= json_encode(array_map(function($t) use ($pdo, $ignored) {
        $cols = $pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_COLUMN);
        return array_values(array_filter($cols, fn($c) => !in_array($c, $ignored[$t] ?? $ignored['default'])));
    }, array_combine($tables, $tables))) ?>;

    function ouvrirModalAjout(table) {
        const champs = colonnesParTable[table] || [];
        const container = document.getElementById('champs-ajout');
        container.innerHTML = '';
        document.getElementById('table-ajout').value = table;
        document.getElementById('modal-title').innerText = 'Ajouter dans "' + table + '"';

        champs.forEach(col => {
            const input = document.createElement('input');
            input.type = 'text';
            input.name = col;
            input.placeholder = colonneLabels[col] || col;
            input.style = 'display:block; width:100%; margin:5px 0; padding:8px; border:1px solid #ccc; border-radius:5px;';
            container.appendChild(input);
        });

        document.getElementById('modal-ajout').style.display = 'block';
    }

    function fermerModalAjout() {
        document.getElementById('modal-ajout').style.display = 'none';
    }
</script>
<script>
    function filterBySearch(tableName) {
        const input = document.getElementById('search-' + tableName);
        const filter = input.value.toUpperCase();
        const cards = document.querySelectorAll(`#table-${tableName} .card`);

        cards.forEach(card => {
            const title = card.querySelector('.card-header').textContent.toUpperCase();
            card.style.display = title.includes(filter) ? 'block' : 'none';
        });
    }
</script>


</body>
</html>