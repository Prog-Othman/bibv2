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

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtres
$role = isset($_GET['role']) ? $_GET['role'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construction de la requête
$query = "SELECT u.*, r.nom_role, 
          (SELECT COUNT(*) FROM n_emprunts WHERE id_utilisateur = u.user_id AND statut = 'actif') as active_loans,
          (SELECT COUNT(*) FROM n_emprunts WHERE id_utilisateur = u.user_id) as total_loans
          FROM n_utilisateurs u
          LEFT JOIN n_role r ON u.user_role_id = r.id_role
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (u.user_nom LIKE ? OR u.user_login LIKE ? OR u.user_email LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

if ($role !== 'all') {
    $query .= " AND u.user_role_id = ?";
    $params[] = $role;
}

if ($status !== 'all') {
    $query .= " AND u.user_status = ?";
    $params[] = $status;
}

// Compte total pour la pagination
$countStmt = $db->prepare(str_replace('u.*, r.nom_role', 'COUNT(*) as count', $query));
$countStmt->execute($params);
$total = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
$totalPages = ceil($total / $limit);

// Ajout de la pagination et du tri à la requête principale
$query .= " ORDER BY u.user_id DESC LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques des utilisateurs
$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM n_utilisateurs")->fetchColumn(),
    'actif' => $db->query("SELECT COUNT(*) FROM n_utilisateurs WHERE user_estActif = 'actif'")->fetchColumn(),
    'inactif' => $db->query("SELECT COUNT(*) FROM n_utilisateurs WHERE user_estActif = 'inactif'")->fetchColumn(),
    'admin' => $db->query("SELECT COUNT(*) FROM n_utilisateurs WHERE user_role_id = 1")->fetchColumn()
];

// Récupérer la liste des rôles
$roles = $db->query("SELECT * FROM n_role ORDER BY id_role")->fetchAll(PDO::FETCH_ASSOC);

// Définir le titre de la page
$page_title = "Gestion des utilisateurs";

// Inclure le header et le sidebar
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Main Content Area -->
<div class="content w-100 m-0 pt-5"  id="content">
    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Gestion des utilisateurs</h1>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <!-- Total Users -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title text-primary mb-1">Total</h5>
                                <h2 class="display-6 mb-0 fw-bold"><?php echo $stats['total']; ?></h2>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                                <i class="bi bi-people text-primary fs-4"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-0">Utilisateurs au total</p>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title text-success mb-1">Actifs</h5>
                                <h2 class="display-6 mb-0 fw-bold"><?php echo $stats['actif']; ?></h2>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3">
                                <i class="bi bi-person-check text-success fs-4"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-0">Utilisateurs actifs</p>
                    </div>
                </div>
            </div>

            <!-- Inactive Users -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title text-danger mb-1">Inactifs</h5>
                                <h2 class="display-6 mb-0 fw-bold"><?php echo $stats['inactif']; ?></h2>
                            </div>
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                                <i class="bi bi-person-x text-danger fs-4"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-0">Utilisateurs inactifs</p>
                    </div>
                </div>
            </div>

            <!-- Admin Users -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title text-info mb-1">Admins</h5>
                                <h2 class="display-6 mb-0 fw-bold"><?php echo $stats['admin']; ?></h2>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3">
                                <i class="bi bi-shield-check text-info fs-4"></i>
                            </div>
                        </div>
                        <p class="text-muted mb-0">Administrateurs</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" name="search" 
                                   placeholder="Rechercher par nom, identifiant ou email..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <select class="form-select" name="role">
                            <option value="all" <?php echo $role === 'all' ? 'selected' : ''; ?>>Tous les rôles</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?php echo $r['id_role']; ?>" <?php echo $role == $r['id_role'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($r['nom_role']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <select class="form-select" name="status">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Tous les statuts</option>
                            <option value="actif" <?php echo $status === 'actif' ? 'selected' : ''; ?>>Actif</option>
                            <option value="inactif" <?php echo $status === 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-gray-800">Liste des utilisateurs</h5>
                    <span class="badge bg-primary rounded-pill">
                        <?php echo $total; ?> utilisateur<?php echo $total > 1 ? 's' : ''; ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Utilisateur</th>
                                <th>Identifiant</th>
                                <th>Emprunts</th>
                                <th>Statut</th>
                                <th>Date inscription</th>
                                <th class="px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
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
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo htmlspecialchars($user['nom_role']); ?>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-primary rounded-pill">
                                                    <?php echo $user['active_loans']; ?> actif<?php echo $user['active_loans'] > 1 ? 's' : ''; ?>
                                                </span>
                                                <span class="small text-muted">
                                                    / <?php echo $user['total_loans']; ?> total
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = $user['user_estActif'] === 'actif' ? 'bg-success' : 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?> bg-opacity-10 text-<?php echo str_replace('bg-', '', $statusClass); ?> rounded-pill px-3">
                                                <?php echo ucfirst($user['user_estActif']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y'); ?>
                                        </td>
                                        <td class="px-4">
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editUserModal"
                                                        data-id="<?php echo $user['user_id']; ?>"
                                                        data-nom="<?php echo htmlspecialchars($user['user_nom']); ?>"
                                                        data-login="<?php echo htmlspecialchars($user['user_login']); ?>"
                                                        data-email="<?php echo htmlspecialchars($user['user_login']); ?>"
                                                        data-role="<?php echo $user['user_role_id']; ?>"
                                                        data-status="<?php echo $user['user_estActif']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                    <span>Modifier</span>
                                                </button>
                                                <?php if ($user['user_login'] != $_SESSION['user']['login']): ?>
                                                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteUserModal"
                                                            data-id="<?php echo $user['user_login']; ?>"
                                                            data-nom="<?php echo htmlspecialchars($user['user_nom']); ?>">
                                                        <i class="bi bi-trash"></i>
                                                        <span>Supprimer</span>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <?php echo empty($search) ? 'Aucun utilisateur trouvé' : 'Aucun résultat pour votre recherche'; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role; ?>&status=<?php echo $status; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role; ?>&status=<?php echo $status; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role; ?>&status=<?php echo $status; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- New User Modal -->
<div class="modal fade" id="newUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-800">Nouvel utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=add" method="POST">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Nom complet</label>
                        <input type="text" class="form-control form-control-lg" name="nom" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Identifiant</label>
                        <input type="text" class="form-control form-control-lg" name="login" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Email</label>
                        <input type="email" class="form-control form-control-lg" name="email" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Mot de passe</label>
                        <input type="password" class="form-control form-control-lg" name="password" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Rôle</label>
                        <select class="form-select form-select-lg" name="role" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?php echo $r['role_id']; ?>">
                                    <?php echo htmlspecialchars($r['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-800">Modifier l'utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=edit" method="POST">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Nom complet</label>
                        <input type="text" class="form-control form-control-lg" name="nom" id="editNom" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Identifiant</label>
                        <input type="text" class="form-control form-control-lg" name="login" id="editLogin" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Email</label>
                        <input type="email" class="form-control form-control-lg" name="email" id="editEmail" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Nouveau mot de passe</label>
                        <input type="password" class="form-control form-control-lg" name="password" placeholder="Laisser vide pour ne pas modifier">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Rôle</label>
                        <select class="form-select form-select-lg" name="role" id="editRole" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?php echo $r['id_role']; ?>">
                                    <?php echo htmlspecialchars($r['nom_role']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Statut</label>
                        <select class="form-select form-select-lg" name="status" id="editStatus" required>
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-800">Supprimer l'utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=delete" method="POST">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-body">
                    <p class="text-muted mb-0">Êtes-vous sûr de vouloir supprimer l'utilisateur : <strong id="deleteNom"></strong> ?</p>
                    <p class="text-danger small mt-2 mb-0">Cette action est irréversible.</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger px-4">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit user modal handler
    const editModal = document.getElementById('editUserModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nom = button.getAttribute('data-nom');
            const login = button.getAttribute('data-login');
            const email = button.getAttribute('data-email');
            const role = button.getAttribute('data-role');
            const status = button.getAttribute('data-status');

            editModal.querySelector('#editId').value = id;
            editModal.querySelector('#editNom').value = nom;
            editModal.querySelector('#editLogin').value = login;
            editModal.querySelector('#editEmail').value = email;
            editModal.querySelector('#editRole').value = role;
            editModal.querySelector('#editStatus').value = status;
        });
    }

    // Delete user modal handler
    const deleteModal = document.getElementById('deleteUserModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nom = button.getAttribute('data-nom');

            deleteModal.querySelector('#deleteId').value = id;
            deleteModal.querySelector('#deleteNom').textContent = nom;
        });
    }
});
</script>
