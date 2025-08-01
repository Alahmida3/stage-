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

require_once '../../include/init.php'; 

if (!isset($mydb) || !($mydb instanceof Database) || !isset($mydb->conn) || $mydb->conn->connect_error) {
    error_log("DEBUG: La connexion à la base de données n'est pas valide dans login_control.php. Erreur: " . ($mydb->conn->connect_error ?? 'Inconnue'));
    message("Erreur critique : Impossible d'établir la connexion à la base de données. Veuillez contacter l'administrateur.", "danger");
    redirect('home.php'); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    $escapedLogin = $mydb->conn->real_escape_string($login); 
    
    $query = "SELECT * FROM user WHERE login = '{$escapedLogin}' LIMIT 1";
    $mydb->setQuery($query);
    $user = $mydb->loadSingleResult();

    if ($user && ($password == $user->password)) { 
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_login'] = $user->login; 
        $_SESSION['user_role'] = $user->role ?? 'admin'; 

        message("Connexion réussie! Bienvenue, " . htmlspecialchars($login) . ".", "success");
        // MODIFICATION ICI : Redirection vers la nouvelle page principale du tableau de bord
        redirect('../index.php'); // Remonte d'un dossier (de 'back-end' à 'admin') puis va vers 'index.php'
    } else {
        message("Nom d'utilisateur ou mot de passe incorrect.", "danger");
        redirect('home.php'); 
    }
} else {
    redirect('home.php');
}
?>