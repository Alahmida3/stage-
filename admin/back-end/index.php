<?php
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

require_once '../../include/init.php';

if (!isset($mydb) || !($mydb instanceof Database)) {
    die("<p style='color: red;'>ERREUR: La connexion à la base de données n'a pas été établie. Vérifiez init.php et data.php.</p>");
}

function message($msg="", $type="info") {
    if(!empty($msg)) {
        $_SESSION['message'] = "<div class='alert alert-{$type}'>{$msg}</div>";
    } else {
        return isset($_SESSION['message']) ? $_SESSION['message'] : "";
    }
}
function display_session_message() {
    if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
}

$loggedInUser = $_SESSION['user_login'] ?? 'Admin';

$view = $_GET['view'] ?? 'products';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

switch ($view) {
    case 'products':
        $pageTitle = "Liste des Produits";
        break;
    case 'categories':
        $pageTitle = "Liste des Catégories";
        break;
    case 'users':
        $pageTitle = "Liste des Utilisateurs";
        break;
    case 'orders':
        $pageTitle = "Liste des Commandes";
        break;
    default:
        $pageTitle = "Tableau de Bord";
        break;
}

if ($action == 'edit' && $id > 0) {
    if ($view == 'products') {
        $pageTitle = "Éditer un Produit";
    } elseif ($view == 'categories') {
        $pageTitle = "Éditer une Catégorie";
    } elseif ($view == 'users') {
        $pageTitle = "Éditer un Utilisateur";
    } elseif ($view == 'orders') {
        $pageTitle = "Éditer une Commande";
    }
} elseif ($action == 'add') {
    if ($view == 'products') {
        $pageTitle = "Ajouter un Nouveau Produit";
    } elseif ($view == 'categories') {
        $pageTitle = "Ajouter une Nouvelle Catégorie";
    } elseif ($view == 'users') {
        $pageTitle = "Ajouter un Nouvel Utilisateur";
    } elseif ($view == 'orders') {
        $pageTitle = "Créer une Nouvelle Commande";
    }
}


if ($view == 'products' && $action == 'delete' && $id > 0) {
    try {
        $mydb->setQuery("SELECT image FROM produit WHERE id = " . $mydb->escape_value($id));
        $product_image = $mydb->loadSingleResult();
        if ($product_image && !empty($product_image->image)) {
            $file_to_delete = PRODUCT_IMAGE_SERVER_UPLOAD_PATH . basename($product_image->image);
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
        }

        $mydb->setQuery("DELETE FROM produit WHERE id = " . $mydb->escape_value($id));
        $mydb->executeQuery();
        if ($mydb->affected_rows() > 0) {
            message("Produit supprimé avec succès !", "success");
        } else {
            message("Erreur lors de la suppression du produit ou produit non trouvé.", "danger");
        }
    } catch (Exception $e) {
        message("Erreur de base de données lors de la suppression: " . htmlspecialchars($e->getMessage()), "danger");
    }
    header('Location: index.php?view=products');
    exit;
}

if ($view == 'categories' && $action == 'delete' && $id > 0) {
    try {
        $mydb->setQuery("DELETE FROM categorie WHERE id = " . $mydb->escape_value($id));
        $mydb->executeQuery();
        if ($mydb->affected_rows() > 0) {
            message("Catégorie supprimée avec succès !", "success");
        } else {
            message("Erreur lors de la suppression de la catégorie ou catégorie non trouvée.", "danger");
        }
    } catch (Exception $e) {
        message("Erreur de base de données lors de la suppression: " . htmlspecialchars($e->getMessage()), "danger");
    }
    header('Location: index.php?view=categories');
    exit;
}

if ($view == 'users' && $action == 'delete' && $id > 0) {
    try {
        $mydb->setQuery("DELETE FROM user WHERE id = " . $mydb->escape_value($id));
        $mydb->executeQuery();
        if ($mydb->affected_rows() > 0) {
            message("Utilisateur supprimé avec succès !", "success");
        } else {
            message("Erreur lors de la suppression de l'utilisateur ou utilisateur non trouvé.", "danger");
        }
    } catch (Exception $e) {
        message("Erreur de base de données lors de la suppression: " . htmlspecialchars($e->getMessage()), "danger");
    }
    header('Location: index.php?view=users');
    exit;
}

if ($view == 'orders' && $action == 'delete' && $id > 0) {
    try {
        $mydb->setQuery("DELETE FROM commande WHERE id_commande = " . $mydb->escape_value($id));
        $mydb->executeQuery();
        if ($mydb->affected_rows() > 0) {
            message("Commande supprimée avec succès !", "success");
        } else {
            message("Erreur lors de la suppression de la commande ou commande non trouvée.", "danger");
        }
    } catch (Exception $e) {
        message("Erreur de base de données lors de la suppression: " . htmlspecialchars($e->getMessage()), "danger");
    }
    header('Location: index.php?view=orders');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product']) || isset($_POST['update_product'])) {
        $is_update = isset($_POST['update_product']);
        $product_id = $is_update ? $_POST['id'] : 0;
        $nom_produit = $mydb->escape_value($_POST['nom_produit']);
        $description = $mydb->escape_value($_POST['description']);
        $prix = $mydb->escape_value($_POST['prix']);
        $id_categorie = $mydb->escape_value($_POST['id_categorie']);
        $date_creation = !empty($_POST['date_creation']) ? $mydb->escape_value($_POST['date_creation']) : null;


        $image_path_for_db = $is_update ? ($_POST['current_image'] ?? '') : '';
        
        if (!is_dir(PRODUCT_IMAGE_SERVER_UPLOAD_PATH)) {
            mkdir(PRODUCT_IMAGE_SERVER_UPLOAD_PATH, 0777, true);
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $image_name = basename($_FILES['image']['name']);
            $full_server_file_path = PRODUCT_IMAGE_SERVER_UPLOAD_PATH . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_server_file_path)) {
                $image_path_for_db = PRODUCT_IMAGE_WEB_DIR . $image_name;
            } else {
                message("Erreur lors de l'upload de l'image.", "danger");
                if (!$is_update) $image_path_for_db = '';
            }
        }

        try {
            if ($is_update) {
                $query = "UPDATE produit SET 
                    nom_produit = '{$nom_produit}', 
                    description = '{$description}', 
                    prix = '{$prix}', 
                    image = '{$image_path_for_db}', 
                    id_categorie = '{$id_categorie}'";
                if ($date_creation) {
                    $query .= ", date_creation = '{$date_creation}'";
                }
                $query .= " WHERE id = '{$product_id}'";
                $message_success = "Produit mis à jour avec succès !";
                $message_no_change = "Aucune modification apportée au produit ou produit non trouvé.";
            } else {
                $query = "INSERT INTO produit (nom_produit, description, prix, image, id_categorie";
                if ($date_creation) {
                    $query .= ", date_creation";
                }
                $query .= ") VALUES ('{$nom_produit}', '{$description}', '{$prix}', '{$image_path_for_db}', '{$id_categorie}'";
                if ($date_creation) {
                    $query .= ", '{$date_creation}'";
                }
                $query .= ")";
                $message_success = "Nouveau produit ajouté avec succès !";
                $message_no_change = "Erreur lors de l'ajout du produit.";
            }
            $mydb->setQuery($query);
            $mydb->executeQuery();
            
            if ($mydb->affected_rows() > 0) {
                message($message_success, "success");
            } else {
                message($message_no_change, "info");
            }
        } catch (Exception $e) {
            message("Erreur de base de données: " . htmlspecialchars($e->getMessage()), "danger");
        }
        header('Location: index.php?view=products');
        exit;
    }

    if (isset($_POST['add_category']) || isset($_POST['update_category'])) {
        $is_update = isset($_POST['update_category']);
        $category_id = $is_update ? $_POST['id'] : 0;
        $nom = $mydb->escape_value($_POST['nom']);
        $description = $mydb->escape_value($_POST['description']);
        $date_creation = !empty($_POST['date_creation']) ? $mydb->escape_value($_POST['date_creation']) : null;

        try {
            if ($is_update) {
                $query = "UPDATE categorie SET 
                    nom = '{$nom}', 
                    description = '{$description}'";
                if ($date_creation) {
                    $query .= ", date_creation = '{$date_creation}'";
                }
                $query .= " WHERE id = '{$category_id}'";
                $message_success = "Catégorie mise à jour avec succès !";
                $message_no_change = "Aucune modification apportée à la catégorie ou catégorie non trouvée.";
            } else {
                $query = "INSERT INTO categorie (nom, description";
                if ($date_creation) {
                    $query .= ", date_creation";
                }
                $query .= ") VALUES ('{$nom}', '{$description}'";
                if ($date_creation) {
                    $query .= ", '{$date_creation}'";
                }
                $query .= ")";
                $message_success = "Nouvelle catégorie ajoutée avec succès !";
                $message_no_change = "Erreur lors de l'ajout de la catégorie.";
            }
            $mydb->setQuery($query);
            $mydb->executeQuery();
            if ($mydb->affected_rows() > 0) {
                message($message_success, "success");
            } else {
                message($message_no_change, "info");
            }
        } catch (Exception $e) {
            message("Erreur de base de données: " . htmlspecialchars($e->getMessage()), "danger");
        }
        header('Location: index.php?view=categories');
        exit;
    }

    if (isset($_POST['add_user']) || isset($_POST['update_user'])) {
        $is_update = isset($_POST['update_user']);
        $user_id = $is_update ? $_POST['id'] : 0;
        $login = $mydb->escape_value($_POST['login']);
        $password = $mydb->escape_value($_POST['password']);

        try {
            if ($is_update) {
                $query = "UPDATE user SET 
                    login = '{$login}', 
                    password = '{$password}' 
                    WHERE id = '{$user_id}'";
                $message_success = "Utilisateur mis à jour avec succès !";
                $message_no_change = "Aucune modification apportée à l'utilisateur ou utilisateur non trouvé.";
            } else {
                $query = "INSERT INTO user (login, password) 
                          VALUES ('{$login}', '{$password}')";
                $message_success = "Nouvel utilisateur ajouté avec succès !";
                $message_no_change = "Erreur lors de l'ajout de l'utilisateur.";
            }
            $mydb->setQuery($query);
            $mydb->executeQuery();
            if ($mydb->affected_rows() > 0) {
                message($message_success, "success");
            } else {
                message($message_no_change, "info");
            }
        } catch (Exception $e) {
            message("Erreur de base de données: " . htmlspecialchars($e->getMessage()), "danger");
        }
        header('Location: index.php?view=users');
        exit;
    }
    
    if (isset($_POST['add_order']) || isset($_POST['update_order'])) {
        $is_update = isset($_POST['update_order']);
        $id_commande = $is_update ? $_POST['id_commande'] : 0;
        $id_produit = $mydb->escape_value($_POST['id_produit']);
        $quantite_commandee = $mydb->escape_value($_POST['quantite_commandee']);
        $prix_commande = $mydb->escape_value($_POST['prix_commande']);
        $numero_commande = $mydb->escape_value($_POST['numero_commande']);
        $id_utilisateur = $mydb->escape_value($_POST['id_utilisateur']);

        try {
            if ($is_update) {
                $query = "UPDATE commande SET 
                    id_produit = '{$id_produit}', 
                    quantite_commandee = '{$quantite_commandee}', 
                    prix_commande = '{$prix_commande}', 
                    numero_commande = '{$numero_commande}',
                    id_utilisateur = '{$id_utilisateur}'
                    WHERE id_commande = '{$id_commande}'";
                $message_success = "Commande mise à jour avec succès !";
                $message_no_change = "Aucune modification apportée à la commande ou commande non trouvée.";
            } else {
                $query = "INSERT INTO commande (id_produit, quantite_commandee, prix_commande, numero_commande, id_utilisateur) 
                          VALUES ('{$id_produit}', '{$quantite_commandee}', '{$prix_commande}', '{$numero_commande}', '{$id_utilisateur}')";
                $message_success = "Nouvelle commande créée avec succès !";
                $message_no_change = "Erreur lors de la création de la commande.";
            }
            $mydb->setQuery($query);
            $mydb->executeQuery();
            if ($mydb->affected_rows() > 0) {
                message($message_success, "success");
            } else {
                message($message_no_change, "info");
            }
        } catch (Exception $e) {
            message("Erreur de base de données: " . htmlspecialchars($e->getMessage()), "danger");
        }
        header('Location: index.php?view=orders');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | E-Commerce</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f7f6;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background-color: #eeeeee;
            color: #333;
            height: 100vh;
            padding-top: 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
        }
        
        .sidebar-header {
            background-color: #eeeeee;
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #ccc;
            color: #000;
            font-size: 1.2em;
            font-weight: bold;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }
        .sidebar ul li {
            margin-bottom: 10px;
        }
        .sidebar ul li a {
            display: block;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .sidebar ul li a:hover, .sidebar ul li.active a {
            background-color: #dddddd;
            border-left: 5px solid #3498db;
            color: #000;
        }
        .sidebar ul li a .fas {
            margin-right: 10px;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 250px;
        }

        .header {
            background-color: white;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            position: sticky;
            top: 0;
            z-index: 999;
            background-color: white;
        }
        
        .header h1 {
            margin: 0;
            font-size: 1.8em;
            color: #333;
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }

        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 30px;
        }
        .table-container h3 {
            color: #333;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-container th, .table-container td {
            padding: 12px;
            border: 1px solid #eee;
            text-align: left;
        }
        .table-container th {
            background-color: #f8f8f8;
            font-weight: bold;
            color: #555;
        }
        .table-container tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .table-container tr:hover {
            background-color: #f0f0f0;
        }
        table img { max-width: 50px; height: auto; border-radius: 4px; }
        .action-buttons a {
            margin-right: 5px;
            text-decoration: none;
            color: #3498db;
        }
        .action-buttons a:hover {
            text-decoration: underline;
        }
        .table-header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .table-header-controls .search-add {
            display: flex;
            gap: 10px;
        }
        .table-header-controls .search-add input[type="text"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .new-item-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s ease;
        }
        .new-item-btn:hover {
            background-color: #218838;
        }
        .show-entries { margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .show-entries select { padding: 5px; border-radius: 4px; border: 1px solid #ccc; }
        .bulk-actions { margin-top: 15px; display: flex; gap: 10px; align-items: center; }
        .bulk-actions select { padding: 5px; border-radius: 4px; border: 1px solid #ccc; }
        .bulk-actions button { padding: 8px 15px; background-color: #dc3545; color: white; border: none; border-radius: 4%px; cursor: pointer; transition: background-color 0.3s ease; }
        .bulk-actions button:hover { background-color: #c82333; }
        .pagination { display: flex; justify-content: flex-end; align-items: center; margin-top: 15px; }
        .pagination button, .pagination a { background-color: #f0f0f0; border: 1px solid #ddd; padding: 8px 12px; cursor: pointer; margin-left: 5px; border-radius: 4px; text-decoration: none; color: #333; }
        .pagination button.active, .pagination a.active { background-color: #3498db; color: white; border-color: #3498db; }

        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 20px auto;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        .form-container input[type="text"],
        .form-container input[type="number"],
        .form-container input[type="password"],
        .form-container textarea,
        .form-container select,
        .form-container input[type="date"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }
        .form-container input[type="file"] {
            margin-bottom: 15px;
        }
        .form-container input[type="submit"] {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }
        .form-container input[type="submit"]:hover {
            background-color: #2980b9;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .form-actions .back-btn {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .form-actions .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            E-Commerce
        </div>
        <ul>
            <li class="<?= ($view == 'products' || !isset($_GET['view'])) ? 'active' : '' ?>"><a href="index.php?view=products"><i class="fas fa-box-open"></i> Produits</a></li>
            <li class="<?= ($view == 'categories') ? 'active' : '' ?>"><a href="index.php?view=categories"><i class="fas fa-tags"></i> Catégories</a></li>
            <li class="<?= ($view == 'users') ? 'active' : '' ?>"><a href="index.php?view=users"><i class="fas fa-users"></i> Utilisateurs</a></li>
            <li class="<?= ($view == 'orders') ? 'active' : '' ?>"><a href="index.php?view=orders"><i class="fas fa-shopping-cart"></i> Commandes</a></li>
            <li><a href="home.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <a href="home.php" class="logout-btn">Déconnexion</a>
        </div>
        <?php display_session_message(); ?>

        <?php
        switch ($view) {
            case 'products':
                if ($action == 'edit' && $id > 0) {
                    $product_to_edit = null;
                    try {
                        $mydb->setQuery("SELECT id, nom_produit, prix, description, image, id_categorie, date_creation FROM produit WHERE id = " . $mydb->escape_value($id));
                        $product_to_edit = $mydb->loadSingleResult();
                    } catch (Exception $e) {
                        echo "<p style='color: red;'>Erreur lors du chargement du produit: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }

                    if ($product_to_edit) {
                        ?>
                        <div class="form-container">
                            <h3>Éditer Produit: <?= htmlspecialchars($product_to_edit->nom_produit ?? '') ?></h3>
                            <form action="index.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="view" value="products">
                                <input type="hidden" name="update_product" value="1">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($product_to_edit->id ?? '') ?>">
                                <input type="hidden" name="current_image" value="<?= htmlspecialchars($product_to_edit->image ?? '') ?>">
                                
                                <label for="nom_produit">Nom du Produit:</label>
                                <input type="text" id="nom_produit" name="nom_produit" value="<?= htmlspecialchars($product_to_edit->nom_produit ?? '') ?>" required>

                                <label for="description">Description:</label>
                                <textarea id="description" name="description" rows="5"><?= htmlspecialchars($product_to_edit->description ?? '') ?></textarea>

                                <label for="prix">Prix:</label>
                                <input type="number" id="prix" name="prix" value="<?= htmlspecialchars($product_to_edit->prix ?? '') ?>" step="0.01" required>

                                <label for="id_categorie">ID Catégorie:</label>
                                <input type="text" id="id_categorie" name="id_categorie" value="<?= htmlspecialchars($product_to_edit->id_categorie ?? '') ?>">

                                <label for="date_creation">Date de Création:</label>
                                <input type="date" id="date_creation" name="date_creation" value="<?= htmlspecialchars($product_to_edit->date_creation ? date('Y-m-d', strtotime($product_to_edit->date_creation)) : '') ?>">

                                <label for="image">Image actuelle:</label>
                                <?php if (!empty($product_to_edit->image)): ?>
<img src="<?= htmlspecialchars(web_root . ($product_to_edit->image ?? '')) ?>" alt="image " style="max-width: 150px; display: block; margin-bottom: 10px;">                                <?php else: ?>
                                    <p>Pas d'image actuelle.</p>
                                <?php endif; ?>
                                <label for="image">Changer l'Image:</label>
                                <input type="file" id="image" name="image">

                                <div class="form-actions">
                                    <a href="index.php?view=products" class="back-btn">Annuler</a>
                                    <input type="submit" value="Mettre à jour le Produit">
                                </div>
                            </form>
                        </div>
                        <?php
                    } else {
                        echo "<p class='alert alert-danger'>Produit non trouvé.</p>";
                        echo '<a href="index.php?view=products">Retour à la liste des produits</a>';
                    }
                } elseif ($action == 'add') {
                    ?>
                    <div class="form-container">
                        <h3>Ajouter un Nouveau Produit</h3>
                        <form action="index.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="view" value="products">
                            <input type="hidden" name="add_product" value="1">
                            
                            <label for="nom_produit">Nom du Produit:</label>
                            <input type="text" id="nom_produit" name="nom_produit" required>

                            <label for="description">Description:</label>
                            <textarea id="description" name="description" rows="5"></textarea>

                            <label for="prix">Prix:</label>
                            <input type="number" id="prix" name="prix" step="0.01" required>

                            <label for="id_categorie">ID Catégorie:</label>
                            <input type="text" id="id_categorie" name="id_categorie">

                            <label for="date_creation">Date de Création:</label>
                            <input type="date" id="date_creation" name="date_creation" value="<?= date('Y-m-d') ?>">

                            <label for="image">Image:</label>
                            <input type="file" id="image" name="image">

                            <div class="form-actions">
                                <a href="index.php?view=products" class="back-btn">Annuler</a>
                                <input type="submit" value="Ajouter le Produit">
                            </div>
                        </form>
                    </div>
                    <?php
                } else {
                    $products = [];
                    try {
                        $mydb->setQuery("SELECT id, nom_produit, prix, description, image, id_categorie, date_creation FROM produit"); 
                        $products = $mydb->loadResultList();
                    } catch (Exception $e) { 
                        echo "<p style='color: red;'>Erreur lors du chargement des produits: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                    ?> 
                    <div class="table-container">
                        <div class="table-header-controls">
                            <div class="show-entries">
                                Show
                                <select>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                </select>
                                entries
                            </div>
                            <div class="search-add">
                                <input type="text" placeholder="Search Products">
                                <a href="index.php?view=products&action=add" class="new-item-btn"><i class="fas fa-plus"></i> Nouveau Produit</a>
                            </div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Image</th>
                                    <th>Nom Produit</th>
                                    <th>Description</th>
                                    <th>Prix</th>
                                    <th>Id Catégorie</th>
                                    <th>Date Création</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product->id ?? '') ?></td>
                                        <td><img src="<?= htmlspecialchars(web_root . ($product->image ?? '')) ?>" alt="Product Image" style="width:50px; height:auto;"></td>
                                        <td><?= htmlspecialchars($product->nom_produit ?? '') ?></td>
                                        <td><?= htmlspecialchars($product->description ?? '') ?></td>
                                        <td><?= htmlspecialchars(number_format($product->prix ?? 0, 2)) ?></td>
                                        <td><?= htmlspecialchars($product->id_categorie ?? '') ?></td>
                                        <td><?= htmlspecialchars($product->date_creation ?? '') ?></td>
                                        <td class="action-buttons">
                                            <a href="index.php?view=products&action=edit&id=<?= htmlspecialchars($product->id ?? '') ?>">Éditer</a>
                                            <a href="index.php?view=products&action=delete&id=<?= htmlspecialchars($product->id ?? '') ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">Supprimer</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" style="text-align: center;">Aucun produit trouvé.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="bulk-actions">
                            <select name="bulk_action">
                                <option value="">Actions groupées</option>
                                <option value="delete">Supprimer la sélection</option>
                            </select>
                            <button type="submit">Appliquer</button>
                        </div>
                        <div class="pagination">
                            <span>Showing 0 to <?= count($products) ?> of <?= count($products) ?> entries</span>
                            <a href="#">Previous</a>
                            <a href="#">Next</a>
                        </div>
                    </div>
                    <?php
                }
                break;

            case 'categories':
                if ($action == 'edit' && $id > 0) {
                    $category_to_edit = null;
                    try {
                        $mydb->setQuery("SELECT id, nom, description, date_creation FROM categorie WHERE id = " . $mydb->escape_value($id));
                        $category_to_edit = $mydb->loadSingleResult();
                    } catch (Exception $e) {
                        echo "<p style='color: red;'>Erreur lors du chargement de la catégorie: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }

                    if ($category_to_edit) {
                        ?>
                        <div class="form-container">
                            <h3>Éditer Catégorie: <?= htmlspecialchars($category_to_edit->nom ?? '') ?></h3>
                            <form action="index.php" method="POST">
                                <input type="hidden" name="view" value="categories">
                                <input type="hidden" name="update_category" value="1">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($category_to_edit->id ?? '') ?>">
                                
                                <label for="nom">Nom de la Catégorie:</label>
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($category_to_edit->nom ?? '') ?>" required>

                                <label for="description">Description:</label>
                                <textarea id="description" name="description" rows="5"><?= htmlspecialchars($category_to_edit->description ?? '') ?></textarea>

                                <label for="date_creation">Date de Création:</label>
                                <input type="date" id="date_creation" name="date_creation" value="<?= htmlspecialchars($category_to_edit->date_creation ? date('Y-m-d', strtotime($category_to_edit->date_creation)) : '') ?>">

                                <div class="form-actions">
                                    <a href="index.php?view=categories" class="back-btn">Annuler</a>
                                    <input type="submit" value="Mettre à jour la Catégorie">
                                </div>
                            </form>
                        </div>
                        <?php
                    } else {
                        echo "<p class='alert alert-danger'>Catégorie non trouvée.</p>";
                        echo '<a href="index.php?view=categories">Retour à la liste des catégories</a>';
                    }
                } elseif ($action == 'add') {
                    ?>
                    <div class="form-container">
                        <h3>Ajouter une Nouvelle Catégorie</h3>
                        <form action="index.php" method="POST">
                            <input type="hidden" name="view" value="categories">
                            <input type="hidden" name="add_category" value="1">
                            
                            <label for="nom">Nom de la Catégorie:</label>
                            <input type="text" id="nom" name="nom" required>

                            <label for="description">Description:</label>
                            <textarea id="description" name="description" rows="5"></textarea>

                            <label for="date_creation">Date de Création:</label>
                            <input type="date" id="date_creation" name="date_creation" value="<?= date('Y-m-d') ?>">

                            <div class="form-actions">
                                <a href="index.php?view=categories" class="back-btn">Annuler</a>
                                <input type="submit" value="Ajouter la Catégorie">
                            </div>
                        </form>
                    </div>
                    <?php
                } else {
                    $categories = [];
                    try {
                        $mydb->setQuery("SELECT id, nom, description, date_creation FROM categorie"); 
                        $categories = $mydb->loadResultList();
                    } catch (Exception $e) {
                        echo "<p style='color: red;'>Erreur lors du chargement des catégories: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                    ?>
                    <div class="table-container">
                        <div class="table-header-controls">
                            <div class="show-entries">
                                Show
                                <select>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                </select>
                                entries
                            </div>
                            <div class="search-add">
                                <input type="text" placeholder="Search Categories">
                                <a href="index.php?view=categories&action=add" class="new-item-btn"><i class="fas fa-plus"></i> Nouvelle Catégorie</a>
                            </div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Nom Catégorie</th>
                                    <th>Description</th>
                                    <th>Date Création</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($category->id ?? '') ?></td>
                                        <td><?= htmlspecialchars($category->nom ?? '') ?></td>
                                        <td><?= htmlspecialchars($category->description ?? '') ?></td>
                                        <td><?= htmlspecialchars($category->date_creation ?? '') ?></td>
                                        <td class="action-buttons">
                                            <a href="index.php?view=categories&action=edit&id=<?= htmlspecialchars($category->id ?? '') ?>">Éditer</a>
                                            <a href="index.php?view=categories&action=delete&id=<?= htmlspecialchars($category->id ?? '') ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">Supprimer</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align: center;">Aucune catégorie trouvée.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="bulk-actions">
                            <select name="bulk_action">
                                <option value="">Actions groupées</option>
                                <option value="delete">Supprimer la sélection</option>
                            </select>
                            <button type="submit">Appliquer</button>
                        </div>
                        <div class="pagination">
                            <span>Showing 0 to <?= count($categories) ?> of <?= count($categories) ?> entries</span>
                            <a href="#">Previous</a>
                            <a href="#">Next</a>
                        </div>
                    </div>
                    <?php
                }
                break;

            case 'users':
                if ($action == 'edit' && $id > 0) {
                    $user_to_edit = null;
                    try {
                        $mydb->setQuery("SELECT id, login, password FROM user WHERE id = " . $mydb->escape_value($id));
                        $user_to_edit = $mydb->loadSingleResult();
                    } catch (Exception $e) {
                        echo "<p style='color: red;'>Erreur lors du chargement de l'utilisateur: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }

                    if ($user_to_edit) {
                        ?>
                        <div class="form-container">
                            <h3>Éditer Utilisateur: <?= htmlspecialchars($user_to_edit->login ?? '') ?></h3>
                            <form action="index.php" method="POST">
                                <input type="hidden" name="view" value="users">
                                <input type="hidden" name="update_user" value="1">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($user_to_edit->id ?? '') ?>">
                                
                                <label for="login">Login:</label>
                                <input type="text" id="login" name="login" value="<?= htmlspecialchars($user_to_edit->login ?? '') ?>" required>

                                <label for="password">Password:</label>
                                <input type="text" id="password" name="password" value="<?= htmlspecialchars($user_to_edit->password ?? '') ?>" required>

                                <div class="form-actions">
                                    <a href="index.php?view=users" class="back-btn">Annuler</a>
                                    <input type="submit" value="Mettre à jour l'Utilisateur">
                                </div>
                            </form>
                        </div>
                        <?php
                    } else {
                        echo "<p class='alert alert-danger'>Utilisateur non trouvé.</p>";
                        echo '<a href="index.php?view=users">Retour à la liste des utilisateurs</a>';
                    }
                } elseif ($action == 'add') {
                    ?>
                    <div class="form-container">
                        <h3>Ajouter un Nouvel Utilisateur</h3>
                        <form action="index.php" method="POST">
                            <input type="hidden" name="view" value="users">
                            <input type="hidden" name="add_user" value="1">
                            
                            <label for="login">Login:</label>
                            <input type="text" id="login" name="login" required>

                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" required>

                            <div class="form-actions">
                                <a href="index.php?view=users" class="back-btn">Annuler</a>
                                <input type="submit" value="Ajouter l'Utilisateur">
                            </div>
                        </form>
                    </div>
                    <?php
                } else {
                    $users = [];
                    try {
                        $mydb->setQuery("SELECT id, login, password FROM user"); 
                        $users = $mydb->loadResultList();
                    } catch (Exception $e) {
                        echo "<p style='color: red;'>Erreur lors du chargement des utilisateurs: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                    ?>
                    <div class="table-container">
                        <div class="table-header-controls">
                            <div class="show-entries">
                                Show
                                <select>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                </select>
                                entries
                            </div>
                            <div class="search-add">
                                <input type="text" placeholder="Search Users">
                                <a href="index.php?view=users&action=add" class="new-item-btn"><i class="fas fa-plus"></i> Nouvel Utilisateur</a>
                            </div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Login</th>
                                    <th>Password</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user->id ?? '') ?></td>
                                        <td><?= htmlspecialchars($user->login ?? '') ?></td>
                                        <td><?= htmlspecialchars($user->password ?? '') ?></td>
                                        <td class="action-buttons">
                                            <a href="index.php?view=users&action=edit&id=<?= htmlspecialchars($user->id ?? '') ?>">Éditer</a>
                                            <a href="index.php?view=users&action=delete&id=<?= htmlspecialchars($user->id ?? '') ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align: center;">Aucun utilisateur trouvé.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="bulk-actions">
                            <select name="bulk_action">
                                <option value="">Actions groupées</option>
                                <option value="delete">Supprimer la sélection</option>
                            </select>
                            <button type="submit">Appliquer</button>
                        </div>
                        <div class="pagination">
                            <span>Showing 0 to <?= count($users) ?> of <?= count($users) ?> entries</span>
                            <a href="#">Previous</a>
                            <a href="#">Next</a>
                        </div>
                    </div>
                    <?php
                }
                break;

            case 'orders':
                if ($action == 'edit' && $id > 0) {
                    $order_to_edit = null;
                    try {
                        $mydb->setQuery("SELECT id_commande, id_produit, quantite_commandee, prix_commande, numero_commande, id_utilisateur FROM commande WHERE id_commande = " . $mydb->escape_value($id));
                        $order_to_edit = $mydb->loadSingleResult();
                    } catch (Exception $e) {
                        echo "<p style='color: red;'>Erreur lors du chargement de la commande: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }

                    if ($order_to_edit) {
                        ?>
                        <div class="form-container">
                            <h3>Éditer Commande: <?= htmlspecialchars($order_to_edit->numero_commande ?? '') ?></h3>
                            <form action="index.php" method="POST">
                                <input type="hidden" name="view" value="orders">
                                <input type="hidden" name="update_order" value="1">
                                <input type="hidden" name="id_commande" value="<?= htmlspecialchars($order_to_edit->id_commande ?? '') ?>">
                                
                                <label for="id_produit">ID Produit:</label>
                                <input type="number" id="id_produit" name="id_produit" value="<?= htmlspecialchars($order_to_edit->id_produit ?? '') ?>" required>

                                <label for="quantite_commandee">Quantité commandée:</label>
                                <input type="number" id="quantite_commandee" name="quantite_commandee" value="<?= htmlspecialchars($order_to_edit->quantite_commandee ?? '') ?>" required>

                                <label for="prix_commande">Prix Commande:</label>
                                <input type="number" id="prix_commande" name="prix_commande" value="<?= htmlspecialchars($order_to_edit->prix_commande ?? '') ?>" step="0.01" required>
                                
                                <label for="numero_commande">Numéro Commande:</label>
                                <input type="text" id="numero_commande" name="numero_commande" value="<?= htmlspecialchars($order_to_edit->numero_commande ?? '') ?>" required>

                                <label for="id_utilisateur">ID Utilisateur:</label>
                                <input type="number" id="id_utilisateur" name="id_utilisateur" value="<?= htmlspecialchars($order_to_edit->id_utilisateur ?? '') ?>" required>

                                <div class="form-actions">
                                    <a href="index.php?view=orders" class="back-btn">Annuler</a>
                                    <input type="submit" value="Mettre à jour la Commande">
                                </div>
                            </form>
                        </div>
                        <?php
                    } else {
                        echo "<p class='alert alert-danger'>Commande non trouvée.</p>";
                        echo '<a href="index.php?view=orders">Retour à la liste des commandes</a>';
                    }
                } elseif ($action == 'add') {
                    ?>
                    <div class="form-container">
                        <h3>Créer une Nouvelle Commande</h3>
                        <form action="index.php" method="POST">
                            <input type="hidden" name="view" value="orders">
                            <input type="hidden" name="add_order" value="1">
                            
                            <label for="id_produit">ID Produit:</label>
                            <input type="number" id="id_produit" name="id_produit" required>

                            <label for="quantite_commandee">Quantité commandée:</label>
                            <input type="number" id="quantite_commandee" name="quantite_commandee" required>

                            <label for="prix_commande">Prix Commande:</label>
                            <input type="number" id="prix_commande" name="prix_commande" step="0.01" required>
                            
                            <label for="numero_commande">Numéro Commande:</label>
                            <input type="text" id="numero_commande" name="numero_commande" required>

                            <label for="id_utilisateur">ID Utilisateur:</label>
                            <input type="number" id="id_utilisateur" name="id_utilisateur" required>

                            <div class="form-actions">
                                <a href="index.php?view=orders" class="back-btn">Annuler</a>
                                <input type="submit" value="Créer la Commande">
                            </div>
                             
                        </form>
                    </div>
                    <?php
                } else {
                    $orders = [];
                    try {
                        $mydb->setQuery("SELECT id_commande, id_produit, quantite_commandee, prix_commande, numero_commande, id_utilisateur FROM commande"); 
                        $orders = $mydb->loadResultList();
                    } catch (Exception $e) {
                        echo "<p style='color: red;'>Erreur lors du chargement des commandes: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                    ?>
                    <div class="table-container">
                        <div class="table-header-controls">
                            <div class="show-entries">
                                Show
                                <select>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                </select>
                                entries
                            </div>
                            <div class="search-add">
                                <input type="text" placeholder="Search Orders">
                                <a href="index.php?view=orders&action=add" class="new-item-btn"><i class="fas fa-plus"></i> Nouvelle Commande</a>
                            </div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Id Commande</th>
                                    <th>Id Produit</th>
                                    <th>Quantité</th>
                                    <th>Prix Commande</th>
                                    <th>Numéro Commande</th>
                                    <th>Id Utilisateur</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($orders)): ?>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($order->id_commande ?? '') ?></td>
                                        <td><?= htmlspecialchars($order->id_produit ?? '') ?></td>
                                        <td><?= htmlspecialchars($order->quantite_commandee ?? '') ?></td>
                                        <td><?= htmlspecialchars(number_format($order->prix_commande ?? 0, 2)) ?></td>
                                        <td><?= htmlspecialchars($order->numero_commande ?? '') ?></td>
                                        <td><?= htmlspecialchars($order->id_utilisateur ?? '') ?></td>
                                        <td class="action-buttons">
                                            <a href="index.php?view=orders&action=edit&id=<?= htmlspecialchars($order->id_commande ?? '') ?>">Éditer</a>
                                            <a href="index.php?view=orders&action=delete&id=<?= htmlspecialchars($order->id_commande ?? '') ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');">Supprimer</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" style="text-align: center;">Aucune commande trouvée.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="bulk-actions">
                            <select name="bulk_action">
                                <option value="">Actions groupées</option>
                                <option value="delete">Supprimer la sélection</option>
                            </select>
                            <button type="submit">Appliquer</button>
                        </div>
                        <div class="pagination">
                            <span>Showing 0 to <?= count($orders) ?> of <?= count($orders) ?> entries</span>
                            <a href="#">Previous</a>
                            <a href="#">Next</a>
                        </div>
                    </div>
                    <?php
                }
                break;

            default:
                header('Location: index.php?view=products');
                exit;
                break;
        }
        ?>

    </div>
</body>
</html>