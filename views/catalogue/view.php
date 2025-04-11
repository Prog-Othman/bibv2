<?php
// Inclure l'en-tête
include __DIR__ . '/../../includes/header.php';
?>



<div class="container py-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-img-top bg-light text-center py-5">
                    <i class="fas fa-book fa-6x text-primary"></i>
                </div>
                <div class="card-body text-center">
                    <span class="badge <?php echo $isAvailable ? 'bg-success' : 'bg-danger'; ?> mb-2">
                        <?php echo $isAvailable ? 'Disponible' : 'Indisponible'; ?>
                    </span>
                    <?php if ($isAvailable && isset($_SESSION['user'])): ?>
                        <a href="<?php echo APP_URL; ?>/loans/request/<?php echo $book['id']; ?>" class="btn btn-primary w-100">
                            <i class="fas fa-book-reader me-1"></i>Emprunter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/catalogue">Catalogue</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($book['title']); ?></li>
                </ol>
            </nav>

            <h1 class="mb-4"><?php echo htmlspecialchars($book['title']); ?></h1>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informations détaillées</h5>
                    <table class="table">
                        <tr>
                            <th style="width: 150px;">Auteur</th>
                            <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                        </tr>
                        <tr>
                            <th>ISBN</th>
                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        </tr>
                        <tr>
                            <th>Catégorie</th>
                            <td><?php echo htmlspecialchars($book['category_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Année</th>
                            <td><?php echo htmlspecialchars($book['date_publication']); ?></td>
                        </tr>
                        <tr>
                            <th>Éditeur</th>
                            <td><?php echo htmlspecialchars($book['publisher']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if (!empty($book['description'])): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Description</h5>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// Corriger le chemin du footer
include __DIR__ . '/../../includes/footer.php'; 
?>
