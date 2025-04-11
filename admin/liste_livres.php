<?php


require_once '../config/Database.php';


Database::getInstance();


global $connexion;



// Récupération des filtres
$titre = $_GET['titre'] ?? '';
$mot_cle = $_GET['mot_cle'] ?? '';
$isbn = $_GET['isbn'] ?? '';

// Construction de la requête dynamique
$sql = "SELECT * FROM n_livre WHERE 1";
$params = [];

if ($titre !== '') {
    $sql .= " AND titre LIKE :titre";
    $params[':titre'] = "%$titre%";
}

if ($mot_cle !== '') {
    $sql .= " AND (titre LIKE :motcle OR auteur LIKE :motcle)";
    $params[':motcle'] = "%$mot_cle%";
}

if ($isbn !== '') {
    $sql .= " AND isbn LIKE :isbn";
    $params[':isbn'] = "%$isbn%";
}

$stmt = $connexion->prepare($sql);
$stmt->execute($params);
$livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste des Livres</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">

  <div class="container">
    <h2 class="mb-4">Liste des livres</h2>

    <!-- Formulaire de filtre -->
    <form method="GET" class="row g-3 mb-4">
      <div class="col-md-4">
        <input type="text" name="titre" class="form-control" placeholder="Filtrer par titre" value="<?= htmlspecialchars($titre) ?>">
      </div>
      <div class="col-md-4">
        <input type="text" name="mot_cle" class="form-control" placeholder="Mot clé (titre ou auteur)" value="<?= htmlspecialchars($mot_cle) ?>">
      </div>
      <div class="col-md-4">
        <input type="text" name="isbn" class="form-control" placeholder="Filtrer par ISBN" value="<?= htmlspecialchars($isbn) ?>">
      </div>
      <div class="col-12 d-flex justify-content-end mt-2">
        <button type="submit" class="btn btn-primary me-2">Rechercher</button>
        <a href="liste_livres.php" class="btn btn-secondary">Réinitialiser</a>
      </div>
    </form>

    <!-- Liste des livres -->
    <table class="table table-bordered table-striped">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Titre</th>
          <th>Auteur</th>
          <th>ISBN</th>
          <th>Date de publication</th>
          <th>Quantité</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($livres) > 0): ?>
          <?php foreach ($livres as $livre): ?>
            <tr>
              <td><?= $livre['id_livre'] ?></td>
              <td><?= htmlspecialchars($livre['titre']) ?></td>
              <td><?= htmlspecialchars($livre['auteur']) ?></td>
              <td><?= htmlspecialchars($livre['isbn']) ?></td>
              <td><?= $livre['date_publication'] ?></td>
              <td><?= $livre['quantite_disponible'] ?> / <?= $livre['quantite_totale'] ?></td>
              <td><?= ucfirst($livre['statut']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center text-muted">Aucun livre trouvé.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
