<?php
require_once '../bootstrap.php';
require_once '../config/config.php';
require_once '../config/Database.php';


Database::getInstance();
include './lang.php';


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



// Traitement des actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? '';

            if (!empty($nom)) {
                $stmt = $connexion->prepare("INSERT INTO n_categorie_livres (nom_categorie, description) VALUES (?, ?)");
                if ($stmt->execute([$nom, $description])) {
                    $message = '<div class="alert alert-success">Catégorie ajoutée avec succès.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de la catégorie.</div>';
                }
            }
        }
        break;

    case 'edit':
        $id = $_GET['id'] ?? null;
        if ($id && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? '';

            if (!empty($nom)) {
                $stmt = $connexion->prepare("UPDATE n_categorie_livres SET nom_categorie = ?, description = ? WHERE id_categorie = ?");
                if ($stmt->execute([$nom, $description, $id])) {
                    $message = '<div class="alert alert-success">' . __('category_update_success') . '</div>';
                } else {
                    $message = '<div class="alert alert-danger">' . __('category_update_error') . '</div>';
                }
            }
            
        }
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Vérifier si la catégorie est utilisée
            $stmt = $connexion->prepare("SELECT COUNT(*) FROM n_livre WHERE category_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $stmt = $connexion->prepare("DELETE FROM n_categorie_livres WHERE id_categorie = ?");
                if ($stmt->execute([$id])) {
                    $message = '<div class="alert alert-success">Catégorie supprimée avec succès.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de la suppression de la catégorie.</div>';
                }
            } else {
                $message = '<div class="alert alert-warning">Cette catégorie ne peut pas être supprimée car elle est utilisée par des livres.</div>';
            }
        }
        break;
}

// Récupérer toutes les catégories
$categories = $connexion->query("SELECT * FROM n_categorie_livres ORDER BY nom_categorie")->fetchAll(PDO::FETCH_ASSOC);

// Définir le titre de la page
$page_title = "Gestion des catégories";

// Inclure le header et le sidebar
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Main Content Area -->
<div class="content w-100 m-0 pt-5" id="content">
    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800"><?= __('manage_categories'); ?></h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-circle"></i> <?= __('new_category'); ?>
                </button>
            </div>

        </div>

        <?php if ($message): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <?php echo $message; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Categories Table Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-gray-800"><?= __('categories_list') ?></h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4"><?= __('name') ?></th>
                                <th><?= __('description') ?></th>
                                <th><?= __('books_count') ?></th>
                                <th class="px-4"><?= __('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <?php
                                $stmt = $connexion->prepare("SELECT COUNT(*) FROM n_livre WHERE category_id = ?");
                                $stmt->execute([$category['id_categorie']]);
                                $bookCount = $stmt->fetchColumn();
                                ?>
                                <tr>
                                    <td class="px-4 fw-medium"><?php echo htmlspecialchars($category['nom_categorie']); ?></td>
                                    <td class="text-muted"><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">
                                            <?php echo $bookCount; ?> livre<?php echo $bookCount > 1 ? 's' : ''; ?>
                                        </span>
                                    </td>
                                    <td class="px-4">
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editCategoryModal"
                                                data-id="<?php echo $category['id_categorie']; ?>"
                                                data-nom="<?php echo htmlspecialchars($category['nom_categorie']); ?>"
                                                data-description="<?php echo htmlspecialchars($category['description'] ?? ''); ?>">
                                                <i class="bi bi-pencil"></i>
                                                <span><?= __('edit') ?></span>
                                            </button>
                                            <?php if ($bookCount == 0): ?>
                                                <a href="?action=delete&id=<?php echo $category['id_categorie']; ?>"
                                                    class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                                                    onclick="return confirm(<?= __('delete_confirmation') ?>)">
                                                    <i class="bi bi-trash"></i>
                                                    <span><?= __('delete') ?></span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted"><?= __('no_categories_found') ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-800" id="addCategoryModalLabel"><?= __('new_category') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=add" method="POST">
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="nom" class="form-label small fw-medium text-gray-800"><?= __('category_name_label') ?></label>
                        <input type="text" class="form-control form-control-lg" id="nom" name="nom" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="form-label small fw-medium text-gray-800"><?= __('description') ?></label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-gray-800" id="editCategoryModalLabel"><?= __('edit_category') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=edit" method="POST" id="editForm">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="editNom" class="form-label small fw-medium text-gray-800"><?= __('category_name_label') ?></label>
                        <input type="text" class="form-control form-control-lg" id="editNom" name="nom" required>
                    </div>
                    <div class="mb-4">
                        <label for="editDescription" class="form-label small fw-medium text-gray-800"><?= __('description') ?></label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all modals
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            new bootstrap.Modal(modal);
        });

        // Edit modal handler
        const editModal = document.getElementById('editCategoryModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const nom = button.getAttribute('data-nom');
                const description = button.getAttribute('data-description');

                const form = editModal.querySelector('#editForm');
                form.action = `?action=edit&id=${id}`;
                editModal.querySelector('#editId').value = id;
                editModal.querySelector('#editNom').value = nom;
                editModal.querySelector('#editDescription').value = description;
            });
        }
    });
</script>