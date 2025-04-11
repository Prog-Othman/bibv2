<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Menu de navigation</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Bibliothèque</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav">

          <!-- Gestion des livres -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="livresDropdown" role="button" data-bs-toggle="dropdown">
              Gestion des livres
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="liste_livres.php">Liste des livres</a></li>
              <li><a class="dropdown-item" href="ajouter_livre.php">Ajouter un livre</a></li>
            </ul>
          </li>

          <!-- Gestion des emprunts -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="empruntsDropdown" role="button" data-bs-toggle="dropdown">
              Gestion des emprunts
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="liste_emprunts.php">Liste des emprunts</a></li>
              <li><a class="dropdown-item" href="ajouter_emprunt.php">Ajouter un emprunt</a></li>
            </ul>
          </li>

        </ul>
      </div>
    </div>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
