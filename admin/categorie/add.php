<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$config_path = __DIR__ . '/../../include/config.php';
if (!file_exists($config_path)) {
    exit("Erreur : Fichier de configuration introuvable à " . htmlspecialchars($config_path));
}
require_once '../../include/init.php';
require_once 'categorie.php';

function redirect_to( $location = null ) {
  if ($location != null) {
    header("Location: {$location}");
    exit;
  }
}

function redirect($location = null) {
    $default_location = 'list.php';
    if ($location != null) {
        redirect_to($location);
    } else {
        redirect_to($default_location);
    }
}

function message($msg="", $type="info") {
    if(!empty($msg)) {
        $_SESSION['message'] = "<div class='alert alert-{$type}'>{$msg}</div>";
    } else {
        return isset($_SESSION['message']) ? $_SESSION['message'] : "";
    }
}

if (isset($_POST['save'])) {
    $nom_categorie = $_POST['CATEGORY'] ?? '';
    $description_categorie = $_POST['DESCRIPTION'] ?? '';
    $date_creation_input = $_POST['DATE_CREATION'] ?? '';

    if (empty($nom_categorie) || empty($description_categorie) || empty($date_creation_input)) {
        message("Tous les champs (Nom, Description, Date de création) sont requis!", "error");
        redirect('add.php');
    } else {
        if (!strtotime($date_creation_input)) {
            message("Format de date de création invalide. Utilisez YYYY-MM-DD HH:MM:SS ou YYYY-MM-DD.", "error");
            redirect('add.php');
        }

        $category = New Category();
        
        $category->nom = $nom_categorie;
        $category->description = $description_categorie;
        $category->date_creation = $date_creation_input;

        if ($category->create()) {
            message("Nouvelle catégorie [" . htmlspecialchars($nom_categorie) . "] créée avec succès!", "success");
        } else {
            message("Erreur lors de la création de la catégorie. Veuillez réessayer.", "error");
        }
        redirect('list.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Nouvelle Catégorie</title>
</head>
<body>

<form class="form-horizontal span6" action="" method="POST">

  <section class="row">
    <div class="col-lg-12">
      <h1>Ajouter une Nouvelle Catégorie</h1>
    </div>
  </section>

  <section class="form-group">
    <div class="col-md-8">
      <label class="col-md-4 control-label" for="CATEGORY">Nom de la Catégorie:</label>
      <section class="col-md-8">
        <input id="CATEGORY" name="CATEGORY" placeholder="Entrez le nom de la catégorie" type="text" value="<?= isset($nom_categorie) ? htmlspecialchars($nom_categorie) : ''; ?>" required>
      </section>
    </div>
  </section>

  <section class="form-group">
    <div class="col-md-8">
      <label class="col-md-4 control-label" for="DESCRIPTION">Description:</label>
      <section class="col-md-8">
        <textarea id="DESCRIPTION" name="DESCRIPTION" placeholder="Entrez la description de la catégorie" rows="3" required><?= isset($description_categorie) ? htmlspecialchars($description_categorie) : ''; ?></textarea>
      </section>
    </div>
  </section>

  <section class="form-group">
    <div class="col-md-8">
      <label class="col-md-4 control-label" for="DATE_CREATION">Date de création:</label>
      <section class="col-md-8">
        <input id="DATE_CREATION" name="DATE_CREATION" type="text" placeholder="YYYY-MM-DD HH:MM:SS" value="<?= isset($date_creation_input) ? htmlspecialchars($date_creation_input) : ''; ?>" required>
      </section>
    </div>
  </section>

  <div class="form-group">
    <section class="col-md-8">
      <label class="col-md-4 control-label" for="idno"></label>
      <div class="col-md-8">
        <button name="save" type="submit">
          Enregistrer
        </button>
        <a href="list.php">Annuler</a>
      </div>
    </section>
  </div>

  <section class="form-group">
    <div class="rows">
      <section class="col-md-6">
        <label class="col-md-6 control-label" for="otherperson"></label>
        <div class="col-md-6">
        </div>
      </section>

      <section class="col-md-6" align="right">
      </section>
    </div>
  </section>

</form>
</body>
</html>