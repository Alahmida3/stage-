<?php
require_once("../../include/init.php");
require_once('categorie.php'); 

$categoryid = $_GET['id'] ?? null; 

if ($categoryid === null || !is_numeric($categoryid) || intval($categoryid) <= 0) {
    echo "<script>alert('ID de catégorie invalide ou manquant! Redirection vers la liste.'); window.location.href='index.php?view=list';</script>";
    exit();
}

$category_obj = new Category(); 
$singlecategory = $category_obj->single_categorie($categoryid); 

if (!$singlecategory) {
    echo "<script>alert('Catégorie non trouvée pour l\'ID spécifié! Redirection vers la liste.'); window.location.href='index.php?view=list';</script>";
    exit();
}

$id_value = htmlspecialchars($singlecategory->id ?? ''); // OU $singlecategory->CATEGID si c'est le nom de votre propriété
$category_name_value = htmlspecialchars($singlecategory->nom ?? ''); // OU $singlecategory->CATEGORIES
$category_description_value = htmlspecialchars($singlecategory->description ?? ''); // Si vous avez une description
?>

<form class="form-horizontal span6" action="control.php?action=edit" method="POST">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Update Category</h1>
        </div> 
    </div> 

    <div class="form-group">
        <div class="col-md-8">
            <label class="col-md-4 control-label" for="CATEGORY">Category:</label>
            <div class="col-md-8">
                <input id="id" name="id" type="hidden" value="<?php echo $id_value; ?>"> 
                <input class="form-control input-sm" id="CATEGORY" name="CATEGORY" placeholder="Category" 
                       type="text" value="<?php echo $category_name_value; ?>">
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-8">
            <label class="col-md-4 control-label" for="DESCRIPTION">Description:</label>
            <div class="col-md-8">
                <textarea class="form-control" id="DESCRIPTION" name="DESCRIPTION" rows="3"><?php echo $category_description_value; ?></textarea>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <div class="col-md-8">
            <label class="col-md-4 control-label" for="idno"></label>
            <div class="col-md-8">
                <button class="btn btn-primary btn-sm" name="save" type="submit" ><span class="fa fa-save fw-fa"></span> Save</button>
                <a href="index.php?view=list" class="btn btn-secondary btn-sm">Annuler</a>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="rows">
            <div class="col-md-6">
                <label class="col-md-6 control-label" for="otherperson"></label>
                <div class="col-md-6"></div>
            </div>
            <div class="col-md-6" align="right"></div>
        </div>
    </div>
</form>