<?php
// categorie.php

class Category {
    public $id;
    public $nom;
    public $description;
    public $date_creation;

    private $conn;

    public function __construct() {
        global $mydb;
        $this->conn = $mydb->conn;
    }

    // Méthode pour récupérer une seule catégorie
    public function single_categorie($id) {
        $sql = "SELECT * FROM categorie WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            error_log("Erreur single_categorie prepare: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $obj = $result->fetch_object();
        $stmt->close();
        return $obj;
    }

// Méthode pour supprimer une catégorie 
    function delete(){
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = intval($_GET['id']); 

        if ($id <= 0) {
            message("ID de catégorie invalide pour la suppression!", "error");
            redirect('list.php');
            return;
        }

        $category = New Category(); 

        // 1. Vérification de l'existence de la catégorie
        if (!$category->single_categorie($id)) {
            message("La catégorie avec l'ID " . htmlspecialchars($id) . " n'existe pas.", "error");
            redirect('list.php');
            return;
        }

        // 2. Tentative de suppression
        if ($category->delete($id)) {
            message("La catégorie a été supprimée avec succès!", "success"); 
            $_SESSION['last_affected_id'] = $id;
            $_SESSION['last_action_type'] = 'delete';
        } else {
            // Ce message est affiché uniquement si une erreur technique survient
            message("Une erreur inattendue est survenue lors de la suppression.", "error");
        }
        redirect('list.php');

    } else {
        message("Aucun ID de catégorie spécifié pour la suppression!", "error");
        redirect('list.php');
    }
}

    // Méthode pour mettre à jour une catégorie
    public function update($id) {
        $sql = "UPDATE categorie SET nom = ?, description = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            error_log("Erreur de préparation UPDATE dans categorie.php: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ssi", $this->nom, $this->description, $id);
        $result = $stmt->execute();
        if ($result) {
            if ($stmt->affected_rows > 0) {
                $stmt->close();
                return true;
            } else {
                error_log("Update: Aucune ligne affectée pour ID " . $id . ". Les données sont peut-être identiques.");
                $stmt->close();
                return true;
            }
        } else {
            error_log("Erreur d'exécution de la requête UPDATE dans categorie.php: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    // Méthode pour créer une nouvelle catégorie
    public function create() {
        $sql = "INSERT INTO categorie (nom, description, date_creation) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            error_log("Erreur de préparation INSERT dans categorie.php: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("sss", $this->nom, $this->description, $this->date_creation);
        $result = $stmt->execute();
        if ($result) {
            $this->id = $this->conn->insert_id;
            $stmt->close();
            return true;
        } else {
            error_log("Erreur d'exécution de la requête INSERT dans categorie.php: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
}