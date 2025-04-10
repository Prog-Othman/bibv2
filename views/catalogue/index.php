<?php
require_once __DIR__ . '/../../config/config.php';

// Load data
// Get books from database with pagination and filters
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$currentCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;

$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$query = "SELECT b.*, author_name as author_name, 
          (SELECT COUNT(*) = 0 FROM n_emprunts e WHERE e.id_exemplaire = b.id_livre AND e.date_retour_effective IS NULL) as available
          FROM n_livre b 
          LEFT JOIN n_author a ON b.author_id = a.author_id ";

$params = [];
$conditions = [];

if ($searchQuery) {
    $conditions[] = "(b.title LIKE :search OR a.name LIKE :search)";
    $params[':search'] = "%$searchQuery%";
}

if ($currentCategory) {
    $conditions[] = "b.category_id = :category";
    $params[':category'] = $currentCategory;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$offset = ($currentPage - 1) * ITEMS_PER_PAGE;
$query .= " LIMIT " . ITEMS_PER_PAGE . " OFFSET " . $offset;

$stmt = $db->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Get categories from database
$categoryQuery = "SELECT category_id, category_name FROM n_categorie_livres ORDER BY category_name";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate pagination
// Get total count of books for pagination
$countQuery = "SELECT COUNT(*) as total FROM n_livre b";
if (!empty($conditions)) {
    $countQuery .= " WHERE " . implode(" AND ", $conditions);
}
$countStmt = $db->prepare($countQuery);
$countStmt->execute($params);
$totalBooks = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Calculate total pages using a default value if ITEMS_PER_PAGE is not defined
$itemsPerPage = defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 10; // Default to 10 items per page
$totalPages = ceil($totalBooks / $itemsPerPage);

// Calculate pagination
$totalPages = ceil(count($books) / ITEMS_PER_PAGE); // Define ITEMS_PER_PAGE in config
?>
<!-- Reste du contenu HTML... -->
<!DOCTYPE html>
<html lang="en">
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
                        <a class="nav-link" href="<?php echo APP_URL; ?>/catalogue">
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
                <form class="d-flex me-3" action="<?php echo APP_URL; ?>/search" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Rechercher un livre..." required>
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarUser" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo isset($_SESSION['user']['nom']) ? htmlspecialchars($_SESSION['user']['nom']) : 'User'; ?>
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
    
<div class="container mt-5 py-4">
    <!-- En-tête et filtres -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-3 text-dark">Catalogue de la Bibliothèque</h1>
        </div>
        <div class="col-md-4">
            <form action="<?php echo APP_URL; ?>/catalogue" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Rechercher..." value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Filtres par catégorie -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group flex-wrap" role="group">
                <a href="<?php echo APP_URL; ?>/catalogue" class="btn <?php echo !isset($currentCategory) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    Tous
                </a>
                <?php
                if (isset($categories)) {
                    // Your code that uses $categories
                    foreach ($categories as $cat): 
                        $categories = []; // Initialize as empty array if that's what you need
                        ?>
                        <a href="<?php echo APP_URL; ?>/catalogue?category=<?php echo urlencode($cat['id']); ?>" 
                           class="btn <?php echo (isset($currentCategory) && $currentCategory == $cat['id']) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                <?php } else {

                }?>
  
            </div>
        </div>
    </div>

    <!-- Grille des livres -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
    <?php if (!empty($books)): ?>
        <?php foreach ($books as $book): ?>
            <!-- Book card HTML here -->
            <div class="col">
                <div class="card h-100">
                    <div class="card-img-top bg-light text-center py-3">
                        <i class="fas fa-book fa-4x text-primary"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($book['author_name']); ?>
                                </small>
                            </p>
                            <p class="card-text">
                                <span class="badge <?php echo $book['available'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $book['available'] ? 'Disponible' : 'Indisponible'; ?>
                                </span>
                            </p>
                            <a href="<?php echo APP_URL; ?>/catalogue/view/<?php echo $book['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-info-circle me-1"></i>Détails
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">Aucun livre disponible</div>
        </div>
    <?php endif; ?>
        <?php foreach ($books as $book): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-img-top bg-light text-center py-3">
                        <i class="fas fa-book fa-4x text-primary"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($book['author_name']); ?>
                            </small>
                        </p>
                        <p class="card-text">
                            <span class="badge <?php echo $book['available'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $book['available'] ? 'Disponible' : 'Indisponible'; ?>
                            </span>
                        </p>
                        <a href="<?php echo APP_URL; ?>/catalogue/view/<?php echo $book['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-info-circle me-1"></i>Détails
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Navigation des pages">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $currentPage == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo APP_URL; ?>/catalogue?page=<?php echo $i; ?><?php echo isset($currentCategory) ? '&category=' . urlencode($currentCategory) : ''; ?><?php echo isset($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php 
// Corriger le chemin du footer
include __DIR__ . '/../../Includes/Footer.php'; 
?>

</body>
</html>