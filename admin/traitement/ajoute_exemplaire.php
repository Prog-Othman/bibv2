<?php
require_once '../../config/config.php';
require_once '../../config/Database.php';

Database::getInstance();
global $connexion;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérification des champs requis
        if (
            empty($_POST['id_livre']) || empty($_POST['code_barre']) ||
            empty($_POST['statut']) || empty($_POST['etat'])
        ) {
            throw new Exception("Veuillez remplir tous les champs obligatoires.");
        }

        // Récupération des données du formulaire
        $id_livre = $_POST['id_livre'];
        $code_barre = $_POST['code_barre'];
        $statut = $_POST['statut'];
        $etat = $_POST['etat'];
        $date_acquisition = $_POST['date_acquisition'] ?: null;
        $date_maintenance = $_POST['date_derniere_maintenance'] ?: null;
        $notes = $_POST['notes'] ?: null;

        // Vérifier que le livre existe
        $stmtCheck = $connexion->prepare("SELECT COUNT(*) FROM n_livre WHERE id_livre = ?");
        $stmtCheck->execute([$id_livre]);
        if ($stmtCheck->fetchColumn() == 0) {
            throw new Exception("Livre non trouvé.");
        }

        // Préparer l'insertion
        $insert = $connexion->prepare("
            INSERT INTO n_exemplaires (
                id_livre, code_barre, statut, etat, date_acquisition, date_derniere_maintenance, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $insert->execute([
            $id_livre,
            $code_barre,
            $statut,
            $etat,
            $date_acquisition,
            $date_maintenance,
            $notes
        ]);

        // Redirection ou succès
        header("Location: ../books.php?success=exemplaire_ajoute");
        exit();

    } catch (Exception $e) {
        error_log("Erreur ajout exemplaire : " . $e->getMessage());
        echo "Erreur : " . $e->getMessage();
    }
} else {
    echo "Requête invalide.";
}
?>
