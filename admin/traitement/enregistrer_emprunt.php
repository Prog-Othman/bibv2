<?php
require_once '../../config/config.php';
require_once '../../config/Database.php';

Database::getInstance();

// Connexion PDO
global $connexion;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les valeurs du formulaire
    $id_utilisateur = $_POST['id_utilisateur'];
    $id_exemplaire = $_POST['id_exemplaire'];
    $date_emprunt = $_POST['date_emprunt'] ?: null; // Si vide, ce sera null
    $date_retour_prevue = $_POST['date_retour_prevue'];
    $date_retour_effective = $_POST['date_retour_effective'] ?: null; // Si vide, ce sera null
    $statut = $_POST['statut'];
    $notes = $_POST['notes'];

    // Préparer la requête SQL pour l'insertion dans la table n_emprunts
    $sql = "INSERT INTO n_emprunts (id_utilisateur, id_exemplaire, date_emprunt, date_retour_prevue, date_retour_effective, statut, notes)
            VALUES (:id_utilisateur, :id_exemplaire, :date_emprunt, :date_retour_prevue, :date_retour_effective, :statut, :notes)";

    // Préparer la requête avec des paramètres
    $stmt = $connexion->prepare($sql);

    // Lier les paramètres
    $stmt->bindValue(':id_utilisateur', $id_utilisateur, PDO::PARAM_INT);
    $stmt->bindValue(':id_exemplaire', $id_exemplaire, PDO::PARAM_INT);
    $stmt->bindValue(':date_emprunt', $date_emprunt ? $date_emprunt : null, PDO::PARAM_STR);
    $stmt->bindValue(':date_retour_prevue', $date_retour_prevue, PDO::PARAM_STR);
    $stmt->bindValue(':date_retour_effective', $date_retour_effective ? $date_retour_effective : null, PDO::PARAM_STR);
    $stmt->bindValue(':statut', $statut, PDO::PARAM_STR);
    $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);

    // Exécuter la requête
    if ($stmt->execute()) {
        header('Location: ../loans.php');
        exit(); // Assurez-vous que le script s'arrête après la redirection
    } else {
        echo "Erreur lors de l'enregistrement de l'emprunt: " . $stmt->errorInfo()[2];
    }
}
?>
