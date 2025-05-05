<?php
if (!isset($_SESSION)) {
    session_start();
}
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
    <!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Your CSS styles here */
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
        
        /* Add the rest of your CSS styles */
    </style>
</head>
<body>
    <div class="d-flex">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom">
    <div class="container-fluid px-3 px-lg-4">
        <div class="d-flex align-items-center w-100">
            <button class="btn border border-0 toggle-sidebar me-3" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>

            <div class="navbar-brand m-0 me-auto">
                <img src="<?php echo APP_URL; ?>/public/images/SupMTI - W Logo.png" alt="Logo" height="40">
            </div>

            <!-- Boutons langue à droite -->
            <div class="d-flex align-items-center ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    $user_id = $_SESSION['user_id'];
                    $unread_count = 0;
                    $query = "SELECT COUNT(*) as count FROM n_notifications WHERE id_utilisateur = ? AND est_lu = 0";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $unread_count = $row['count'];
                    }
                    ?>
                    <a href="<?php echo APP_URL; ?>/notifications.php" class="text-white me-3 position-relative">
                        <i class="bi bi-bell"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $unread_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
                <a href="?lang=fr" class="text-white me-2">Français</a>
                |
                <a href="?lang=en" class="text-white ms-2">English</a>
            </div>
        </div>
    </div>
</nav>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    function toggleSidebar() {
        sidebar.classList.toggle('sidebar-hidden');
        content.classList.toggle('content-full');
        toggleBtn.classList.toggle('shifted');
    }
    
    toggleBtn.addEventListener('click', toggleSidebar);
    
    // Responsive handling
    function checkWidth() {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('sidebar-hidden');
            content.classList.add('content-full');
            toggleBtn.classList.add('shifted');
        }
    }
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(checkWidth, 250);
    });
    
    checkWidth(); // Initial check
});
</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
