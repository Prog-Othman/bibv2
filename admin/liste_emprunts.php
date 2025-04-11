<?php
$pdo = new PDO("mysql:host=localhost;dbname=bibliotheque", "root", "");

// Récupération des filtres
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$nom_adh = $_GET['nom_adh'] ?? '';

// Construction dynamique
$sql = "
     SELECT e.*,CONCAT(etu.etud_nom,' ', etu.etud_prenom) AS nom 
    FROM n_emprunts e 
    JOIN n_utilisateurs u ON u.user_id = e.id_utilisateur
    JOIN n_etudiants etu ON u.user_ref_id = etu.etud_id
    WHERE 1
";
$params = [];

if ($date_debut && $date_fin) {
    $sql .= " AND e.date_emprunt BETWEEN :debut AND :fin";
    $params[':debut'] = $date_debut . " 00:00:00";
    $params[':fin'] = $date_fin . " 23:59:59";
}

if ($nom_adh) {
    $sql .= " AND u.nom LIKE :nom";
    $params[':nom'] = "%$nom_adh%";
}

$sql .= " ORDER BY e.date_emprunt DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$emprunts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste des Emprunts</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">

  <div class="container">
    <h2 class="mb-4">Liste des emprunts</h2>

    <!-- Formulaire de recherche -->
    <form method="GET" class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">Date début</label>
        <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($date_debut) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Date fin</label>
        <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($date_fin) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Nom de l'adhérent</label>
        <input type="text" name="nom_adh" class="form-control" placeholder="Ex : Dupont" value="<?= htmlspecialchars($nom_adh) ?>">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary me-2">Rechercher</button>
        <a href="liste_emprunts.php" class="btn btn-secondary">Réinitialiser</a>
      </div>
    </form>

    <!-- Résultat -->
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Nom Adhérent</th>
          <th>Date Emprunt</th>
          <th>Retour Prévu</th>
          <th>Retour Effectif</th>
          <th>Statut</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($emprunts) > 0): ?>
          <?php foreach ($emprunts as $emp): ?>
            <tr>
              <td><?= $emp['id_emprunt'] ?></td>
              <td><?= htmlspecialchars($emp['nom']) ?></td>
              <td><?= $emp['date_emprunt'] ?></td>
              <td><?= $emp['date_retour_prevue'] ?></td>
              <td><?= $emp['date_retour_effective'] ?? '—' ?></td>
              <td><?= ucfirst($emp['statut']) ?></td>
              <td><?= htmlspecialchars($emp['notes'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center text-muted">Aucun emprunt trouvé.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
