<?php
require_once '../bootstrap.php';
require_once '../config/config.php';
require_once '../config/Database.php';


Database::getInstance();
// // Vérifier si l'utilisateur est connecté
// if (!isset($_SESSION['user'])) {
//     header('Location: ../auth/Connexion.php');
//     exit();
// }

// // Vérifier si l'utilisateur est un administrateur
// if ($_SESSION['user']['user_role_id'] != 1) {
//     header('Location: ../user/dashboard.php');
//     exit();
// }

global $connexion;

// Récupération des paramètres de recherche
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construction de la requête SQL pour récupérer les utilisateurs et les étudiants
$sql = "SELECT u.user_id, u.user_nom, u.user_email, etu.etud_nom, etu.etud_prenom, etu.etud_cni, etu.etud_passport 
        FROM n_utilisateurs u
        JOIN n_etudiants etu ON u.user_id = etu.etud_user_id
        WHERE u.user_nom LIKE :search
        OR u.user_email LIKE :search
        OR etu.etud_nom LIKE :search
        OR etu.etud_prenom LIKE :search
        OR etu.etud_cni LIKE :search
        OR etu.etud_passport LIKE :search";

// Préparation de la requête avec le paramètre de recherche
$stmt = $connexion->prepare($sql);
$stmt->execute([':search' => '%' . $search . '%']);

// Récupération des résultats
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);


$statut = isset($_GET['statut']) ? $_GET['statut'] : 'disponible';  // Valeur par défaut 'disponible'

// Construction de la requête SQL pour récupérer les livres et leurs exemplaires
$sql = "SELECT l.id_livre, l.titre, ex.id_exemplaire, ex.statut, ex.etat, ex.code_barre 
        FROM n_livre l
        JOIN n_exemplaires ex ON l.id_livre = ex.id_livre
        WHERE ex.statut = :statut";  // Filtrer par statut (disponible, emprunté, etc.)

// Préparation de la requête SQL
$stmt = $connexion->prepare($sql);
$stmt->execute([':statut' => $statut]);

// Récupération des résultats
$exemplaires = $stmt->fetchAll(PDO::FETCH_ASSOC);





// Inclure le header et le sidebar
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Main Content Area -->
<div class="content w-100 m-0 pt-5"  id="content">
    <div class="container-fluid p-4">
        <!-- Header Section -->
        
        <div class="container-fluid p-4">
<!-- Form Section -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
  <div class="card-body">
    <h2 class="mb-4">Formulaire d'emprunt</h2>

    <form method="POST" action="traitement/enregistrer_emprunt.php">
      <!-- Utilisateur -->
      <div class="mb-3">
          <label for="id_utilisateur" class="form-label">Liste des utilisateurs</label>
          <select class="form-control" id="id_utilisateur" name="id_utilisateur" required>
              <option value="">Sélectionnez un utilisateur</option>
              <?php foreach ($utilisateurs as $utilisateur): ?>
                  <option value="<?php echo $utilisateur['user_id']; ?>">
                      <?php echo htmlspecialchars($utilisateur['user_nom']) . ' - ' . htmlspecialchars($utilisateur['etud_nom']) . ' ' . htmlspecialchars($utilisateur['etud_prenom']); ?>
                  </option>
              <?php endforeach; ?>
          </select>
      </div>
      <!-- Exemplaire -->
      <div class="mb-3">
          <label for="id_exemplaire" class="form-label">ID Exemplaire</label>
          <select class="form-control" id="id_exemplaire" name="id_exemplaire" required>
              <option value="">Sélectionnez un exemplaire</option>
              <?php foreach ($exemplaires as $exemplaire): ?>
                  <option value="<?php echo $exemplaire['id_exemplaire']; ?>">
                      <?php echo htmlspecialchars($exemplaire['code_barre']) . ' - ' . htmlspecialchars($exemplaire['statut']) . ' (' . htmlspecialchars($exemplaire['etat']) . ')'; ?>
                  </option>
              <?php endforeach; ?>
          </select>
      </div>

      <!-- Date emprunt (auto gérée, sauf si modif) -->
      <div class="mb-3">
        <label for="date_emprunt" class="form-label">Date d'emprunt (optionnel)</label>
        <input type="date" class="form-control" id="date_emprunt" name="date_emprunt">
      </div>

      <!-- Date retour prévue -->
      <div class="mb-3">
        <label for="date_retour_prevue" class="form-label">Date de retour prévue</label>
        <input type="date" class="form-control" id="date_retour_prevue" name="date_retour_prevue" required>
      </div>

      <!-- Date retour effective -->
      <div class="mb-3">
        <label for="date_retour_effective" class="form-label">Date de retour effective (si déjà rendu)</label>
        <input type="date" class="form-control" id="date_retour_effective" name="date_retour_effective">
      </div>

      <!-- Statut -->
      <div class="mb-3">
        <label for="statut" class="form-label">Statut</label>
        <select class="form-select" id="statut" name="statut">
          <option value="actif" selected>Actif</option>
          <option value="rendu">Rendu</option>
          <option value="en_retard">En retard</option>
          <option value="perdu">Perdu</option>
        </select>
      </div>

      <!-- Notes -->
      <div class="mb-3">
        <label for="notes" class="form-label">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Notes supplémentaires (optionnel)"></textarea>
      </div>

      <!-- Boutons -->
      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <button type="reset" class="btn btn-secondary">Réinitialiser</button>
      </div>

    </form>
  </div>
</div>

</div>
    
    
      
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">

<script>
    // Activer Select2 
    $(document).ready(function() {
        $('#id_utilisateur').select2({
            placeholder: 'Sélectionnez un utilisateur',
            allowClear: true
        });
    });
</script>