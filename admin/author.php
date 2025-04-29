<?php
require_once '../bootstrap.php';
require_once '../config/config.php';
require_once '../config/Database.php';


Database::getInstance();

include './lang.php';

global $connexion;

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/Connexion.php');
    exit();
}

if ($_SESSION['user']['user_role_id'] != 1) {
    header('Location: ../user/dashboard.php');
    exit();
}



$action = $_GET['action'] ?? 'list';
$message = '';

switch ($action) {
    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $status = $_POST['status'] ?? 'Actif';
            $now = date('Y-m-d H:i:s');

            if (!empty($name)) {
                $stmt = $connexion->prepare("INSERT INTO n_author (author_name, author_status, author_created_on, author_updated_on) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $status, $now, $now])) {
                    $message = '<div class="alert alert-success">Auteur ajouté avec succès.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de l\'auteur.</div>';
                }
            }
        }
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'] ?? '';
            $status = $_POST['status'] ?? 'Actif';

            if (!empty($name)) {
                $stmt = $connexion->prepare("UPDATE n_author SET author_name = ?, author_status = ? WHERE author_id = ?");
                if ($stmt->execute([$name, $status, $id])) {
                    $message = '<div class="alert alert-success">' . __('author_update_success') . '</div>';
                } else {
                    $message = '<div class="alert alert-danger">' . __('author_update_error') . '</div>';
                }
            }
            
        }
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $connexion->prepare("DELETE FROM n_author WHERE author_id = ?");
            if ($stmt->execute([$id])) {
                $message = '<div class="alert alert-success">' . __('author_delete_success') . '</div>';
            } else {
                $message = '<div class="alert alert-danger">' . __('author_delete_error') . '</div>';
            }            
        }
        break;
}

$authors = $connexion->query("SELECT * FROM n_author ORDER BY author_name")->fetchAll(PDO::FETCH_ASSOC);
$page_title = "Gestion des auteurs";

require_once '../includes/header.php';
require_once '../includes/sidebar.php';

?>

<div class="content w-100 m-0 pt-5" id="content">
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= __('author_management') ?></h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAuthorModal">
                <i class="bi bi-plus-circle"></i> <?= __('add_author') ?>
            </button>
        </div>

        <?php if ($message): ?>
            <div class="row mb-4">
                <div class="col-12"><?php echo $message; ?></div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-gray-800"><?= __('authors_list') ?></h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('name') ?></th>
                                <th><?= __('status') ?></th>
                                <th><?= __('created_at') ?></th>
                                <th><?= __('updated_at') ?></th>
                                <th><?= __('actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($authors as $author): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($author['author_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $author['author_status'] === 'Actif' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $author['author_status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $author['author_created_on']; ?></td>
                                    <td><?php echo $author['author_updated_on']; ?></td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editAuthorModal"
                                                    data-id="<?php echo $author['author_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($author['author_name']); ?>"
                                                    data-status="<?php echo $author['author_status']; ?>">
                                                <i class="bi bi-pencil"></i> <?= __('edit') ?>
                                            </button>
                                            <a href="?action=delete&id=<?php echo $author['author_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Supprimer cet auteur ?')">
                                                <i class="bi bi-trash"></i> <?= __('delete') ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($authors)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted"><?= __('no_authors_found') ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Add Author Modal -->
<div class="modal fade" id="addAuthorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="?action=add" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title"><?= __('new_author') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label><?= __('name') ?></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label><?= __('status') ?></label>
                    <select name="status" class="form-select">
                        <option value="Actif"><?= __('active') ?></option>
                        <option value="Inactif"><?= __('inactive') ?></option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <button class="btn btn-primary"><?= __('add') ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Author Modal -->
<div class="modal fade" id="editAuthorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="?action=edit" method="POST" class="modal-content border-0 shadow">
            <input type="hidden" name="id" id="editId">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title"><?= __('edit_author') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label><?= __('name') ?></label>
                    <input type="text" name="name" class="form-control" id="editName" required>
                </div>
                <div class="mb-3">
                    <label><?= __('status') ?></label>
                    <select name="status" class="form-select" id="editStatus">
                        <option value="Actif"><?= __('active') ?></option>
                        <option value="Inactif"><?= __('inactive') ?></option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-light" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                <button class="btn btn-primary"><?= __('update') ?></button>
            </div>
        </form>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editAuthorModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const status = button.getAttribute('data-status');

        editModal.querySelector('#editId').value = id;
        editModal.querySelector('#editName').value = name;
        editModal.querySelector('#editStatus').value = status;
    });
});
</script>
