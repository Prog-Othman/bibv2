<?php
require_once '../config/config.php';
require_once '../config/Database.php';

ob_start(); 

Database::getInstance();
global $connexion;



$roleId = $_GET['role_id'] ?? null;

if (!$roleId) {
    echo json_encode([]);
    exit;
}

$demandeurs = [];

switch ($roleId) {
    case 3: // Étudiants
        $sql = "SELECT u.user_id AS id, CONCAT(e.etud_nom, ' ', e.etud_prenom) AS nom FROM n_etudiants e
                JOIN n_utilisateurs u ON e.etud_user_id = u.user_id
                where u.user_bib_status= '0'
                ";
        break;

    case 5: // Enseignants
        $sql = "SELECT u.user_id AS id, CONCAT(p.prof_nom, ' ', p.prof_prenom) AS nom FROM prof p
                JOIN n_utilisateurs u ON p.prof_user_id = u.user_id
                where u.user_bib_status= '0'
                ";
        break;


    default:
        echo json_encode([]);
        exit;
}

$stmt = $connexion->prepare($sql);
$stmt->execute();
$demandeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_end_clean(); 

header('Content-Type: application/json');
echo json_encode($demandeurs);
