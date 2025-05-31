<?php
// Script de déconnexion de l'utilisateur
// Détruit la session et redirige vers la page de connexion

session_start();
// Supprime toutes les variables de session
$_SESSION = array();
// Détruit la session
session_destroy();
// Redirige vers la page de connexion
header('Location: login.php');
exit;
?>
