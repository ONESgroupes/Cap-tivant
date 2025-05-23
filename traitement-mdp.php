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
    $mail->Subject = 'Reinitialisation de votre mot de passe Cap\'Tivant';
    $mail->Body = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reinitialisation de mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #577550;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header img {
            max-width: 150px;
        }
        .content {
            padding: 30px;
            background-color: #f9f9f9;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #f29066;
            color: white !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="content">
        <h2 style="color: #577550;">Bonjour,</h2>
        <p>Vous avez demande a reinitialiser votre mot de passe pour votre compte Cap\'Tivant.</p>
        <p>Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>
        <p style="text-align: center;">
            <a href="'.$lien.'" class="button">Reinitialiser mon mot de passe</a>
        </p>
        <p>Si vous n\'avez pas demande cette reinitialisation, veuillez ignorer cet email.</p>
        <p>A bientot sur Cap\'Tivant,<br>L\'equipe Cap\'Tivant</p>
        
        <hr style="margin: 30px 0;">

        <h2 style="color: #577550;">Hello,</h2>
        <p>You have requested to reset your password for your Cap\'Tivant account.</p>
        <p>Click the button below to choose a new password:</p>
        <p style="text-align: center;">
            <a href="'.$lien.'" class="button">Reset My Password</a>
        </p>
        <p>If you did not request this reset, please ignore this email.</p>
        <p>See you soon on Cap\'Tivant,<br>The Cap\'Tivant Team</p>
    </div>
    
    <div class="footer">
        <p>
            <a href="https://www.cap-tivant.com" style="color: #577550;">Notre site / Our Website</a> |
            <a href="https://www.cap-tivant.com/contact" style="color: #577550;">Contact</a>
        </p>
    </div>
</body>
</html>';


    // Envoi
    $mail->send();

    // Redirection vers la page avec message
    header("Location: mdp-oublier.php?success=1&email=" . urlencode($email));
    exit();

} catch (Exception $e) {
    header("Location: mdp-oublier.php?error=2");
    exit();
}
