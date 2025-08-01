<?php
// Inclut le fichier de configuration de la base de données
// Assurez-vous que ce chemin est correct pour votre installation
require_once("../include/config.php");
require_once("../include/init.php");
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Nouveau Produit</title>
    <style>
        /* Styles CSS de base pour la lisibilité si Bootstrap n'est pas inclus */
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-horizontal { max-width: 800px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; display: flex; align-items: center; }
        .control-label { flex: 0 0 30%; text-align: right; padding-right: 15px; }
        .col-md-8 { flex: 1; }
        .form-control { width: calc(100% - 10px); padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        textarea.form-control { resize: vertical; min-height: 80px; }
        select.form-control { height: 34px; }
        .btn-primary { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary:hover { background-color: #0056b3; }
        .page-header { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

<form class="form-horizontal" action="control.php?action=add" method="POST" enctype="multipart/form-data">
    <header>
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Ajouter un Nouveau Produit</h1>
            </div>
        </div>
    </header>

    <main>
        <input type="hidden" name="add_product" value="1">
        
        <section class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="nom_produit">Nom du Produit:</label>
                <div class="col-md-8">
                    <input class="form-control input-sm" id="nom_produit" name="nom_produit" placeholder="Nom du Produit" type="text" value="" required>
                </div>
            </div>
        </section>

        <section class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="prix">Prix:</label>
                <div class="col-md-8">
                    <input class="form-control input-sm" id="prix" step="any" name="prix" placeholder="Prix" type="number" value="" required>
                </div>
            </div>
        </section>

        <section class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="description">Description:</label>
                <div class="col-md-8">
                    <textarea class="form-control input-sm" id="description" name="description" cols="1" rows="3" required></textarea>
                </div>
            </div>
        </section>

        <section class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="image">Image:</label>
                <div class="col-md-8">
                    <input type="file" name="image" id="image" accept="image/*" required/>
                </div>
            </div>  
        </section>

        <section class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="id_categorie">ID Catégorie:</label>
                <div class="col-md-8">
                    <input class="form-control input-sm" id="id_categorie" name="id_categorie" placeholder="ID Catégorie" type="text" value="">
                </div>
            </div>
        </section>

        <section class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="date_creation">Date de Création:</label>
                <div class="col-md-8">
                    <input class="form-control input-sm" id="date_creation" name="date_creation" type="date" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
        </section>

        <footer>
            <div class="form-group">
                <div class="col-md-8">
                    <label class="col-md-4 control-label" for="idno"></label>
                    <div class="col-md-8">
                        <button class="btn btn-primary btn-sm" name="save" type="submit"><span class="fa fa-save fw-fa"></span> Enregistrer</button>
                        <button class="btn btn-primary btn-sm" name="cancel" type="reset"><span class="fa fa-save fw-fa"></span> Annuler </button>
                    </div>
                </div>
            </div>
        </footer>
    </main>
</form>
</body>