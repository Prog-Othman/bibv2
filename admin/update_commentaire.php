<?php
require_once '../config/config.php';
require_once '../config/Database.php';

Database::getInstance();
global $connexion;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_emprunt'])) {
    $id_emprunt = $_POST['id_emprunt'];
    $notes = $_POST['notes'];

    $sql = "UPDATE n_emprunts SET notes = :notes WHERE id_emprunt = :id_emprunt";
    $stmt = $connexion->prepare($sql);
    $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
    $stmt->bindValue(':id_emprunt', $id_emprunt, PDO::PARAM_INT);
    $stmt->execute();
}

header("Location: irrégularités.php");
exit();
