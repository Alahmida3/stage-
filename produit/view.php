<?php
require_once("../include/init.php");
require_once("../include/config.php");

session_start();

$message = '';
if (isset($_SESSION['message'])) {
    $message = '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}

$add_button_class = 'btn-primary';
if (isset($_SESSION['last_action']) && $_SESSION['last_action'] == 'add_product') {
    $add_button_class = 'btn-clicked';
    unset($_SESSION['last_action']);
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { max-width: 1200px; margin: auto; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .page-header { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .page-header h1 { margin: 0; font-size: 28px; color: #333; }
        .btn { padding: 8px 15px; border-radius: 4px; text-decoration: none; display: inline-block; cursor: pointer; }
        
        .btn-primary { 
            background-color: #6c757d;
            color: white; 
            border: 1px solid #6c757d; 
        }
        
        .btn-primary:hover { 
            background-color: #5a6268;
            border-color: #545b62; 
        }

        .btn-clicked {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white;
        }

        .table-responsive { overflow-x: auto; margin-top: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { padding: 12px 15px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
        .table th { background-color: #f2f2f2; font-weight: bold; color: #555; }
        .table tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .table tbody tr:hover { background-color: #f1f1f1; }
        .table .action-buttons .btn { margin-right: 5px; }
        .table .action-buttons .btn-info { background-color: #17a2b8; border-color: #17a2b8; color: white; }
        .table .action-buttons .btn-info:hover { background-color: #117a8b; border-color: #117a8b; }
        .table .action-buttons .btn-danger { background-color: #dc3545; border-color: #dc3545; color: white; }
        .table .action-buttons .btn-danger:hover { background-color: #c82333; border-color: #bd2130; }
        .pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        .pagination a { text-decoration: none; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; color: #007bff; }
        .pagination a:hover { background-color: #e9ecef; }
        .pagination span { font-size: 0.9em; color: #555; }
        .product-image { max-width: 80px; height: auto; border-radius: 4px; }
        .no-data { text-align: center; color: #888; padding: 20px; }
    </style>
</head>
<body>

<div class="container">
    <header class="page-header">
        <h1>Liste des Produits</h1>
        <a href="add.php" class="btn <?php echo htmlspecialchars($add_button_class); ?>"><span class="fa fa-plus-circle fw-fa"></span> Nouveau Produit</a>
    </header>

    <main>
        <?php echo $message; ?>
        <div class="table-responsive">
            <table class="table table-hover table-bordered" id="product-table">
                <thead>
                    <tr>
                        <th>Nom du Produit</th>
                        <th>Prix</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>ID Catégorie</th>
                        <th>Date de Création</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $mydb->setQuery("SELECT * FROM `produit`");
                        $cur = $mydb->executeQuery();
                        $numrows = $mydb->num_rows($cur);

                        if ($numrows > 0) {
                            while ($result = $mydb->fetch_array($cur)) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($result['nom_produit']) . '</td>';
                                echo '<td>' . htmlspecialchars($result['prix']) . ' €</td>';
                                echo '<td>' . htmlspecialchars(substr($result['description'], 0, 50)) . '...</td>';
                                echo '<td><img src="' . htmlspecialchars($result['image']) . '" alt="' . htmlspecialchars($result['nom_produit']) . '" class="product-image"></td>';
                                echo '<td>' . htmlspecialchars($result['id_categorie']) . '</td>';
                                echo '<td>' . htmlspecialchars($result['date_creation']) . '</td>';
                                echo '<td class="action-buttons">';
                                echo '<a href="edit.php?id=' . htmlspecialchars($result['id']) . '" class="btn btn-info btn-xs" title="Modifier">Modifier</a>';
                                echo '<a href="delete.php?id=' . htmlspecialchars($result['id']) . '" class="btn btn-danger btn-xs" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce produit ?\');" title="Supprimer">Supprimer</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7" class="no-data">Aucun produit trouvé.</td></tr>';
                        }
                    } catch (Exception $e) {
                        echo '<tr><td colspan="7" class="no-data">Erreur de chargement des produits : ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                        error_log("Erreur de base de données lors du chargement des produits : " . $e->getMessage());
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <span>Affichage de 1 à <?php echo $numrows; ?> sur <?php echo $numrows; ?> entrées</span>
            <div>
                <a href="#">Précédent</a>
                <a href="#">Suivant</a>
            </div>
        </div>
    </main>
</div>

</body>