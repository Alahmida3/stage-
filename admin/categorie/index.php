<?php
require_once("../../include/init.php");


$view = (isset($_GET['view']) && $_GET['view'] != '') ? $_GET['view'] : '';
$header=$view;
$title="Categorie";
switch ($view) {		
	case 'list' :
		$content    = 'list.php';		
		break;

	case 'add' :
		$content    = 'add.php';		
		break;

	case 'edit' :
		$content    = 'edit.php';		
		break;

	default :
		$content    = 'list.php';		
}

?>
  
