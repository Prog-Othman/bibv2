<?php

$lang = 'fr';

if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
}


$langFile =  "../lang/{$lang}.php";

if (!file_exists($langFile)) {
    $langFile = "../lang/fr.php";
}

$translations = include($langFile);

ob_start();
require_once __DIR__ . '/../bootstrap.php';

// Si l'utilisateur est déjà connecté, le rediriger vers son tableau de bord
if (isset($_SESSION['user'])) {
    if (isset($_SESSION['user']['user_role_id']) && $_SESSION['user']['user_role_id'] != 1) {
        header('Location: ../admin/books.php');
    } else if (isset($_SESSION['user']['user_role_id']) && $_SESSION['user']['user_role_id'] != 3) {
        header('Location: ../user/dashboard.php');
    }
    exit();
}

// Initialisation des variables
$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_login = filter_input(INPUT_POST, 'user_login', FILTER_SANITIZE_STRING);
    $user_nom = filter_input(INPUT_POST, 'user_nom', FILTER_SANITIZE_STRING);
    
    

    if (empty($user_login) || empty($user_nom)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        try {
            // Connexion à la base de données
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Recherche de l'utilisateur
            $stmt = $db->prepare("SELECT u.*, r.nom_role, r.niveau_acces 
                                FROM n_utilisateurs u 
                                LEFT JOIN n_role r ON u.user_role_id = r.id_role 
                                WHERE u.user_nom = ?");
            $stmt->execute([$user_nom]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $bcryptValidationProcess = password_verify($user_login, $user['user_login']);
            if ($user and $bcryptValidationProcess) {
                // Vérifier si l'utilisateur est actif
                if (!$user['user_estActif']) {
                    $error = 'Votre compte est désactivé. Veuillez contacter l\'administrateur.';
                } else {
                    // Création de la session avec plus d'informations
                    $_SESSION['user'] = [
                        'id' => $user['user_id'],
                        'login' => $user['user_login'],
                        'nom' => $user['user_nom'],
                        'role' => $user['nom_role'],
                        'niveau_acces' => $user['niveau_acces'],
                        'photo' => $user['user_photo'],
                        'user_role_id' => $user['user_role_id']  // Ajout de user_role_id
                    ];

                    // Mise à jour de la dernière connexion
                    $update_stmt = $db->prepare("UPDATE n_utilisateurs SET user_dern_conx = NOW() WHERE user_id = ?");
                    $update_stmt->execute([$user['user_id']]);

                    // Redirection selon le rôle
                    if ($user['user_role_id'] == 1) {
                        header('Location: ../admin/books.php');
                    } else if ($user['user_role_id'] == 3) {
                        header('Location: ../user/dashboard.php');
                    }
                    exit();
                }
            } else {
                $error = 'Login ou nom incorrect.';
            }
        } catch (PDOException $e) {
            $error = 'Une erreur est survenue. Veuillez réessayer plus tard.';
            // Log l'erreur pour l'administrateur
            error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo APP_URL; ?>/public/css/style.css" rel="stylesheet">
</head>
<body class="bg-primary-custom min-vh-100">
    <div class="container-fluid min-vh-100">
        <div class="row min-vh-100">
            <div class="col-md-6 bg-primary-custom d-flex align-items-center justify-content-center p-4">
                <img src="../public/images/SupMTI - W Logo.png" alt="Library Logo" class="img-fluid" style="max-width: 300px;">
            </div>
            <div class="col-md-6 bg-white d-flex align-items-center justify-content-center p-4">
                <div class="w-100" style="max-width: 400px;">
                    <h1 class="text-primary-custom text-center display-4 mb-4"><?php echo $translations['Espace Connexion'];?> </h1>
                    <h2 class="text-center h4 mb-4 text-dark">
                        Connectez-vous pour 
                        accéder à votre espace
                    </h2>
                    <?php if($error): ?>
                    <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">

                        <div class="mb-3">
                            <label for="user_nom" class="form-label text-primary-custom"><?php echo $translations['Compte'];?></label>
                            <input type="text" class="form-control form-control-lg" id="user_nom" name="user_nom" required 
                                value="<?php echo isset($_POST['user_nom']) ? htmlspecialchars($_POST['user_nom']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="user_login" class="form-label text-primary-custom"><?php echo $translations['Mot de passe'];?></label>
                            <input type="password" class="form-control form-control-lg" id="user_login" name="user_login" required 
                                value="<?php echo isset($_POST['user_login']) ? htmlspecialchars($_POST['user_login']) : ''; ?>">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="remember" id="remember">
                            <label class="form-check-label text-black" for="remember">
                                <?php echo $translations['Rester connecté'];?>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary-custom text-white btn-lg w-100 d-flex align-items-center justify-content-center gap-2">
                            <svg id="Login" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.185 12H3.40698" stroke="#ffffff" stroke-width="1.5" stroke-linecap="square"></path>
                                <path d="M13.6462 15.2751L16.9352 12.0001L13.6462 8.72412" stroke="#ffffff" stroke-width="1.5" stroke-linecap="square"></path>
                                <path d="M9.66382 7.375V2.75H21.0928V21.25H9.66382V16.625" stroke="#ffffff" stroke-width="1.5" stroke-linecap="square"></path>
                            </svg>
                            <?php echo $translations['Connectez-vous'];?> 
                        </button>
                    </form>
                    
                    <p class="text-center mt-3">
                        <a href="request_reset.php" class="text-primary-custom text-decoration-none"> <?php echo $translations['Mot de passe oublié ?'];?></a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>