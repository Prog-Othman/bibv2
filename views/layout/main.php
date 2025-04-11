<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/public/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
        <div class="container">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>">
                <img src="<?php echo APP_URL; ?>/public/images/SupMTI - W Logo.png" alt="Library Logo" class="img-fluid" style="max-width: 150px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/views/catalogue/index.php">
                            <i class="fas fa-book me-1"></i>Catalogue
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/loans">
                                <i class="fas fa-book-reader me-1"></i>Mes emprunts
                            </a>
                        </li>
                        <?php if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'librarian'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarAdmin" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog me-1"></i>Administration
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/books">Gestion des livres</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/users">Gestion des utilisateurs</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/loans">Gestion des emprunts</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/categories">Gestion des catégories</a></li>
                                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/reports">Rapports</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <!-- <form class="d-flex me-3" action="<?php echo APP_URL; ?>/search" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Rechercher un livre..." required>
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form> -->
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarUser" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo isset($_SESSION['user']['login']) ? htmlspecialchars($_SESSION['user']['login']) : 'User'; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/profile">Mon profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/auth/logout">Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>/auth/Connexion.php">Connexion</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-5 py-4">
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['flash']['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?php echo $content; ?>
    </main>

<?php include_once 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo APP_URL; ?>/public/js/main.js"></script>
</body>
</html>