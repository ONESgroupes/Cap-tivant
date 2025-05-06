<?php
require 'config.php';

$token = $_POST['token'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

$tokenFile = __DIR__ . "/tokens/$token.txt";

if (!file_exists($tokenFile)) {
    exit("❌ Lien invalide.");
}

$email = trim(file_get_contents($tokenFile));
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

// ✅ Le champ s'appelle 'password_hash' dans ta table
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->execute([$hash, $email]);

unlink($tokenFile); // sécurité
echo "✅ Mot de passe mis à jour avec succès.";
?>
