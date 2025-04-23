<?php

require_once __DIR__ . '/../../config/config.php';

require_once '../../config/Database.php';
  session_start();
Database::getInstance();


global $connexion;


$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_livre = $_POST['id_livre'] ?? null;
    $date_reservation = $_POST['date_reservation'] ?? null;

    if ($id_livre && $user_id && $date_reservation) {
        $stmt = $connexion->prepare("INSERT INTO n_reservation (id_livre, id_utilisateur, date_reservation) VALUES (?, ?, ?)");
        $stmt->execute([$id_livre, $user_id, $date_reservation]);



        // Redirection ou message
        header('Location: ../reservation.php?success=1');
        exit;
    } else {
        // Gérer l’erreur
        header('Location: ../reservation.php?error=1');
        exit;
    }
}
?>
