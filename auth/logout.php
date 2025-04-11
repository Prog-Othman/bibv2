<?php
require_once __DIR__ . '/../bootstrap.php';

session_start();

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header('Location: ' . APP_URL . '/auth/Connexion.php');
exit();
?>

