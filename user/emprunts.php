<?php
require_once '../bootstrap.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_login'])) {
    header('Location: ../auth/Connexion.php');
    exit();
}

// Vérifier si l'utilisateur est un administrateur
if ($_SESSION['user']['user_role_id'] != 3) {
    header('Location: ../admin/dashboard.php');
    exit();
}

// Inclure le contrôleur
require_once __DIR__ . '/../controllers/EmpruntsController.php';
$controller = new EmpruntsController();

$user_id = $_SESSION['user_login'];
$success = false;

// Traitement de la demande d'emprunt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'emprunter' && isset($_POST['exemplaire_id'])) {
        $exemplaire_id = $_POST['exemplaire_id'];
        
        // Utiliser le contrôleur pour créer l'emprunt
        if ($controller->creerEmprunt($user_id, $exemplaire_id)) {
            header('Location: emprunts.php?success=1');
            exit();
        }
    } elseif ($_POST['action'] === 'prolonger' && isset($_POST['emprunt_id'])) {
        $emprunt_id = $_POST['emprunt_id'];
        
        // Utiliser le contrôleur pour prolonger l'emprunt
        if ($controller->prolongerEmprunt($emprunt_id, $user_id)) {
            header('Location: emprunts.php?success=2');
            exit();
        }
    }
}

// Récupérer les données via le contrôleur
$emprunts = $controller->getUserEmprunts($user_id);
$livres_disponibles = $controller->getLivresDisponibles();

// Définir le titre de la page
$page_title = "Mes Emprunts";

// Include header and sidebar
include_once('../includes/header.php');
include_once('../includes/sidebar.php');
?>
<?php echo "<!--Looking for: " . realpath('../includes/header.php') . "-->"; ?>
<?php include_once('../includes/header.php'); ?>

<div class="content" id="content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <header class="page-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1><i class="bi bi-journal-bookmark"></i> Mes Emprunts</h1>
                        <p class="lead">Gérez vos emprunts et découvrez de nouveaux livres à emprunter</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex justify-content-md-end">
                            <div class="me-3">
                                <span class="badge rounded-pill bg-primary">
                                    <i class="bi bi-book"></i> <?= count($emprunts) ?> livre(s) emprunté(s)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="container">
            <!-- Alert Messages -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> L'emprunt a été effectué avec succès.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (isset($_GET['success']) && $_GET['success'] == 2): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> L'emprunt a été prolongé avec succès.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Rest of your content here -->
            
            <!-- Emprunts en cours -->
            <section class="mb-5">
                <!-- Your emprunts section content here -->
            </section>

            <!-- Livres disponibles -->
            <section>
                <!-- Your livres section content here -->
            </section>
        </div>
    </div>
</div>

<?php include_once('../includes/footer.php'); ?>


