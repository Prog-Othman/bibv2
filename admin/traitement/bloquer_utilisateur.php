<?php
require_once '../../config/config.php';
require_once '../../config/Database.php';

Database::getInstance();
global $connexion;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];

    $bloque = ($action === 'bloquer') ? 1 : 0;

    $sql = "UPDATE n_utilisateurs SET user_bib_status = :bloque WHERE user_id = :id";
    $stmt = $connexion->prepare($sql);
    $stmt->bindValue(':bloque', $bloque, PDO::PARAM_INT);
    $stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    header('Location: ../irregularite.php?bloque=' . $action);
    exit;
}
