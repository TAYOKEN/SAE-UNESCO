<?php
session_start();

// Supprimer toutes les variables de session
session_unset();

// Détruire la session
session_destroy();

// Supprimer le cookie d'authentification
setcookie('auth_token', '', time() - 3600, '/');

// Rediriger vers la page de index 
header('Location: index.php?message=logged_out');
exit();
?>

