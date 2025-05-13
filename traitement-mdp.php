<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$email = $_POST['email'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: mdp-oublier.php?error=1");
    exit();
}

$mail = new PHPMailer(true);

try {
    // Config SMTP Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'paule.rochette2004@gmail.com';
    $mail->Password   = 'cgzteciubsyxsxmv';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Expéditeur et destinataire
    $mail->setFrom('paule.rochette2004@gmail.com', 'CapTivant');
    $mail->addAddress($email);

    // Création du token
    $token = bin2hex(random_bytes(32));

    // Dossier tokens
    $dossier = __DIR__ . '/tokens/';
    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }

    // Enregistrement du token
    file_put_contents($dossier . $token . '.txt', $email);

    // Lien de réinitialisation
    $lien = "http://localhost/app/reinitialisation.php?token=$token";

    // Contenu du mail
    $mail->isHTML(true);
    $mail->Subject = '🔐 Réinitialisation de votre mot de passe';
    $mail->Body = "
        Bonjour,<br><br>
        Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe :<br><br>
        <a href='$lien'>$lien</a><br><br>
        Ce lien est valable une seule fois.";

    // Envoi
    $mail->send();

    // Redirection vers la page avec message
    header("Location: mdp-oublier.php?success=1&email=" . urlencode($email));
    exit();

} catch (Exception $e) {
    header("Location: mdp-oublier.php?error=2");
    exit();
}
