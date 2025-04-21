<?php
require_once '../bootstrap.php';
require_once '../config/config.php';

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

// Connexion à la base de données
$db = new PDO("mysql:host=localhost;dbname=bibliotheque;charset=utf8", "root", "");

// Récupérer les statistiques générales
$stats = [
    'total_users' => $db->query("SELECT COUNT(*) FROM n_utilisateurs")->fetchColumn(),
    'total_books' => $db->query("SELECT COUNT(*) FROM n_livre")->fetchColumn(),
    'active_loans' => $db->query("SELECT COUNT(*) FROM n_emprunts WHERE statut = 'actif'")->fetchColumn(),
    'overdue_loans' => $db->query("SELECT COUNT(*) FROM n_emprunts WHERE statut = 'en_retard'")->fetchColumn()
];

// Récupérer les derniers emprunts
$query = $db->query("
    SELECT e.*, u.user_nom, u.user_login, l.titre
    FROM n_emprunts e
    JOIN n_utilisateurs u ON e.id_utilisateur = u.user_id
    JOIN n_exemplaires ex ON e.id_exemplaire = ex.id_exemplaire
    JOIN n_livre l ON ex.id_livre = l.id_livre
    ORDER BY e.date_emprunt DESC
    LIMIT 10
");
$recent_loans = $query->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les utilisateurs récemment actifs
$query = $db->query("
    SELECT user_id, user_nom, user_login, user_dern_conx
    FROM n_utilisateurs
    WHERE user_dern_conx IS NOT NULL
    ORDER BY user_dern_conx DESC
    LIMIT 10
");
$active_users = $query->fetchAll(PDO::FETCH_ASSOC);

// Définir le titre de la page
$page_title = "Administration";
?>

<?php
// Inclure le sidebar
require_once '../includes/header.php';
?>

<?php
// Inclure le sidebar
require_once '../includes/sidebar.php';
?>


<!-- Main Content Area -->
<div class="content w-100 m-0 pt-5"  id="content">
    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Tableau de bord Administrateur</h1>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary d-flex align-items-center gap-2" id="printBtn">
                    <i class="bi bi-printer"></i> Imprimer
                </button>
                <button class="btn btn-outline-primary d-flex align-items-center gap-2" id="exportBtn">
                    <i class="bi bi-download"></i> Exporter
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <!-- Books Card -->
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title text-primary mb-1">Livres</h5>
                                <h2 class="display-6 mb-0 fw-bold"><?php echo $stats['total_books']; ?></h2>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-book text-primary fs-4"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-3">Livres disponibles dans la bibliothèque</p>
                        <a href="<?php echo APP_URL; ?>/admin/books.php" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-2 w-100 justify-content-center">
                            <i class="bi bi-eye"></i> Voir tous
                        </a>
                    </div>
                </div>
            </div>

            <!-- Active Loans Card -->
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title text-success mb-1">Emprunts actifs</h5>
                                <h2 class="display-6 mb-0 fw-bold"><?php echo $stats['active_loans']; ?></h2>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="bi bi-bookmark-check text-success fs-4"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-3">Livres actuellement empruntés</p>
                        <a href="<?php echo APP_URL; ?>/admin/loans.php?status=active" class="btn btn-sm btn-outline-success d-flex align-items-center gap-2 w-100 justify-content-center">
                            <i class="bi bi-eye"></i> Voir tous
                        </a>
                    </div>
                </div>
            </div>

            <!-- Overdue Card -->
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title text-danger mb-1">Retards</h5>
                                <h2 class="display-6 mb-0 fw-bold"><?php echo $stats['overdue_loans']; ?></h2>
                            </div>
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                                <i class="bi bi-exclamation-circle text-danger fs-4"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-3">Emprunts non retournés à temps</p>
                        <a href="<?php echo APP_URL; ?>/admin/loans.php?status=overdue" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-2 w-100 justify-content-center">
                            <i class="bi bi-exclamation-circle"></i> Gérer les retards
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="row g-4">
            <!-- Recent Loans -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-gray-800">Emprunts récents</h5>
                            <a href="<?php echo APP_URL; ?>/admin/loans.php" class="btn btn-sm btn-outline-primary">
                                Voir tous
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4">Livre</th>
                                        <th>Date d'emprunt</th>
                                        <th>Date de retour</th>
                                        <th class="px-4">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_loans)): ?>
                                        <?php foreach ($recent_loans as $loan): ?>
                                            <?php 
                                                $statusClass = 'bg-secondary';
                                                switch ($loan['statut']) {
                                                    case 'actif':
                                                        $statusClass = 'bg-success';
                                                        break;
                                                    case 'en_retard':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                    case 'termine':
                                                        $statusClass = 'bg-primary';
                                                        break;
                                                }
                                            ?>
                                            <tr>
                                                <td class="px-4"><?php echo htmlspecialchars($loan['titre']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($loan['date_emprunt'])); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($loan['date_retour'])); ?></td>
                                                <td class="px-4">
                                                    <span class="badge <?php echo $statusClass; ?> rounded-pill">
                                                        <?php echo ucfirst($loan['statut']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">Aucun emprunt récent</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-gray-800">Utilisateurs récents</h5>
                            <a href="<?php echo APP_URL; ?>/admin/users.php" class="btn btn-sm btn-outline-primary">
                                Voir tous
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4">Utilisateur</th>
                                        <th class="px-4">Dernière connexion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($active_users)): ?>
                                        <?php foreach ($active_users as $user): ?>
                                            <tr>
                                                <td class="px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" 
                                                             style="width: 40px; height: 40px;">
                                                            <span class="text-primary fw-bold">
                                                                <?php echo strtoupper(substr($user['user_nom'], 0, 1)); ?>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium"><?php echo htmlspecialchars($user['user_nom']); ?></div>
                                                            <div class="small text-muted"><?php echo htmlspecialchars($user['user_id']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4">
                                                    <div class="small text-muted">
                                                        <?php echo date('d/m/Y H:i', strtotime($user['user_dern_conx'])); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center py-4 text-muted">Aucun utilisateur récent</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Print functionality
    document.getElementById('printBtn').addEventListener('click', function() {
        window.print();
    });
    
    // Export functionality (simple CSV example)
    document.getElementById('exportBtn').addEventListener('click', function() {
        window.location.href = '<?php echo APP_URL; ?>/admin/export_dashboard.php';
    });
});
</script>