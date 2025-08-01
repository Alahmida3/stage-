<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

require_once __DIR__ . '/../../include/init.php';
require_once('categorie.php');

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';

switch ($action) {
    case 'add' :
        doInsert();
        break;
    
    case 'edit' :
        doEdit();
        break;
    
    case 'delete' :
        doDelete();
        break;
    default:
        redirect('list.php');
        break;
}

function doInsert(){
    if(isset($_POST['save'])){
        $nom_categorie = $_POST['CATEGORY'] ?? '';
        $description_categorie = $_POST['DESCRIPTION'] ?? ''; // Assurez-vous que ce champ existe dans add.php
        $date_creation_input = $_POST['DATE_CREATION'] ?? ''; // Assurez-vous que ce champ existe dans add.php

        if (empty($nom_categorie) || empty($description_categorie) || empty($date_creation_input)) {
            message("Tous les champs (Nom, Description, Date de création) sont requis!", "error");
            redirect('list.php?view=add'); // Redirige vers le formulaire d'ajout
        } elseif (!strtotime($date_creation_input)) {
            message("Format de date de création invalide. Utilisez YYYY-MM-DD HH:MM:SS ou YYYY-MM-DD.", "error");
            redirect('list.php?view=add');
        }
        else {
            $category = New Category();
            
            $category->nom = $nom_categorie;
            $category->description = $description_categorie;
            $category->date_creation = $date_creation_input; // Utilise la date saisie par l'utilisateur

            if ($category->create()) {
                message("Nouvelle catégorie [" . htmlspecialchars($nom_categorie) . "] créée avec succès!", "success");
                $_SESSION['last_affected_id'] = $category->id; // Stocke l'ID de la nouvelle catégorie
                $_SESSION['last_action_type'] = 'add'; // Indique que c'était un ajout
            } else {
                message("Erreur lors de la création de la catégorie. Veuillez réessayer.", "error");
            }
            redirect('list.php');
        }
    } else {
        message("Accès non autorisé à l'insertion de catégorie.", "error");
        redirect('list.php');
    }
}

function doEdit(){
    if(isset($_POST['save'])){
        $idToUpdate = $_POST['id'] ?? null;
        $nom_categorie = $_POST['CATEGORY'] ?? ''; 
        $description_categorie = $_POST['DESCRIPTION'] ?? ''; 

        if (empty($nom_categorie) || empty($description_categorie) || empty($idToUpdate)) {
            message("Tous les champs (Nom, Description) et l'ID sont requis pour la mise à jour!", "error");
            redirect('list.php?view=edit&id=' . htmlspecialchars($idToUpdate));
            return;
        }

        $idToUpdate = intval($idToUpdate);
        if ($idToUpdate <= 0) {
            message("ID de catégorie invalide pour la modification!", "error");
            redirect('list.php');
            return;
        }

        $category = New Category(); 
        
        $category->nom = $nom_categorie;         
        $category->description = $description_categorie; 

        if ($category->update($idToUpdate)) { 
            message("Catégorie [" . htmlspecialchars($nom_categorie) . "] mise à jour avec succès!", "success");
            $_SESSION['last_affected_id'] = $idToUpdate; // Stocke l'ID de la catégorie modifiée
            $_SESSION['last_action_type'] = 'edit'; // Indique que c'était une modification
        } else {
            message("Erreur lors de la mise à jour de la catégorie. L'ID n'a peut-être pas été trouvé ou un problème est survenu.", "error");
        }
        redirect('list.php'); 
    }
}

function doDelete(){
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = intval($_GET['id']); 

        if ($id <= 0) {
            message("ID de catégorie invalide pour la suppression!", "error");
            redirect('list.php');
            return;
        }

        $category = New Category(); 

        if ($category->delete($id)) {
            message("La catégorie a été supprimée avec succès!", "success"); 
            $_SESSION['last_affected_id'] = $id; // Stocke l'ID de la catégorie supprimée
            $_SESSION['last_action_type'] = 'delete'; // Indique que c'était une suppression
        } else {
            message("Erreur lors de la suppression de la catégorie. L'ID n'a peut-être pas été trouvé ou un problème est survenu.", "error");
        }
        redirect('list.php');

    } else {
        message("Aucun ID de catégorie spécifié pour la suppression!", "error");
        redirect('list.php');
    }
}
?>