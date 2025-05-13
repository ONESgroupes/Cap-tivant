<?php
// Affiche les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php'; // S'assurer que ce fichier existe et fonctionne

$token = $_GET['token'] ?? '';
$tokenPath = __DIR__ . "/tokens/$token.txt";
$message = '';
$email = '';

// Si le lien est cliqué (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!file_exists($tokenPath)) {
        $message = "❌ Lien invalide ou expiré.";
    } else {
        $email = trim(file_get_contents($tokenPath));
    }
}

// Si le formulaire est soumis (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $tokenPath = __DIR__ . "/tokens/$token.txt";
    $nouveau_mdp = $_POST['new_password'] ?? '';

    if (!file_exists($tokenPath)) {
        $message = "❌ Lien invalide ou expiré.";
    } elseif (empty($nouveau_mdp)) {
        $message = "❌ Le mot de passe ne peut pas être vide.";
    } elseif (strlen($nouveau_mdp) < 12 || !preg_match('/[A-Z]/', $nouveau_mdp) || !preg_match('/[a-z]/', $nouveau_mdp)) {
        $message = "❌ Le mot de passe doit contenir au moins 12 caractères, une majuscule et une minuscule.";
    } else {
        $email = trim(file_get_contents($tokenPath));
        $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$hash, $email]);

        unlink($tokenPath);
        $message = "✅ Mot de passe mis à jour avec succès. Redirection...";
        $redirect = true;
    }
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="PageAccueil.css">
    <link rel="stylesheet" href="mdp-oublier.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
</head>
<body>

<div class="top-left" onclick="toggleMenu()">
    <img src="images/menu-vert.png" alt="Menu">
</div>

<div id="menu-overlay" class="menu-overlay">
    <div class="menu-content" id="menu-links"></div>
</div>

<div class="top-center">
    <div class="logo-block">
        <a href="PageAccueil.php">
            <img src="images/logo-transparent.png" alt="Logo" style="width: 30px;">
        </a>
        <p class="logo-slogan">Cap'Tivant</p>
        <h1 class="page-title">Réinitialiser votre mot de passe</h1>
    </div>

    <div class="formulaire-connexion" id="confirmation">
        <?php if (!empty($message)): ?>
            <p style="text-align:center; margin-bottom:20px; color:<?= strpos($message, '✅') === 0 ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <?php if (empty($message) || strpos($message, '✅') !== 0): ?>
            <?php if (!empty($email)): ?>
                <p style="text-align:center; margin-bottom:20px;">Réinitialisation pour : <strong><?= htmlspecialchars($email) ?></strong></p>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="champ">
                    <div class="password-container">
                        <input type="password" name="new_password" id="new_password"
                               placeholder="Nouveau mot de passe (12 caractères min, majuscule, minuscule)"
                               required
                               pattern="^(?=.*[a-z])(?=.*[A-Z]).{12,}$"
                               title="12 caractères minimum avec au moins une majuscule et une minuscule">
                        <span toggle="#new_password" class="toggle-password">👁️</span>
                    </div>
                    <p class="password-hint" style="font-size: 0.8em; color: #666; margin-top: 5px;">
                        Le mot de passe doit contenir au moins 12 caractères, une majuscule et une minuscule
                    </p>
                </div>
                <div class="logo-block">
                    <div class="connexion">
                        <button type="submit" style="color: white; background: none; border: none;">Valider</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div style="text-align:center; margin-top:20px;">
                <a href="Connexion.php" class="btn" style="color:white; background-color:#577550; padding:10px 20px; border-radius:10px; text-decoration:none;">Retour à la connexion</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="bouton-bas">
    <a href="MentionsLegales.php" class="bottom-text" style="color: #577550">Mentions légales</a>
    <span class="bottom-text" style="color: #577550">&bull;</span>
    <a href="Contact.php" class="bottom-text" style="color: #577550">Contact</a>
</div>

<?php if (!empty($redirect)): ?>
    <script>
        setTimeout(function () {
            window.location.href = 'Connexion.php';
        }, 3000); // Redirection après 3 secondes
    </script>
<?php endif; ?>
<script>
    // Fonctionnalité œil pour mot de passe
    document.querySelectorAll('.toggle-password').forEach(function(element) {
        element.addEventListener('click', function() {
            const input = document.querySelector(this.getAttribute('toggle'));
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        });
    });

    // Validation en temps réel
    document.getElementById('new_password').addEventListener('input', function(e) {
        const password = e.target.value;
        const isValid = password.length >= 12 && /[A-Z]/.test(password) && /[a-z]/.test(password);

        if (password.length > 0) {
            e.target.style.borderColor = isValid ? 'green' : 'red';
        } else {
            e.target.style.borderColor = '';
        }
    });
</script>
</body>
</html>
