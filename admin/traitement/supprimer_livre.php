<?php
require_once '../../config/config.php';
require_once '../../config/Database.php';
Database::getInstance();

global $connexion;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_livre'])) {
    $id = $_POST['id_livre'];

    try {
        // Supprimer les exemplaires liés au livre
        $stmt1 = $connexion->prepare("DELETE FROM n_exemplaires WHERE id_livre = ?");
        $stmt1->execute([$id]);

        // Puis supprimer le livre
        $stmt2 = $connexion->prepare("DELETE FROM n_livre WHERE id_livre = ?");
        $stmt2->execute([$id]);

        header("Location: ../books.php?success=suppression_effectuee");
        exit();
    } catch (Exception $e) {
        error_log("Erreur suppression livre ou exemplaires : " . $e->getMessage());
        header("Location: ../livres.php?error=suppression_echouee");
        exit();
    }
}
?>
