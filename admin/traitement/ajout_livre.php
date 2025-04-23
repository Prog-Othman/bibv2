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
        $isbn = $_POST['isbn'];
        $date_publication = $_POST['date_publication'] ?: null; 
        $quantite_totale = $_POST['quantite_totale'];
        $quantite_disponible = $_POST['quantite_disponible'];
        $categorie = $_POST['categorie'];
        $mots_cles = $_POST['mots_cles'] ?? '';
        $resume = $_POST['resume'] ?? '';
        $author_name ='NULL';

        $auteur = isset($_POST['auteur']) && trim($_POST['auteur']) !== '' ? $_POST['auteur'] : null;

        $entreprise_accueil = !empty($_POST['entreprise_accueil']) ? $_POST['entreprise_accueil'] : null;
        $encadrant_interne  = !empty($_POST['encadrant_interne']) ? $_POST['encadrant_interne'] : null;
        $etudiants = [
            !empty($_POST['etudiants'][0]) ? $_POST['etudiants'][0] : null,
            !empty($_POST['etudiants'][1]) ? $_POST['etudiants'][1] : null,
            !empty($_POST['etudiants'][2]) ? $_POST['etudiants'][2] : null,
        ];


        // Vérifier si la catégorie existe dans la table n_categorie_livres
        $categoryCheckQuery = "SELECT COUNT(*) FROM n_categorie_livres WHERE id_categorie = ?";
        $stmt = $connexion->prepare($categoryCheckQuery);
        $stmt->execute([$categorie]);
        $categoryExists = $stmt->fetchColumn();

        if (!$categoryExists) {
            throw new Exception("La catégorie sélectionnée n'existe pas.");
        }

        // Insérer le nouveau livre dans la table n_livre
        $insertQuery = "INSERT INTO n_livre (titre, author_id, isbn, date_publication, quantite_totale, quantite_disponible, category_id, mots_cle, resume,auteur,entreprise_accueil,encadrant_interne,etudiants_premier,etudiants_deuxieme,etudiants_troisieme) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?)";
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
            $author_name,
            $entreprise_accueil,
            $encadrant_interne,
            $etudiants['0'],
            $etudiants['1'],
            $etudiants['2']
        ]);

       
            $idLivre = $connexion->lastInsertId();

            
            for ($i = 1; $i <= $quantite_disponible; $i++) {
                $code_barre = uniqid("EX_"); 

                $insertExemplaireQuery = "INSERT INTO n_exemplaires (id_livre, code_barre, statut, etat, date_acquisition) 
                                        VALUES (?, ?, 'disponible', 'bon', NOW())";
                $stmtExemplaire = $connexion->prepare($insertExemplaireQuery);
                $stmtExemplaire->execute([$idLivre, $code_barre]);
            }

 
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
