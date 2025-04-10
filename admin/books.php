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

// Traitement de l'ajout d'un nouveau livre
if (isset($_GET['action']) && $_GET['action'] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérifier que tous les champs requis sont présents
        if (!isset($_POST['titre']) || !isset($_POST['auteur']) || !isset($_POST['isbn']) || !isset($_POST['categorie'])) {
            throw new Exception("Tous les champs sont requis");
        }

        // Insérer le nouveau livre
        $insertQuery = "INSERT INTO n_livre (titre, auteur, isbn, id_categorie) 
                       VALUES (?, ?, ?, ?)";
        $insertStmt = $db->prepare($insertQuery);
        $result = $insertStmt->execute([
            $_POST['titre'],
            $_POST['auteur'],
            $_POST['isbn'],
            $_POST['categorie']
        ]);

        // Vérifier le résultat de l'insertion
        if (!$result) {
            throw new Exception("Erreur lors de l'ajout du livre");
        }

        // Rediriger vers la même page pour voir le nouveau livre
        header('Location: books.php');
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log("Error adding book: " . $error);
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Construction de la requête
$query = "SELECT l.*, c.nom_categorie, 
         (SELECT COUNT(*) FROM n_exemplaires WHERE id_livre = l.id_livre) as total_copies,
         (SELECT COUNT(*) FROM n_exemplaires e 
          LEFT JOIN n_emprunts em ON e.id_exemplaire = em.id_exemplaire 
          WHERE e.id_livre = l.id_livre AND (em.statut = 'actif' OR em.statut = 'en_retard')) as copies_borrowed
         FROM n_livre l
         LEFT JOIN n_categorie_livres c ON l.id_categorie = c.id_categorie
         WHERE 1=1";

$params = [];
if (!empty($search)) {
    $query .= " AND (l.titre LIKE ? OR l.auteur LIKE ? OR l.isbn LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

if ($categoryFilter > 0) {
    $query .= " AND l.id_categorie = ?";
    $params[] = $categoryFilter;
}

// Compte total pour la pagination
$countStmt = $db->prepare(str_replace('l.*, c.nom_categorie,', 'COUNT(*) as count,', $query));
$countStmt->execute($params);
$total = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
$totalPages = ceil($total / $limit);

// Ajout de la pagination à la requête principale
$query .= " ORDER BY l.titre LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les catégories pour le filtre
$categories = $db->query("SELECT * FROM n_categorie_livres ORDER BY nom_categorie")->fetchAll(PDO::FETCH_ASSOC);

// Définir le titre de la page
$page_title = "Gestion des livres";

// Inclure le header et le sidebar
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Main Content Area -->
<div class="content w-100 m-0 pt-5"  id="content">
    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Gestion des livres</h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addBookModal">
                    <i class="bi bi-plus-circle"></i> Nouveau livre
                </button>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" name="search" 
                                   placeholder="Rechercher par titre, auteur ou ISBN..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <select class="form-select" name="category">
                            <option value="0">Toutes les catégories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id_categorie']; ?>" 
                                        <?php echo $categoryFilter == $category['id_categorie'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['nom_categorie']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Books Table Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-gray-800">Liste des livres</h5>
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
                                            <?php echo htmlspecialchars($book['auteur']); ?>
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
                                                        data-bs-target="#editBookModal"
                                                        data-id="<?php echo $book['id_livre']; ?>"
                                                        data-titre="<?php echo htmlspecialchars($book['titre']); ?>"
                                                        data-auteur="<?php echo htmlspecialchars($book['auteur']); ?>"
                                                        data-isbn="<?php echo htmlspecialchars($book['isbn']); ?>"
                                                        data-categorie="<?php echo $book['id_categorie']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                    <span>Modifier</span>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success d-flex align-items-center gap-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#addCopyModal"
                                                        data-id="<?php echo $book['id_livre']; ?>"
                                                        data-titre="<?php echo htmlspecialchars($book['titre']); ?>">
                                                    <i class="bi bi-plus-circle"></i>
                                                    <span>Exemplaire</span>
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
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>">
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
                <h5 class="modal-title text-gray-800">Nouveau livre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=add" method="POST">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Titre</label>
                        <input type="text" class="form-control form-control-lg" name="titre" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Auteur</label>
                        <input type="text" class="form-control form-control-lg" name="auteur" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">ISBN</label>
                        <input type="text" class="form-control form-control-lg" name="isbn" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Mots-clés</label>
                        <input type="text" class="form-control form-control-lg" name="mots_cles" placeholder="Séparez les mots-clés par des virgules">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Catégorie</label>
                        <select class="form-select form-select-lg" name="categorie" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id_categorie']; ?>">
                                    <?php echo htmlspecialchars($category['nom_categorie']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Ajouter</button>
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
                <h5 class="modal-title text-gray-800">Modifier le livre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=edit" method="POST" id="editForm">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Titre</label>
                        <input type="text" class="form-control form-control-lg" name="titre" id="editTitre" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Auteur</label>
                        <input type="text" class="form-control form-control-lg" name="auteur" id="editAuteur" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">ISBN</label>
                        <input type="text" class="form-control form-control-lg" name="isbn" id="editIsbn" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Catégorie</label>
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
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
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
                <h5 class="modal-title text-gray-800">Ajouter un exemplaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=add_copy" method="POST" id="addCopyForm">
                <input type="hidden" name="livre_id" id="copyBookId">
                <div class="modal-body">
                    <p class="text-muted mb-4">Vous allez ajouter un nouvel exemplaire pour le livre : <strong id="copyBookTitle"></strong></p>
                    <div class="mb-4">
                        <label class="form-label small fw-medium text-gray-800">Code barre</label>
                        <input type="text" class="form-control form-control-lg" name="code_barre" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit book modal handler
    const editModal = document.getElementById('editBookModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const titre = button.getAttribute('data-titre');
            const auteur = button.getAttribute('data-auteur');
            const isbn = button.getAttribute('data-isbn');
            const categorie = button.getAttribute('data-categorie');

            const form = editModal.querySelector('#editForm');
            form.action = `?action=edit&id=${id}`;
            editModal.querySelector('#editId').value = id;
            editModal.querySelector('#editTitre').value = titre;
            editModal.querySelector('#editAuteur').value = auteur;
            editModal.querySelector('#editIsbn').value = isbn;
            editModal.querySelector('#editCategorie').value = categorie;
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
</script>

