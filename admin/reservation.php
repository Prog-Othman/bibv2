<?php
require_once '../bootstrap.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/Database.php';
Database::getInstance();
global $connexion;
include './lang.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/Connexion.php');
    exit();
}

// Vérifier si l'utilisateur est un administrateur
if ($_SESSION['user']['user_role_id'] != 1) {
    header('Location: ../user/dashboard.php');
    exit();
}

// Récupérer les réservations
$query = "SELECT r.*, u.user_nom, u.user_login, l.titre, l.isbn 
          FROM n_reservation r 
          JOIN n_utilisateurs u ON r.id_utilisateur = u.user_id 
          JOIN n_livre l ON r.id_livre = l.id_livre 
          ORDER BY r.date_reservation DESC";
$reservations = $connexion->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['reservation_id'])) {
        $reservation_id = $_POST['reservation_id'];
        $action = $_POST['action'];
        
        switch ($action) {
            case 'approve':
                $connexion->prepare("UPDATE n_reservation SET statut = 'active' WHERE id_reservation = ?")->execute([$reservation_id]);
                break;
            case 'reject':
                $connexion->prepare("UPDATE n_reservation SET statut = 'annulee' WHERE id_reservation = ?")->execute([$reservation_id]);
                break;
            case 'complete':
                $connexion->prepare("UPDATE n_reservation SET statut = 'terminee' WHERE id_reservation = ?")->execute([$reservation_id]);
                break;
        }
        
        // Rediriger pour éviter la soumission multiple du formulaire
        header('Location: reservation.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations - Administration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="content w-100 m-0 pt-5"  id="content">
        <div class="container-fluid p-4">
             <h1>Gestion des Réservations</h1>
        
           <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Livre</th>
                        <th>ISBN</th>
                        <th>Date de Réservation</th>
                        <th>Date d'Expiration</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (
                        $reservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['id_reservation']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['user_nom']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['titre']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['isbn']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($reservation['date_reservation'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($reservation['date_expiration'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($reservation['statut']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $reservation['statut'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($reservation['statut'] === 'en_attente'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id_reservation']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success">Approuver</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id_reservation']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger">Rejeter</button>
                                    </form>
                                <?php elseif ($reservation['statut'] === 'active'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id_reservation']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <button type="submit" class="btn btn-primary">Terminer</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
       </div>
    </div>

</body>
</html> 