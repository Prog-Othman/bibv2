<div class="row mb-4">
    <div class="col-md-12">
        <div class="jumbotron bg-light p-5 rounded d-flex align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 text-dark">Bienvenue à la Bibliothèque</h1>
                <p class="lead text-dark">Découvrez notre collection de livres et profitez de nos services de prêt.</p>
                <hr class="my-4 text-dark">
                <p class="text-dark">Vous pouvez rechercher des livres, gérer vos emprunts et découvrir nos nouveautés.</p>
                <p class="text-dark">Veuillez vous connecter dans votre espace</p>
            </div>
            <div class="col-md-4 text-center">
                <img src="public/images/bibl.png" alt="Bibliothèque" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>


<!-- <div class="row">
    <div class="col-md-6 border border-0 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-star me-2"></i>Derniers Livres Ajoutés
                </h5>
            </div>
            <div class="card-body bg-light">
                <?php if (!empty($latestBooks)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($latestBooks as $book): ?>
                            <a href="<?php echo APP_URL; ?>/books/view/<?php echo $book['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($book['title']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($book['category_name']); ?></small>
                                </div>
                                <small class="text-muted">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted my-3">Aucun livre récent à afficher</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Livres les Plus Populaires
                </h5>
            </div>
            <div class="card-body bg-light">
                <?php if (!empty($popularBooks)): ?>
                    <div class="list-group bg-light list-group-flush">
                        <?php foreach ($popularBooks as $book): ?>
                            <a href="<?php echo APP_URL; ?>/books/view/<?php echo $book['id_livre']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($book['titre'] ?? ''); ?></h6>
                                    <small class="text-muted"><?php echo isset($book['loan_count']) ? $book['amende_count'] : 0; ?> emprunts</small>
                                </div>
                                <small class="text-muted">ISBN: <?php echo htmlspecialchars($book['isbn'] ?? ''); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted my-3">Aucun livre populaire à afficher</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> -->