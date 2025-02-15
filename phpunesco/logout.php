<?php
session_start();  // Démarre ou reprend la session
session_destroy();  // Détruit la session
header('Location: login.php');  // Redirige l'utilisateur vers la page de connexion
exit();
?>
