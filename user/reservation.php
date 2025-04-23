<?php
// bootstrap.php (in root folder)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load config and core files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Router.php';
require_once '../config/Database.php';

Database::getInstance();


global $connexion;

// require_once __DIR__ . '/../models/Book.php';



// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Instantiate the router
$router = new Router();

// Check if user is logged in and has the correct role
if (!isset($_SESSION['user']) || $_SESSION['user']['user_role_id'] != 3) {
    // Redirect to login page or show an error
    header('Location: /auth/Connexion.php');
    exit();
}



require_once '../bootstrap.php'; 

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user']['id'];

// Récupérer la liste des catégories
$categories = [];
$query_categories = "SELECT id_categorie ,nom_categorie FROM n_categorie_livres";
$stmt_categories = $connexion->prepare($query_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);


// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Recherche
$titre = $_GET['titre'] ?? '';
$mot_cle = $_GET['mot_cle'] ?? '';
$resume = $_GET['resume'] ?? '';
$isbn = $_GET['isbn'] ?? '';
$id_livre = $_GET['id_livre'] ?? '';
$categoryFilter = $_GET['category'] ?? 0;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$query = "SELECT l.*, c.nom_categorie, aut.*,
         (SELECT COUNT(*) FROM n_exemplaires WHERE id_livre = l.id_livre) as total_copies,
         (SELECT COUNT(*) FROM n_exemplaires e 
          LEFT JOIN n_emprunts em ON e.id_exemplaire = em.id_exemplaire 
          WHERE e.id_livre = l.id_livre AND (em.statut = 'actif' OR em.statut = 'en_retard')) as copies_borrowed
         FROM n_livre l
         LEFT JOIN n_categorie_livres c ON l.category_id = c.id_categorie
         LEFT JOIN n_author aut ON l.author_id = aut.author_id
         WHERE 1=1";

$params = [];

if (!empty($titre)) {
    $query .= " AND l.titre LIKE ?";
    $params[] = "%$titre%";
}
if (!empty($mot_cle)) {
    $query .= " AND l.mots_cle LIKE ?";
    $params[] = "%$mot_cle%";
}
if (!empty($resume)) {
    $query .= " AND l.resume LIKE ?";
    $params[] = "%$resume%";
}
if (!empty($isbn)) {
    $query .= " AND l.isbn LIKE ?";
    $params[] = "%$isbn%";
}
if (!empty($id_livre)) {
    $query .= " AND l.id_livre = ?";
    $params[] = $id_livre;
}
if ($categoryFilter > 0) {
    $query .= " AND l.category_id = ?";
    $params[] = $categoryFilter;
}

// Compte total pour la pagination
$countStmt = $connexion->prepare(str_replace('l.*, c.nom_categorie,', 'COUNT(*) as count,', $query));
$countStmt->execute($params);
$total = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
$totalPages = ceil($total / $limit);

// Ajout de la pagination à la requête principale
$query .= " ORDER BY l.titre LIMIT $limit OFFSET $offset";
$stmt = $connexion->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Récupérer les catégories pour le filtre
$categories = $connexion->query("SELECT * FROM n_categorie_livres ORDER BY nom_categorie")->fetchAll(PDO::FETCH_ASSOC);


// Définir le titre de la page
$page_title = "Tableau de bord";

// Inclure le header et sidebar
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Main Content Area -->
<div class="content w-100 m-0 pt-5"  id="content">
    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Ouvrages</h1>
        </div>
        <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Réservation effectuée avec succès !
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Une erreur est survenue lors de la réservation. Veuillez réessayer.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>

       
<!-- Search and Filter Section -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="titre" placeholder="Titre de l'ouvrage" value="<?php echo htmlspecialchars($titre ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="mot_cle" placeholder="Mot-clé" value="<?php echo htmlspecialchars($mot_cle ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="resume" placeholder="Partie du résumé" value="<?php echo htmlspecialchars($resume ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="isbn" placeholder="ISBN" value="<?php echo htmlspecialchars($isbn ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category">
                        <option value="0">Toutes les catégories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id_categorie']; ?>" <?php echo ($categoryFilter == $category['id_categorie']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['nom_categorie']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </div>
            </form>
            </div>
        </div>

        <!-- Books Table Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-gray-800">Liste Des Ouvrages</h5>
                    <span class="badge bg-primary rounded-pill">
                        <?php echo $total; ?> livre<?php echo $total > 1 ? 's' : ''; ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Titre</th>
                                <th>Auteur</th>
                                <th>Catégorie</th>
                                <th>ISBN</th>
                                <th>Disponibilité</th>
                                <th class="px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($books)): ?>
                                <?php foreach ($books as $book): ?>
                                    <tr>
                                        <td class="px-4 fw-medium">
                                            <?php echo htmlspecialchars($book['titre']); ?>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo htmlspecialchars($book['author_name']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">
                                                <?php echo htmlspecialchars($book['nom_categorie']); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo htmlspecialchars($book['isbn']); ?>
                                        </td>
                                        <td>
                                            <?php
                                            $available = $book['total_copies'] - $book['copies_borrowed'];
                                            $badgeClass = $available > 0 ? 'bg-success' : 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?> bg-opacity-10 text-<?php echo $available > 0 ? 'success' : 'danger'; ?> rounded-pill px-3">
                                                <?php echo $available; ?>/<?php echo $book['total_copies']; ?> disponible<?php echo $available > 1 ? 's' : ''; ?>
                                            </span>
                                        </td>
                                        <td class="px-4">
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#reserver"
                                                    data-id="<?php echo $book['id_livre']; ?>">
                                                    <i class="bi bi-plus-circle"></i>
                                                    <span>Réserver</span>
                                                </button>
                                            
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <?php echo empty($search) ? 'Aucun livre trouvé' : 'Aucun résultat pour votre recherche'; ?>
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
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

     
    </div>
</div>

<!-- Modal de réservation -->
<div class="modal fade" id="reserver" tabindex="-1" aria-labelledby="reserverLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="./traitement/reserver.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reserverLabel">Réserver un ouvrage</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="text" name="id_livre" id="modal_id_livre">

        <div class="mb-3">
          <label for="date_reservation" class="form-label">Date de souhaite</label>
          <input type="date" class="form-control" name="date_reservation" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
      </div>
    </form>
  </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 

<script>
  const reserverModal = document.getElementById('reserver');
  reserverModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const idLivre = button.getAttribute('data-id');
    const inputHidden = document.getElementById('modal_id_livre');
    inputHidden.value = idLivre;
  });
</script>
