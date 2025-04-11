<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion des Livres</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

  <div class="container">
    <h2 class="mb-4">Formulaire de livre</h2>

    <form method="POST" action="enregistrer_livre.php" enctype="multipart/form-data">
      <!-- Titre -->
      <div class="mb-3">
        <label for="titre" class="form-label">Titre du livre</label>
        <input type="text" class="form-control" id="titre" name="titre" required>
      </div>

      <!-- Auteur (nom texte si author_id pas utilisé dans ce formulaire) -->
      <div class="mb-3">
        <label for="auteur" class="form-label">Nom de l'auteur</label>
        <input type="text" class="form-control" id="auteur" name="auteur" required>
      </div>

      <!-- ISBN -->
      <div class="mb-3">
        <label for="isbn" class="form-label">ISBN</label>
        <input type="text" class="form-control" id="isbn" name="isbn">
      </div>

      <!-- Date publication -->
      <div class="mb-3">
        <label for="date_publication" class="form-label">Date de publication</label>
        <input type="date" class="form-control" id="date_publication" name="date_publication">
      </div>

      <!-- Catégorie -->
      <div class="mb-3">
        <label for="category_id" class="form-label">Catégorie (ID)</label>
        <input type="number" class="form-control" id="category_id" name="category_id" min="1">
      </div>

      <!-- Quantités -->
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="quantite_totale" class="form-label">Quantité totale</label>
          <input type="number" class="form-control" id="quantite_totale" name="quantite_totale" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="quantite_disponible" class="form-label">Quantité disponible</label>
          <input type="number" class="form-control" id="quantite_disponible" name="quantite_disponible" required>
        </div>
      </div>

      <!-- Image -->
      <div class="mb-3">
        <label for="image_livre" class="form-label">Image du livre</label>
        <input type="file" class="form-control" id="image_livre" name="image_livre" accept="image/*">
      </div>

      <!-- Statut -->
      <div class="mb-3">
        <label for="statut" class="form-label">Statut</label>
        <select class="form-select" id="statut" name="statut">
          <option value="disponible" selected>Disponible</option>
          <option value="emprunté">Emprunté</option>
          <option value="en_reparation">En réparation</option>
        </select>
      </div>

      <!-- Boutons -->
      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <button type="reset" class="btn btn-secondary">Réinitialiser</button>
      </div>
    </form>
  </div>

</body>
</html>
