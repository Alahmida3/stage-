<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function redirect($location = null) {
    if ($location != null) {
        header("Location: {$location}");
        exit;
    }
}

function message($msg = "", $type = "info") {
    if (!empty($msg)) {
        $_SESSION['message'] = "<div class='alert alert-{$type}'>{$msg}</div>";
    }
}

require_once __DIR__ . '/../../include/init.php';

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';

switch ($action) {
    case 'add':
        add_product();
        break;
    
    case 'edit':
        edit_product();
        break;
    
    case 'delete':
        delete_product();
        break;
    
    default:
        redirect('view.php');
        break;
}

function add_product() {
    global $mydb;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['save'])) {
        message("Erreur : Le formulaire d'ajout de produit n'a pas été soumis correctement.", "danger");
        redirect('add.php');
    }

    try {
        $nom_produit = $mydb->escape_value($_POST['nom_produit']);
        $description = $mydb->escape_value($_POST['description']);
        $prix = $mydb->escape_value($_POST['prix']);
        $id_categorie = $mydb->escape_value($_POST['id_categorie'] ?? 0);
        $date_creation = !empty($_POST['date_creation']) ? $mydb->escape_value($_POST['date_creation']) : date('Y-m-d');
        
        $image_path_for_db = '';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../photos/';
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $image_name = basename($_FILES['image']['name']);
            $full_server_file_path = $upload_dir . uniqid() . '_' . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_server_file_path)) {
                $image_path_for_db = 'photos/' . basename($full_server_file_path);
            } else {
                message("Erreur lors de l'upload de l'image. Le produit a été ajouté sans image.", "warning");
            }
        }
        
        $query = "INSERT INTO produit (nom_produit, description, prix, image, id_categorie, date_creation) 
                  VALUES ('{$nom_produit}', '{$description}', '{$prix}', '{$image_path_for_db}', '{$id_categorie}', '{$date_creation}')";
        
        $mydb->setQuery($query);
        $mydb->executeQuery();
        
        if ($mydb->affected_rows() > 0) {
            message("Nouveau produit ajouté avec succès !", "success");
        } else {
            message("Erreur lors de l'ajout du produit. Aucune ligne n'a été affectée.", "danger");
        }
        
    } catch (Exception $e) {
        message("Erreur de base de données: " . htmlspecialchars($e->getMessage()), "danger");
    }
    
    redirect('view.php');
}

function edit_product() {
    global $mydb;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['save'])) {
        message("Erreur : Le formulaire de modification n'a pas été soumis correctement.", "danger");
        redirect('view.php');
    }

    try {
        $id = $mydb->escape_value($_POST['id']);
        $nom_produit = $mydb->escape_value($_POST['nom_produit']);
        $description = $mydb->escape_value($_POST['description']);
        $prix = $mydb->escape_value($_POST['prix']);
        $date_creation = !empty($_POST['date_creation']) ? $mydb->escape_value($_POST['date_creation']) : date('Y-m-d');
        $id_categorie = $mydb->escape_value($_POST['id_categorie'] ?? 0);


        $update_fields = [
            "nom_produit = '{$nom_produit}'",
            "description = '{$description}'",
            "prix = '{$prix}'",
            "date_creation = '{$date_creation}'",
            "id_categorie = '{$id_categorie}'"
        ];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = '../photos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $image_name = basename($_FILES['image']['name']);
            $full_server_file_path = $upload_dir . uniqid() . '_' . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_server_file_path)) {
                $image_path_for_db = 'photos/' . basename($full_server_file_path);
                $update_fields[] = "image = '{$image_path_for_db}'";
            } else {
                message("Erreur lors de l'upload de la nouvelle image. Le produit a été mis à jour sans changer l'image.", "warning");
            }
        }

        $query = "UPDATE produit SET " . implode(', ', $update_fields) . " WHERE id = '{$id}'";
        
        $mydb->setQuery($query);
        $mydb->executeQuery();
        
        if ($mydb->affected_rows() > 0) {
            message("Produit mis à jour avec succès !", "success");
        } else {
            message("Aucune modification n'a été effectuée.", "warning");
        }

    } catch (Exception $e) {
        message("Erreur de base de données : " . htmlspecialchars($e->getMessage()), "danger");
    }
    
    redirect('view.php');
}

function delete_product() {
    global $mydb;
    
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = $mydb->escape_value($_GET['id']);
        
        $query = "DELETE FROM produit WHERE id = '{$id}' LIMIT 1";
        $mydb->setQuery($query);
        $mydb->executeQuery();
        
        if ($mydb->affected_rows() > 0) {
            message("Le produit a été supprimé avec succès!", "success");
        } else {
            message("Erreur lors de la suppression du produit. L'ID n'a peut-être pas été trouvé ou un problème est survenu.", "danger");
        }
    } else {
        message("ID de produit invalide pour la suppression!", "danger");
    }
    
    redirect('view.php');
}
?>