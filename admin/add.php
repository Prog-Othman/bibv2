<?php
$page_title = "Ajouter une catégorie";
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Ajouter une nouvelle catégorie</h4>
                </div>
                <div class="card-body">
                    <form action="<?= APP_URL ?>/admin/categories/store" method="POST">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom de la catégorie</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="<?= APP_URL ?>/admin/categories" class="btn btn-secondary">Retour</a>
                            <button type="submit" class="btn btn-primary">Ajouter la catégorie</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

