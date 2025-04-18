<?php
ob_start();

require_once '../config/config.php';
require_once '../config/Database.php';

Database::getInstance();
global $connexion;

try {
    $roleId = $_GET['role_id'] ?? null;

    if (!$roleId) {
        echo json_encode([]);
        exit;
    }

    $demandeurs = [];

    switch ($roleId) {
        case 3: // Étudiants
            $sql = "SELECT u.user_id AS id, CONCAT(e.etud_nom, ' ', e.etud_prenom) AS nom 
                    FROM n_etudiants e
                    JOIN n_utilisateurs u ON e.etud_user_id = u.user_id
                    WHERE u.user_bib_status = '0'";
            break;

        case 5: // Enseignants
            $sql = "SELECT u.user_id AS id, CONCAT(p.prof_nom, ' ', p.prof_prenom) AS nom 
                    FROM prof p
                    JOIN n_utilisateurs u ON p.prof_user_id = u.user_id
                    WHERE u.user_bib_status = '0'";
            break;

        default:
            echo json_encode([]);
            exit;
    }

    $stmt = $connexion->prepare($sql);
    $stmt->execute();
    $demandeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_end_clean(); // Nettoie le tampon avant d’envoyer les headers
    header('Content-Type: application/json');
    echo json_encode($demandeurs);
} catch (PDOException $e) {
    ob_end_clean(); // Nettoie tout avant d’envoyer l’erreur
    error_log("Erreur PDO dans get_utilisateurs_by_role.php : " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données.']);
} catch (Exception $e) {
    ob_end_clean();
    error_log("Erreur générale dans get_utilisateurs_by_role.php : " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur est survenue.']);
}
