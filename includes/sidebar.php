<?php
if (!isset($_SESSION)) {
    session_start();
}

// Include the Router class
require_once __DIR__ . '/../core/Router.php';
$router = new Router();




?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="<?php echo APP_URL; ?>/public/css/style.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 65px;
            left: 0;
            height: 100vh;
            width: 280px; 
            background-color: #003435;
            padding-top: 1rem;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar-hidden {
            margin-left: -280px;
        }
        
        .content {
            margin-left: 280px; 
            transition: all 0.3s ease;
        }
        
        .content-full {
            margin-left: 0;
        }
        
        .sidebar .logo-container {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .logo-container img {
            max-width: 150px;
            height: auto;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: #fff;  
        }
        
        .user-profile {
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            position: absolute;
            bottom: 60px;
            width: 100%;
            color: white;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <!-- Admin Menu -->
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['user_role_id'] == 1): ?>
            <div class="nav flex-column mt-3">
               
                <a class="nav-link <?= $router->isActiveRoute('admin', 'books') ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/books.php">
                    <i class="bi bi-book"></i> <?= __('books') ?>
                </a>
                <a class="nav-link <?= $router->isActiveRoute('admin', 'loans') ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/loans.php">
                    <i class="bi bi-clipboard-check"></i> <?= __('loans') ?>
                </a>
                <a class="nav-link <?= $router->isActiveRoute('admin', 'categories') ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/categories.php">
                    <i class="bi bi-tags"></i> <?= __('categories') ?>
                </a>

                <a class="nav-link <?= $router->isActiveRoute('admin', 'auteur') ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/author.php">
                    <i class="bi bi-person-lines-fill"></i> <?= __('authors') ?>
                </a>

                <a class="nav-link <?= $router->isActiveRoute('admin', 'irregularite') ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/irregularite.php">
                   <i class="bi bi-exclamation-triangle-fill"></i> <?= __('irregularites') ?>
                </a>
                <a class="nav-link <?= $router->isActiveRoute('admin', 'reservation') ? 'active' : '' ?>" 
                   href="<?= APP_URL ?>/admin/reservation.php">
                   <i class="bi bi-calendar-check"></i> <?= __('reservations') ?>
                </a>
            </div>
        <?php endif; ?>

        <!-- User Menu -->
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['user_role_id'] == 3): ?>
            <div class="nav flex-column mt-3">
                <a class="nav-link" href="<?= APP_URL ?>/user/dashboard.php">
                    <i class="bi bi-speedometer2"></i> <?= __('Mon_tableau_bord') ?>
                </a>
                <a class="nav-link" href="<?= APP_URL ?>/user/reservation.php">
                    <i class="bi bi-book"></i> <?= __('Mes_reservations') ?>
                </a>
                <!-- <a class="nav-link" href="<?php echo APP_URL; ?>/user/emprunts.php" onclick="return true;">
                    <i class="bi bi-book"></i> Mes emprunts
                </a>
                <a class="nav-link" href="<?= APP_URL ?>/user/profile.php">
                    <i class="bi bi-person"></i> Mon profil
                </a> -->
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user'])): ?>
            <!-- Profil utilisateur -->
            <div class="user-profile">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <div>
                        <div class="fw-bold">
                            <?php 
                            if (isset($_SESSION['user']['nom'])) {
                                echo htmlspecialchars($_SESSION['user']['nom'], ENT_QUOTES, 'UTF-8');
                            } else {
                                echo 'Nom non défini'; // Default value if nom is not set
                            }
                            ?>
                        </div>
                        <small class="text-light-50">
                            <?php echo isset($_SESSION['user']['user_role_id']) && $_SESSION['user']['user_role_id'] == 1 ? 'Administrateur' : 'Etudiant'; ?>
                        </small>
                    </div>
                </div>
                <a href="<?php echo APP_URL; ?>/auth/logout.php" class="btn btn-outline-light btn-sm w-100">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </div>
        <?php else: ?>
            <!-- Menu visiteur -->
            <div class="nav flex-column mt-3">
                <a class="nav-link" href="<?php echo APP_URL; ?>/auth/Connexion.php">
                    <i class="bi bi-box-arrow-in-right"></i> Connexion
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="content" id="content">
        <div class="container-fluid py-4">
            <!-- Le contenu de la page sera inséré ici -->
        </div>
    </div>
</body>
</html>