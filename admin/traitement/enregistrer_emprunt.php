<?php
require_once '../../config/config.php';
require_once '../../config/Database.php';

Database::getInstance();
global $connexion;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Type d'emprunteur
    $type_emprunteur = $_POST['type_emprunteur'];

    // Champs communs
    $id_exemplaire = $_POST['id_exemplaire'];
    $date_emprunt = $_POST['date_emprunt'] ?: null;
    $date_retour_prevue = $_POST['date_retour_prevue'];
    $date_retour_effective = $_POST['date_retour_effective'] ?: null;
    $statut = $_POST['statut'];
    $notes = $_POST['notes'];
    $utilisateur = isset($_POST['id_utilisateur']) && !empty($_POST['id_utilisateur'])? $_POST['id_utilisateur']: null;


   
        // Externe
        $nom_externe = $_POST['nom_externe'];
        $tel_externe = $_POST['tel_externe'];
        $email_externe = $_POST['email_externe'];
        $identite_externe = $_POST['identite_externe'];

        $sql = "INSERT INTO n_emprunts (
                    id_utilisateur, id_exemplaire, date_emprunt, date_retour_prevue, 
                    date_retour_effective, statut, notes, 
                    nom_externe, tel_externe, email_externe, identite_externe
                ) VALUES (
                    :utilisateur, :id_exemplaire, :date_emprunt, :date_retour_prevue, 
                    :date_retour_effective, :statut, :notes,
                    :nom_externe, :tel_externe, :email_externe, :identite_externe
                )";

        $stmt = $connexion->prepare($sql);
        $stmt->bindValue(':utilisateur', $utilisateur, PDO::PARAM_INT);

        $stmt->bindValue(':id_exemplaire', $id_exemplaire, PDO::PARAM_INT);
        $stmt->bindValue(':date_emprunt', $date_emprunt, PDO::PARAM_STR);
        $stmt->bindValue(':date_retour_prevue', $date_retour_prevue, PDO::PARAM_STR);
        $stmt->bindValue(':date_retour_effective', $date_retour_effective, PDO::PARAM_STR);
        $stmt->bindValue(':statut', $statut, PDO::PARAM_STR);
        $stmt->bindValue(':notes', $notes, PDO::PARAM_STR);
        $stmt->bindValue(':nom_externe', $nom_externe, PDO::PARAM_STR);
        $stmt->bindValue(':tel_externe', $tel_externe, PDO::PARAM_STR);
        $stmt->bindValue(':email_externe', $email_externe, PDO::PARAM_STR);
        $stmt->bindValue(':identite_externe', $identite_externe, PDO::PARAM_STR);
    

    // Exécution
    if ($stmt->execute()) { 
        // Mise à jour du statut de l'exemplaire à "emprunte"
        $updateExemplaire = "UPDATE n_exemplaires SET statut = 'emprunte' WHERE id_exemplaire = :id_exemplaire";
        $stmtUpdate = $connexion->prepare($updateExemplaire);
        $stmtUpdate->bindValue(':id_exemplaire', $id_exemplaire, PDO::PARAM_INT);
        $stmtUpdate->execute();

        header('Location: ../loans.php');
        exit();
    } else {
        echo "Erreur lors de l'enregistrement de l'emprunt : " . $stmt->errorInfo()[2];
    }

  
}
?>
