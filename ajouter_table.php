<?php
require_once 'config.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

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
        $_SESSION['last_values'] = $_POST;
        header("Location: admin.php");
        exit;
    }

    $columns = array_keys($data);
    $sql = "INSERT INTO `$table` (" . implode(", ", $columns) . ")
            VALUES (" . implode(", ", $placeholders) . ")";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        // ✉️ Envoi de mail si un bateau a été ajouté
        if ($table === 'bateaux') {
            $stmtUsers = $pdo->query("SELECT email, first_name FROM users WHERE newsletter = 1");
            $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as $user) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'paule.rochette2004@gmail.com';
                    $mail->Password = 'cgzteciubsyxsxmv';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('paule.rochette2004@gmail.com', 'Cap\'Tivant');
                    $mail->addAddress($user['email'], $user['first_name']);

                    $mail->isHTML(true);
                    $mail->Subject = '🛥️ Nouveau bateau disponible sur Cap\'Tivant !';
                    $mail->Body = "
                        <p>Bonjour {$user['first_name']},</p>
                        <p>Un nouveau bateau vient d’être ajouté :</p>
                        <ul>
                            <li><strong>Titre :</strong> {$data['titre']}</li>
                            <li><strong>Port :</strong> {$data['port']}</li>
                            <li><strong>Prix :</strong> {$data['prix']} €</li>
                        </ul>
                        <p>Réservez-le dès maintenant sur notre site !</p>
                        <p>À bientôt sur Cap'Tivant ⛵</p>
                    ";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Erreur envoi mail newsletter : " . $mail->ErrorInfo);
                }
            }
        }

        header("Location: admin.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur SQL : " . $e->getMessage();
        $_SESSION['last_table'] = $table;
        $_SESSION['last_values'] = $_POST;
        header("Location: admin.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Requête invalide.";
    header("Location: admin.php");
    exit;
}
