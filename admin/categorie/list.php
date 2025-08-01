<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$chemin = __DIR__ . '/../../include/config.php';
if (!file_exists($chemin)) {
    echo "Fichier de configuration introuvable : " . htmlspecialchars($chemin) . "<br/>";
    exit("Le script ne peut pas continuer sans le fichier de configuration.");
}

require_once '../../include/init.php';
require_once 'categorie.php';

$mydb->setQuery("SELECT * FROM categorie");
$categories = $mydb->loadResultList();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function display_session_message() {
    if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
}

// Récupérer les informations de la dernière action depuis la session
$lastAffectedId = $_SESSION['last_affected_id'] ?? null;
$lastActionType = $_SESSION['last_action_type'] ?? null;

// Effacer les variables de session après les avoir lues
unset($_SESSION['last_affected_id']);
unset($_SESSION['last_action_type']);


$view_mode = isset($_GET['view']) ? $_GET['view'] : '';
$categoryToEdit = null;

if ($view_mode == 'edit' && isset($_GET['id'])) {
    $categoryId = (int)$_GET['id'];
    if ($categoryId > 0) {
        $category_obj = new Category();
        $categoryToEdit = $category_obj->single_categorie($categoryId);

        if (!$categoryToEdit) {
            $_SESSION['message'] = "<div class='alert alert-danger'>Catégorie introuvable pour modification.</div>";
            header('Location: list.php');
            exit;
        }
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger'>ID de catégorie invalide pour modification.</div>";
        header('Location: list.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Catégories</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-title {
            flex-grow: 1;
            text-align: center;
        }

        h1 {
            color: #0056b3;
            margin: 0;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-weight: bold;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 0.9em;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #555;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #e9e9e9;
        }

        /* Styles pour les boutons/liens d'action (par défaut gris) */
        .action-button {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f0f0f0;
            color: #333;
            text-decoration: none;
            font-size: 0.8em;
            text-align: center;
            transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
            cursor: pointer;
        }
        .action-button:hover {
            background-color: #e0e0e0;
            border-color: #bbb;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        /* Nouvelle classe pour mettre en évidence les boutons après action */
        .action-button.highlight-red {
            background-color: #dc3545; /* Un rouge fort */
            border-color: #dc3545;
            color: white; /* Texte blanc pour contraster */
        }
        .action-button.highlight-red:hover {
            background-color: #c82333; /* Rouge plus foncé au survol */
            border-color: #bd2130;
        }

        /* Styles pour les formulaires (ajout/édition) */
        form table {
            width: auto;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        form th, form td {
            padding: 10px;
        }
        form input[type="text"], form textarea {
            width: 95%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form button[type="submit"] {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            margin-right: 10px;
        }
        form button[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <div class="header-title">
            <h1>Liste des Catégories</h1>
        </div>
        <div>
            <a href="list.php?view=add" class="action-button">
                Nouveau
            </a>
        </div>
    </div>
</header>

<main>
    <?php
    display_session_message();
    ?>

    <?php if ($view_mode == 'add'): ?>
    <section>
        <h2>Ajouter une Nouvelle Catégorie</h2>
        <form method="POST" action="control.php?action=add">
            <table>
                <tr>
                    <th>Nom Catégorie</th>
                    <td><input type="text" name="CATEGORY" required></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td><textarea name="DESCRIPTION" required></textarea></td>
                </tr>
                <tr>
                    <th>Date de création</th>
                    <td><input type="text" name="DATE_CREATION" placeholder="YYYY-MM-DD HH:MM:SS" required></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <button type="submit" name="save">Enregistrer</button>
                        <a href="list.php" class="action-button cancel">Annuler</a>
                    </td>
                </tr>
            </table>
        </form>
    </section>
    <?php elseif ($view_mode == 'edit' && $categoryToEdit): ?>
    <section>
        <h2>Modifier la Catégorie : <?= htmlspecialchars($categoryToEdit->nom) ?></h2>
        <form method="POST" action="control.php?action=edit">
            <input type="hidden" name="id" value="<?= htmlspecialchars($categoryToEdit->id) ?>">
            <table>
                <tr>
                    <th>Nom Catégorie</th>
                    <td><input type="text" name="CATEGORY" value="<?= htmlspecialchars($categoryToEdit->nom) ?>" required></td>
                </tr>
                <tr>
                    <th>Description</th>
                    <td><textarea name="DESCRIPTION" required><?= htmlspecialchars($categoryToEdit->description) ?></textarea></td>
                </tr>
                <tr>
                    <th>Date de création</th>
                    <td><input type="text" name="DATE_CREATION" value="<?= htmlspecialchars($categoryToEdit->date_creation) ?>" readonly></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <button type="submit" name="save">Enregistrer les modifications</button>
                        <a href="list.php" class="action-button cancel">Annuler</a>
                    </td>
                </tr>
            </table>
        </form>
    </section>
    <?php else: ?>
    <section>
        <table>
            <thead>
                <tr>
                    <th>Nom Catégorie</th>
                    <th>Description</th>
                    <th>Date de création</th>
                    <th style="width: 15%; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($categories) && (is_array($categories) || is_object($categories))) {
                    foreach ($categories as $cat) {
                        // Détermine si le bouton doit être rouge
                        $isAffected = ($lastAffectedId == $cat->id);
                        $editClass = ($isAffected && $lastActionType == 'edit') ? ' highlight-red' : '';
                        $deleteClass = ($isAffected && $lastActionType == 'delete') ? ' highlight-red' : '';
                ?>
                        <tr>
                            <td><?= htmlspecialchars($cat->nom) ?></td>
                            <td><?= htmlspecialchars($cat->description) ?></td>
                            <td><?= htmlspecialchars($cat->date_creation) ?></td>
                            <td style="text-align: center;">
                                <a href="list.php?view=edit&id=<?= htmlspecialchars($cat->id) ?>" class="action-button edit<?= $editClass ?>">
                                    Modifier
                                </a>

                                <a href="control.php?action=delete&id=<?= htmlspecialchars($cat->id) ?>" class="action-button delete<?= $deleteClass ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="4">Aucune catégorie trouvée.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>
</main>
</body>
</html>