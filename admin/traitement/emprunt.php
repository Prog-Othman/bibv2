<?php
// Connexion à la base de données et autres inclusions
require_once '../../config/config.php';
require_once '../../config/Database.php';

Database::getInstance();
global $connexion;

// Traitement du retour d'emprunt
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Vérifier si les données nécessaires sont présentes
        if (!isset($_POST['id']) || !isset($_POST['etat'])) {
            throw new Exception("Les informations du retour sont incomplètes.");
        }

        // Récupérer les données du formulaire
        $idEmprunt = $_POST['id'];
        $etat = $_POST['etat'];
        $commentaire = $_POST['commentaire'] ?? ''; // Commentaire optionnel

        // Vérifier que l'emprunt existe
        $checkQuery = "SELECT * FROM n_emprunts WHERE id_emprunt = ?";
        $stmt = $connexion->prepare($checkQuery);
        $stmt->execute([$idEmprunt]);
        $emprunt = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$emprunt) {
            throw new Exception("L'emprunt n'existe pas.");
        }

        // Mettre à jour les informations de l'emprunt (date de retour effective, statut, et notes)
        $updateQuery = "UPDATE n_emprunts 
                        SET date_retour_effective = NOW(), 
                            statut = ?, 
                            notes = ? 
                        WHERE id_emprunt = ?";
        $updateStmt = $connexion->prepare($updateQuery);
        $result = $updateStmt->execute([$etat,$commentaire,$idEmprunt]);

        

        // Vérifier si la mise à jour a réussi
        if (!$result) {
            throw new Exception("Erreur lors de la mise à jour de l'emprunt.");
        }

        if (strtolower($etat) === 'rendu') {
            $idExemplaire = $emprunt['id_exemplaire'];
            $updateExemplaireQuery = "UPDATE n_exemplaires SET statut = 'disponible' WHERE id_exemplaire = ?";
            $stmtUpdateEx = $connexion->prepare($updateExemplaireQuery);
            $stmtUpdateEx->execute([$idExemplaire]);
        }
        // Rediriger vers la page des emprunts après la mise à jour
        header('Location: ../loans.php');
        exit();
    } catch (Exception $e) {
        // Gérer les erreurs
        $error = $e->getMessage();
        error_log("Error processing return: " . $error);
        echo "Erreur : " . $error;
    }
}
?>
