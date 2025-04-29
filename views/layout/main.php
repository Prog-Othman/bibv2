
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
    <div class="container d-flex align-items-center justify-content-between flex-nowrap" style="flex-wrap: nowrap !important;">
    <a class="navbar-brand me-3" href="<?php echo APP_URL; ?>">
        <img src="<?php echo APP_URL; ?>/public/images/SupMTI - W Logo.png" alt="Library Logo" class="img-fluid" style="max-width: 150px;">
    </a>

    <ul class="navbar-nav flex-row me-auto">
        <li class="nav-item me-3">
            <!-- <a class="nav-link" href="<?php echo APP_URL; ?>/views/catalogue/index.php">
                <i class="fas fa-book me-1"></i>Catalogue
            </a> -->
        </li>
        <?php 
        if (isset($_SESSION['user'])): ?>
            
            <?php if ($_SESSION['user']['user_role_id'] === '1' ): ?>
                <li class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarAdmin" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>Administration
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/books">Gestion des livres</a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/loans">Gestion des emprunts</a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/categories">Gestion des catégories</a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/author">Gestion des Auteurs</a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/irregularite">irregularites</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- si il est etudiant  -->
            <?php if ($_SESSION['user']['user_role_id'] === '3' ): ?>
                <li class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarAdmin" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>Administration
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/dashboard">tableau de bord</a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/reservation">Reservation</a></li>
                       
                    </ul>
                </li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>

    <ul class="navbar-nav flex-row">
   <li class="nav-item dropdown me-2">
    <a class="nav-link dropdown-toggle" href="#" id="navbarLang" role="button" data-bs-toggle="dropdown">
        🌐 <?php echo strtoupper($_SESSION['lang'] ?? 'FR'); ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>?lang=fr">Français</a></li>
        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>?lang=en">English</a></li>
    </ul>
</li>

        <?php if (isset($_SESSION['user'])): ?>

            <li class="nav-item dropdown me-2">
                <a class="nav-link dropdown-toggle" href="#" id="navbarUser" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user me-1"></i>
                    <?php echo isset($_SESSION['user']['nom']) ? htmlspecialchars($_SESSION['user']['nom']) : 'User'; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/auth/logout">Déconnexion</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li class="nav-item me-2">
            <a class="nav-link" href="<?php echo APP_URL; ?>/auth/Connexion.php?lang=<?php echo $_SESSION['lang'] ?? 'fr'; ?>">Connexion</a>
        </li>
        <?php endif; ?>
    </ul>
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