<?php
session_start();
function redirect_to( $location = null ) {
  if ($location != null) {
    header("Location: {$location}");
    exit;
  }
}

function redirect($location = null) {
    $default_location = 'home.php'; 
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

function display_session_message() {
    if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <div class="connexion">
        <div class="logo">
            <img src="pdp.png" alt="logo erreur" class="image">
        </div>
        <h1 class="titre">Dashboard Admin</h1>

        <?php 
        display_session_message(); 
        ?>

        <form action="index.php" method="POST"> 
            <div class="groupe">
                <i class="fas fa-user icone"></i>
                <input type="text" id="login" name="login" placeholder="Nom d'utilisateur" class="saisie" required>
            </div>
            <div class="groupe">
                <i class="fas fa-lock icone"></i>
                <input type="password" id="password" name="password" placeholder="Mot de passe" class="saisie" required>
            </div>
            <button type="submit" class="bouton">Se connecter</button>
        </form>
    </div>
</body>
</html>