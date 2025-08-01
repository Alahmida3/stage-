<?php  
require_once("../include/init.php");
require_once("../include/config.php");

$PROID = $_GET['id'] ?? null;

if (!$PROID) {
    header('Location: view.php');
    exit;
}

try {
    $mydb->setQuery("SELECT * FROM `produit` WHERE id = '{$PROID}' LIMIT 1");
    $cur = $mydb->executeQuery();
    $singleproduct = $mydb->fetch_array($cur);

    if (!$singleproduct) {
        header('Location: view.php');
        exit;
    }

} catch (Exception $e) {
    echo "Erreur de base de données : " . htmlspecialchars($e->getMessage());
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Produit</title>
    <style>
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
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Modifier le Produit</h1>
    </div>
</div>
<form class="form-horizontal span6" action="control.php?action=edit" method="POST" enctype="multipart/form-data">
    <div class="row"> 
        <div class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="nom_produit">Nom du Produit:</label>
                <div class="col-md-8">
                    <input class="form-control input-sm" id="nom_produit" name="nom_produit" placeholder="Nom du Produit" type="text" value="<?php echo htmlspecialchars($singleproduct['nom_produit']); ?>">
                    <input id="id_produit" name="id_produit" type="hidden" value="<?php echo htmlspecialchars($singleproduct['id']); ?>">
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="description">Description:</label>
                <div class="col-md-8"> 
                    <textarea class="form-control input-sm" id="description" name="description" cols="1" rows="3"><?php echo htmlspecialchars($singleproduct['description']); ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="prix">Prix:</label>
                <div class="col-md-8">
                    <input class="form-control input-sm" id="prix" name="prix" placeholder="Prix" type="number" step="any" value="<?php echo htmlspecialchars($singleproduct['prix']); ?>">
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="image">Image actuelle:</label>
                <div class="col-md-8">
                    <?php if (!empty($singleproduct['image'])): ?>
                        <img src="<?php echo htmlspecialchars($singleproduct['image']); ?>" alt="Image du produit" style="max-width: 150px; height: auto;">
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="image_new">Nouvelle image:</label>
                <div class="col-md-8">
                    <input type="file" name="image" id="image_new" accept="image/*"/>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="date_creation">Date de Création:</label>
                <div class="col-md-8">
                    <input class="form-control input-sm" id="date_creation" name="date_creation" type="date" value="<?php echo htmlspecialchars($singleproduct['date_creation']); ?>">
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <div class="col-md-8">
                <label class="col-md-4 control-label" for="idno"></label>
                <div class="col-md-8">
                    <button class="btn btn-primary btn-sm" name="save" type="submit"><span class="fa fa-save fw-fa"></span> Enregistrer les modifications</button>
                </div>
            </div>
        </div>
    </div>
</form>
</body>
</html>