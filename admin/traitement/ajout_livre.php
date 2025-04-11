<?php
require_once '../../config/config.php';
require_once '../../config/Database.php';

Database::getInstance();

// Connexion PDO
global $connexion;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Vérifier que tous les champs requis sont présents
        if (!isset($_POST['titre']) || !isset($_POST['auteur']) || !isset($_POST['isbn']) || !isset($_POST['categorie']) || !isset($_POST['quantite_totale']) || !isset($_POST['quantite_disponible'])) {
            throw new Exception("Tous les champs sont requis");
        }

        // Récupérer les données du formulaire
        $titre = $_POST['titre'];
        $auteur = $_POST['auteur'];
        $isbn = $_POST['isbn'];
        $date_publication = $_POST['date_publication'] ?: null; // Si vide, null
        $quantite_totale = $_POST['quantite_totale'];
        $quantite_disponible = $_POST['quantite_disponible'];
        $categorie = $_POST['categorie'];
        $mots_cles = $_POST['mots_cles'] ?? '';
        $resume = $_POST['resume'] ?? '';

        $author_name ='NULL';
        // Vérifier si la catégorie existe dans la table n_categorie_livres
        $categoryCheckQuery = "SELECT COUNT(*) FROM n_categorie_livres WHERE id_categorie = ?";
        $stmt = $connexion->prepare($categoryCheckQuery);
        $stmt->execute([$categorie]);
        $categoryExists = $stmt->fetchColumn();

        if (!$categoryExists) {
            throw new Exception("La catégorie sélectionnée n'existe pas.");
        }

        // Insérer le nouveau livre dans la table n_livre
        $insertQuery = "INSERT INTO n_livre (titre, author_id, isbn, date_publication, quantite_totale, quantite_disponible, category_id, mots_cle, resume,auteur) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
        $insertStmt = $connexion->prepare($insertQuery);
        $result = $insertStmt->execute([
            $titre,
            $auteur,
            $isbn,
            $date_publication,
            $quantite_totale,
            $quantite_disponible,
            $categorie,
            $mots_cles,
            $resume,
            $author_name
        ]);

        // Vérifier le résultat de l'insertion
        if (!$result) {
            throw new Exception("Erreur lors de l'ajout du livre");
        }

        // Rediriger vers la page des livres après l'ajout
        header('Location: ../books.php');
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Error adding book: " . $error);
        echo "Erreur : " . $error;
    }
}
?>
