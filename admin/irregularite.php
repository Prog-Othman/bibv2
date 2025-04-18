<?php
require_once '../bootstrap.php';
require_once '../config/config.php';
require_once '../config/Database.php';

Database::getInstance();
global $connexion;

// Date actuelle moins 2 mois
$limite = date('Y-m-d', strtotime('-2 months'));

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/Connexion.php');
    exit();
}

// Vérifier si l'utilisateur est un administrateur
if ($_SESSION['user']['user_role_id'] != 1) {
    header('Location: ../user/dashboard.php');
    exit();
}

// Pagination
$parPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $parPage;

// Total des emprunts en retard
$countSql = "
    SELECT COUNT(*) as total
    FROM n_emprunts e
    WHERE e.statut = 'en_retard' AND DATE(e.date_retour_prevue) < :limite
";
$countStmt = $connexion->prepare($countSql);
$countStmt->bindValue(':limite', $limite);
$countStmt->execute();
$total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$pagesTotal = ceil($total / $parPage);

// Requête principale
$sql = "
    SELECT 
        e.id_emprunt,
        e.date_emprunt,
        e.date_retour_prevue,
        e.id_exemplaire,
        CONCAT(l.titre, ' ', exm.code_barre) AS nom_livre,
        e.statut,
        e.notes,
        e.id_utilisateur,
        u.user_bib_status,
        COALESCE(CONCAT(etu.etud_nom, ' ', etu.etud_prenom), e.nom_externe) AS nom_emprunteur
    FROM n_emprunts e
    JOIN n_exemplaires exm ON e.id_exemplaire = exm.id_exemplaire
    JOIN n_livre l ON exm.id_livre = l.id_livre 
    LEFT JOIN n_utilisateurs u ON u.user_id = e.id_utilisateur
    LEFT JOIN n_etudiants etu ON u.user_ref_id = etu.etud_id
    WHERE e.statut = 'en_retard'
      AND DATE(e.date_retour_prevue) < :limite
    ORDER BY e.date_retour_prevue ASC
    LIMIT :limit OFFSET :offset
";

$stmt = $connexion->prepare($sql);
$stmt->bindValue(':limite', $limite);
$stmt->bindValue(':limit', $parPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$retards = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="content w-100 m-0 pt-5" id="content">
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Gestion des retards</h1>
        </div>

        <?php if (isset($_GET['bloque'])): ?>
            <div class="alert alert-success">
                L'utilisateur a été <?= $_GET['bloque'] === 'bloquer' ? 'bloqué' : 'débloqué' ?> avec succès.
            </div>
        <?php endif; ?>

        <!-- Formulaire de recherche -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-8">
                <label class="form-label">Nom de l'adhérent</label>
                <input type="text" name="nom_adh" class="form-control" placeholder="Ex : Ali" value="<?= htmlspecialchars($_GET['nom_adh'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Rechercher</button>
                <a href="retards.php" class="btn btn-secondary">Réinitialiser</a>
            </div>
        </form>

        <!-- Résultat -->
        <?php if (count($retards) > 0): ?>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Emprunteur</th>
                        <th>Date prévue</th>
                        <th>Exemplaire</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($retards as $emprunt): ?>
                        <tr>
                            <td><?= htmlspecialchars($emprunt['nom_emprunteur']) ?></td>
                            <td><?= date('d/m/Y', strtotime($emprunt['date_retour_prevue'])) ?></td>
                            <td><?= htmlspecialchars($emprunt['nom_livre']) ?></td>
                            <td><?= htmlspecialchars($emprunt['statut']) ?></td>
                            <td>
                                <?php if (!empty($emprunt['id_utilisateur'])): ?>
                                    <form method="POST" action="traitement/bloquer_utilisateur.php"
                                          onsubmit="return confirm('Voulez-vous vraiment <?= intval($emprunt['user_bib_status']) ? 'débloquer' : 'bloquer' ?> cet utilisateur ?');"
                                          style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?= $emprunt['id_utilisateur'] ?>">
                                        <input type="hidden" name="action" value="<?= intval($emprunt['user_bib_status']) ? 'debloquer' : 'bloquer' ?>">
                                        <button type="submit" class="btn btn-<?= intval($emprunt['user_bib_status']) ? 'success' : 'danger' ?> btn-sm">
                                            <?= intval($emprunt['user_bib_status']) ? 'Débloquer' : 'Bloquer' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Externe</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($pagesTotal > 1): ?>
                <nav>
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $pagesTotal; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">Aucun emprunt en retard de plus de 2 mois.</div>
        <?php endif; ?>
    </div>
</div>
