<?php
include '../bootstrap.php';

session_start();
session_destroy();

// Ne pas afficher avant cette ligne
header("Location: Connexion.php");
exit;
?>
