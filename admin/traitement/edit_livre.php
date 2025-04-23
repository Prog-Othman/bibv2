<?php
require_once '../../config/config.php';
require_once '../../config/Database.php';

Database::getInstance();
global $connexion;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
      

        // Récupération des données du formulaire
        $id = $_POST['id'];
        $titre = $_POST['titre'];
        $auteur = $_POST['auteur'];
        $isbn = $_POST['isbn'];
        $categorie = $_POST['categorie'];

        

        // Vérifier que le livre existe
        $checkStmt = $connexion->prepare("SELECT COUNT(*) FROM n_livre WHERE id_livre = ?");
        $checkStmt->execute([$id]);
        if ($checkStmt->fetchColumn() == 0) {
            throw new Exception("Livre non trouvé.");
        }

        // Vérifier que la catégorie existe
        $checkCat = $connexion->prepare("SELECT COUNT(*) FROM n_categorie_livres WHERE id_categorie = ?");
        $checkCat->execute([$categorie]);
        if ($checkCat->fetchColumn() == 0) {
            throw new Exception("Catégorie invalide.");
        }

        // Mettre à jour le livre
        $update = $connexion->prepare("
            UPDATE n_livre
            SET titre = ?, auteur = ?, isbn = ?, category_id = ?
            WHERE id_livre = ?
        ");

        $update->execute([
            $titre,
            $auteur,
            $isbn,
            $categorie,
            $id
        ]);

        // Redirection après succès
        header('Location: ../books.php?success=modification_effectuee');
        exit();

    } catch (Exception $e) {
        error_log("Erreur modification livre : " . $e->getMessage());
        echo "Erreur : " . $e->getMessage();
    }
} else {
    echo "Requête invalide.";
}
?>
