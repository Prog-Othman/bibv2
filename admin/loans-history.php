<?php
require_once '../bootstrap.php';
require_once '../config/config.php';

// Vérifier si l'utilisateur est connecté
// if (!isset($_SESSION['user'])) {
//     header('Location: ../auth/Connexion.php');
//     exit();
// }

// // Vérifier si l'utilisateur est un administrateur
// if ($_SESSION['user']['user_role_id'] != 1) {
//     header('Location: ../user/dashboard.php');
//     exit();
// }

// Connexion à la base de données
$db = new PDO("mysql:host=localhost;dbname=bibliotheque;charset=utf8", "root", "");

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20; // Nombre d'emprunts par page
$offset = ($page - 1) * $limit;

// Récupérer le nombre total d'emprunts
$stmt = $db->query("SELECT COUNT(*) FROM n_emprunts WHERE statut = 'rendu'");
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Liste des emprunts retournés
$stmt = $db->prepare("
    SELECT e.*, l.titre as titre_livre, u.user_nom, u.user_login 
    FROM n_emprunts e 
    JOIN n_exemplaires ex ON e.id_exemplaire = ex.id_exemplaire 
    JOIN n_livre l ON ex.id_livre = l.id_livre 
    JOIN n_utilisateurs u ON e.id_utilisateur = u.user_id 
    WHERE e.statut = 'rendu'
    ORDER BY e.date_retour_effective DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Définir le titre de la page
$page_title = "Historique des emprunts";

// Inclure l'en-tête et le sidebar
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="content w-100 m-0 pt-5"  id="content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 text-dark">Historique des emprunts</h1>
            <a href="loans.php" class="btn btn-primary">Emprunts en cours</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Livre</th>
                            <th>Emprunteur</th>
                            <th>Date d'emprunt</th>
                            <th>Date de retour prévue</th>
                            <th>Date de retour effective</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($loans)): ?>
                            <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($loan['titre_livre']); ?></td>
                                    <td><?php echo htmlspecialchars($loan['user_nom'] . ' (' . $loan['user_login'] . ')'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($loan['date_emprunt'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($loan['date_retour_prevue'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($loan['date_retour_effective'])); ?></td>
                                    <td>
                                        <?php
                                        try {
                                            $returnDate = new DateTime($loan['date_retour_effective']);
                                            $dueDate = new DateTime($loan['date_retour_prevue']);
                                            if ($returnDate > $dueDate) {
                                                echo '<span class="badge bg-warning">Retourné en retard</span>';
                                            } else {
                                                echo '<span class="badge bg-success">Retourné à temps</span>';
                                            }
                                        } catch (Exception $e) {
                                            echo '<span class="badge bg-danger">Erreur de date</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Aucun historique d'emprunt</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
