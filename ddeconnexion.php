<?php
session_start();
session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600); // Supprime le cookie de session
header('Location: Connexion.php'); // Redirection vers la page de connexion
exit;
?>
