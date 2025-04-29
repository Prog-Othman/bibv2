<?php
require_once '../bootstrap.php';
require_once '../config/config.php';
require_once '../config/Database.php';


Database::getInstance();


global $connexion;
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


// Récupérer la liste des auteurs
$auteurs = [];
$query = "SELECT author_id, author_name FROM n_author WHERE author_status = 'Actif'";
$stmt = $connexion->prepare($query);
$stmt->execute();
$auteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

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


$etudiants = [];

$sql = "SELECT etud_id, concat(etud_nom,' ',etud_prenom)as nom FROM n_etudiants ORDER BY etud_nom ASC";
$stmt = $connexion->prepare($sql);
$stmt->execute();
$etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);


$professeurs = $connexion->query("SELECT prof_id as id, prof_nom as nom FROM prof")->fetchAll();



// Définir le titre de la page
$page_title = "Gestion Des Ouvrages";
include './lang.php';

// Inclure le header et le sidebar
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

?>



<!-- Main Content Area -->
<div class="content w-100 m-0 pt-5" id="content">
    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800"><?= __('manage_books') ?></h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addBookModal">
                    <i class="bi bi-plus-circle"></i> <?= __('add_book') ?>
                </button>
            </div>
        </div>
        <?php if (isset($_GET['success']) && $_GET['success'] === 'modification_effectuee'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                     <?= __('book_modified') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'suppression_effectuee'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= __('book_deleted') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        <?php endif; ?>



        <!-- Search and Filter Section -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="titre" placeholder="<?= __('title') ?>" value="<?php echo htmlspecialchars($titre ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="mot_cle" placeholder="<?= __('keyword') ?>" value="<?php echo htmlspecialchars($mot_cle ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="resume" placeholder="<?= __('resume_part') ?>" value="<?php echo htmlspecialchars($resume ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="isbn" placeholder="<?= __('isbn') ?>" value="<?php echo htmlspecialchars($isbn ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control" name="id_livre" placeholder="<?= __('book_id') ?>" value="<?php echo htmlspecialchars($id_livre ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category">
                        <option value="0"><?= __('all_categories') ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id_categorie']; ?>" <?php echo ($categoryFilter == $category['id_categorie']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['nom_categorie']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary"><?= __('filter') ?></button>
                </div>
            </form>
            </div>
        </div>

        <!-- Books Table Card -->
        <div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-gray-800"><?= __('book_list') ?></h5>
            <span class="badge bg-primary rounded-pill">
                <?= $total ?> <?= __('books') ?><?= $total > 1 ? 's' : '' ?>
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4"><?= __('title') ?></th>
                        <th><?= __('author') ?></th>
                        <th><?= __('category') ?></th>
                        <th><?= __('isbn') ?></th>
                        <th><?= __('available') ?></th>
                        <th class="px-4"><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($books)): ?>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td class="px-4 fw-medium">
                                    <?= htmlspecialchars($book['titre']) ?>
                                </td>
                                <td class="text-muted">
                                    <?= htmlspecialchars($book['author_name']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">
                                        <?= htmlspecialchars($book['nom_categorie']) ?>
                                    </span>
                                </td>
                                <td class="text-muted">
                                    <?= htmlspecialchars($book['isbn']) ?>
                                </td>
                                <td>
                                    <?php
                                    $available = $book['total_copies'] - $book['copies_borrowed'];
                                    $badgeClass = $available > 0 ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass ?> bg-opacity-10 text-<?= $available > 0 ? 'success' : 'danger' ?> rounded-pill px-3">
                                        <?= $available ?>/<?= $book['total_copies'] ?> <?= __('available') ?><?= $available > 1 ? 's' : '' ?>
                                    </span>
                                </td>
                                <td class="px-4">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editBookModal"
                                            data-id="<?= $book['id_livre'] ?>"
                                            data-titre="<?= htmlspecialchars($book['titre']) ?>"
                                            data-auteur="<?= htmlspecialchars($book['author_name']) ?>"
                                            data-isbn="<?= htmlspecialchars($book['isbn']) ?>"
                                            data-categorie="<?= $book['category_id'] ?>">
                                            <i class="bi bi-pencil"></i>
                                            <span><?= __('edit') ?></span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success d-flex align-items-center gap-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addCopyModal"
                                            data-id="<?= $book['id_livre'] ?>"
                                            data-titre="<?= htmlspecialchars($book['titre']) ?>">
                                            <i class="bi bi-plus-circle"></i>
                                            <span><?= __('copy') ?></span>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteBookModal"
                                            data-id="<?= $book['id_livre'] ?>"
                                            data-title="<?= htmlspecialchars($book['titre']) ?>">
                                            <i class="bi bi-trash"></i>
                                            <span><?= __('delete') ?></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <?= empty($search) ? __('no_books_found') : __('no_search_results') ?>
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

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-800"><?= __('add_book') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="traitement/ajout_livre.php" method="POST">
                <div class="modal-body">
                    <!-- Titre du livre -->
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('title') ?></label>
                        <input type="text" class="form-control form-control-lg" name="titre" required>
                    </div>

                    <!-- Auteur -->
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('author') ?></label>
                        <select class="form-select form-select-lg" name="auteur">
                            <option value=""><?= __('Internal_author') ?></option>
                            <?php foreach ($auteurs as $auteur): ?>
                                <option value="<?php echo $auteur['author_id']; ?>">
                                    <?php echo htmlspecialchars($auteur['author_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- ISBN -->
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('isbn') ?></label>
                        <input type="text" class="form-control form-control-lg" name="isbn" pattern="^[0-9\-]{1,14}$" maxlength="14" title="Maximum 14 chiffres ou tirets autorisés">

                    </div>

                    <!-- Date de publication -->
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('date_publication') ?></label>
                        <input type="date" class="form-control form-control-lg" name="date_publication" required>
                    </div>

                    <!-- Quantité totale -->
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('quanti_totale') ?></label>
                        <input type="number" class="form-control form-control-lg" name="quantite_totale" required>
                    </div>

                    <!-- Quantité disponible -->
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('quanti_disponible') ?></label>
                        <input type="number" class="form-control form-control-lg" name="quantite_disponible" required>
                    </div>

                    <!-- Catégorie -->
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('category') ?></label>
                        <select class="form-select form-select-lg" name="categorie" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id_categorie']; ?>">
                                    <?php echo htmlspecialchars($category['nom_categorie']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Entreprise d'accueil (visible si PFE/PFA/PFC) -->
                    <div class="mb-4 d-none" id="entrepriseField">
                        <label class="form-label small fw-medium text-gray-800"><?= __('hosting_company') ?></label>
                        <input type="text" class="form-control form-control-lg" name="entreprise_accueil">
                    </div>

                    <!-- Encadrant interne (visible si PFE/PFA/PFC) -->
                    <div class="mb-4 d-none" id="encadrantField">
                        <label class="form-label small fw-medium text-gray-800"><?= __('internal_supervisor') ?></label>
                        <select class="form-control form-control-lg" name="encadrant_interne">
                            <option value="">-- <?= __('select_supervisor') ?>--</option>
                            <?php foreach ($professeurs as $prof) : ?>
                                <option value="<?= htmlspecialchars($prof['id']) ?>">
                                    <?= htmlspecialchars($prof['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Étudiants ayant écrit le PFE (visible si PFE/PFA/PFC) -->
                    <div class="mb-4 d-none" id="etudiantsField">
                        <label class="form-label small fw-medium text-gray-800"><?= __('students') ?></label>
                        <div id="etudiantsList">
                            <select name="etudiants[]" class="form-select form-select-lg mb-2 etudiant-select">
                                <option value="">--<?=__('choose_student') ?>  --</option>
                                <?php foreach ($etudiants as $etudiant): ?>
                                    <option value="<?php echo $etudiant['etud_id']; ?>">
                                        <?php echo htmlspecialchars($etudiant['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addEtudiantBtn"><?= __('add_another_student') ?></button>
                    </div>



                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('keyword') ?></label>
                        <textarea class="form-control form-control-lg" name="mots_cles" maxlength="250" rows="2" placeholder="Ex: science, roman, aventure..."></textarea>
                        <div class="form-text"><?= __('max_250_characters') ?></div>
                    </div>

                    <!-- Résumé -->
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('resume') ?></label>
                        <textarea class="form-control form-control-lg" name="resume" rows="5" placeholder="<?= __('resume') ?>"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-primary px-4"><?= __('add') ?></button>
                </div>
            </form>

        </div>
    </div>
</div>
<div class="modal fade" id="deleteBookModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-800">Supprimer le livre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form action="traitement/supprimer_livre.php" method="POST">
                <input type="hidden" name="id_livre" id="deleteBookId">
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Êtes-vous sûr de vouloir supprimer le livre :
                        <strong id="deleteBookTitle" class="text-danger"></strong> ?
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger px-4">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Book Modal -->
<div class="modal fade" id="editBookModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-800"><?= __('edit_book') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="traitement/edit_livre.php" method="POST" id="editForm">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('title') ?></label>
                        <input type="text" class="form-control form-control-lg" name="titre" id="editTitre" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('author') ?></label>
                        <input type="text" class="form-control form-control-lg" name="auteur" id="editAuteur">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('isbn') ?></label>
                        <input type="text" class="form-control form-control-lg" name="isbn" id="editIsbn" >
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800"><?= __('category') ?></label>
                        <select class="form-select form-select-lg" name="categorie" id="editCategorie" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id_categorie']; ?>">
                                    <?php echo htmlspecialchars($category['nom_categorie']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-primary px-4"><?= __('save') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Copy Modal -->
<div class="modal fade" id="addCopyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-800"><?= __('add_copy') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="traitement/ajoute_exemplaire.php" method="POST" id="addCopyForm">
                <input type="hidden" name="id_livre" id="copyBookId">

                <div class="modal-body">
                    <p class="text-muted mb-4">
                            <?= __('add_copy_description') ?>
                        <strong id="copyBookTitle"></strong>
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-medium text-gray-800"><?= __('barcode') ?></label>
                        <input type="text" class="form-control form-control-lg" name="code_barre" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium text-gray-800"><?= __('status') ?></label>
                        <select name="statut" class="form-select form-select-lg" required>
                            <option value="disponible" selected><?= __('available') ?></option>
                            <option value="emprunte"><?= __('borrowed') ?></option>
                            <option value="reserve"><?= __('reserved') ?></option>
                            <option value="maintenance"><?= __('maintenance') ?></option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium text-gray-800"><?= __('condition') ?></label>
                        <select name="etat" class="form-select form-select-lg" required>
                            <option value="neuf"><?= __('new') ?></option>
                            <option value="bon" selected><?= __('good') ?></option>
                            <option value="moyen"><?= __('average') ?></option>
                            <option value="mauvais"><?= __('poor') ?></option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium text-gray-800"><?= __('acquisition_date') ?></label>
                        <input type="date" class="form-control form-control-lg" name="date_acquisition" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium text-gray-800"><?= __('last_maintenance_date') ?></label>
                        <input type="date" class="form-control form-control-lg" name="date_derniere_maintenance" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium text-gray-800"><?= __('notes') ?></label>
                        <textarea class="form-control form-control-lg" name="notes" rows="3" placeholder="<?= __('notes_placeholder') ?>"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-primary px-4"><?= __('add') ?></button>
                </div>
            </form>

        </div>
    </div>
</div>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>


    document.addEventListener('DOMContentLoaded', function() {
        // Edit book modal handler
        const editBookModal = document.getElementById('editBookModal');
        if (editBookModal) {
            editBookModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;

                const id = button.getAttribute('data-id');
                const titre = button.getAttribute('data-titre');
                const auteur = button.getAttribute('data-auteur');
                const isbn = button.getAttribute('data-isbn');
                const categorie = button.getAttribute('data-categorie');

                document.getElementById('editId').value = id;
                document.getElementById('editTitre').value = titre;
                document.getElementById('editAuteur').value = auteur;
                document.getElementById('editIsbn').value = isbn;
                document.getElementById('editCategorie').value = categorie;
            });
        }


        // Add copy modal handler
        const addCopyModal = document.getElementById('addCopyModal');
        if (addCopyModal) {
            addCopyModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const titre = button.getAttribute('data-titre');

                addCopyModal.querySelector('#copyBookId').value = id;
                addCopyModal.querySelector('#copyBookTitle').textContent = titre;
            });
        }
    });



    const deleteBookModal = document.getElementById('deleteBookModal');
    if (deleteBookModal) {
        deleteBookModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const title = button.getAttribute('data-title');

            deleteBookModal.querySelector('#deleteBookId').value = id;
            deleteBookModal.querySelector('#deleteBookTitle').textContent = title;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const categorieSelect = document.querySelector('select[name="categorie"]');
        const entrepriseField = document.getElementById('entrepriseField');
        const encadrantField = document.getElementById('encadrantField');

        // Tableau des catégories qui nécessitent les champs supplémentaires
        const categoriesSpeciales = ['PFE', 'PFA', 'PFC'];

        // Fonction pour mettre à jour l'affichage
        function toggleChampsSupp() {
            const selectedText = categorieSelect.options[categorieSelect.selectedIndex].text.toUpperCase();
            if (categoriesSpeciales.includes(selectedText)) {
                entrepriseField.classList.remove('d-none');
                encadrantField.classList.remove('d-none');
            } else {
                entrepriseField.classList.add('d-none');
                encadrantField.classList.add('d-none');
            }
        }

        // Appel initial
        toggleChampsSupp();

        // À chaque changement
        categorieSelect.addEventListener('change', toggleChampsSupp);
    });


    document.addEventListener('DOMContentLoaded', function () {
        const categorieSelect = document.querySelector('select[name="categorie"]');
        const entrepriseField = document.getElementById('entrepriseField');
        const encadrantField = document.getElementById('encadrantField');
        const etudiantsField = document.getElementById('etudiantsField');
        const etudiantsList = document.getElementById('etudiantsList');
        const addEtudiantBtn = document.getElementById('addEtudiantBtn');

        const categoriesSpeciales = ['PFE', 'PFA', 'PFC'];

        function toggleChampsSupp() {
            const selectedText = categorieSelect.options[categorieSelect.selectedIndex].text.toUpperCase();
            const show = categoriesSpeciales.includes(selectedText);
            entrepriseField.classList.toggle('d-none', !show);
            encadrantField.classList.toggle('d-none', !show);
            etudiantsField.classList.toggle('d-none', !show);
        }

        categorieSelect.addEventListener('change', toggleChampsSupp);
        toggleChampsSupp();

        // Initialiser Select2 pour le premier champ
        // $('.etudiant-select').select2({
        //     placeholder: "Choisir un étudiant",
        //     width: '100%'
        // });

        addEtudiantBtn.addEventListener('click', function () {
            const currentFields = etudiantsList.querySelectorAll('select[name="etudiants[]"]');
            if (currentFields.length < 3) {
                // Cloner le premier select et reinitialiser
                const firstSelect = currentFields[0];
                const clone = firstSelect.cloneNode(true);
                clone.selectedIndex = 0;
                clone.classList.add('etudiant-select');

                etudiantsList.appendChild(clone);

                // Réinitialiser Select2 sur tous les nouveaux selects
                // $(clone).select2({
                //     placeholder: "Choisir un étudiant",
                //     width: '100%'
                // });
            } else {
                alert('Maximum 3 étudiants autorisés.');
            }
        });
    });
</script>
