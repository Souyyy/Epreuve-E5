<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du lieu est fourni dans l'URL
if (!isset($_GET['idLieu'])) {
    $_SESSION['error'] = "ID du lieu manquant.";
    header("Location: manage-places.php");
    exit();
}

$idLieu = filter_input(INPUT_GET, 'idLieu', FILTER_SANITIZE_SPECIAL_CHARS);

// Vérifiez si l'ID du lieu est un entier valide
if (!$idLieu && $idLieu !== 0) {
    $_SESSION['error'] = "ID du lieu invalide.";
    header("Location: manage-places.php");
    exit();
}

// Essayer de recuperer les données de l'ID saisie
try {
    $queryCheck = "SELECT * FROM LIEU WHERE id_lieu = :idLieu";
    $statementCheck = $connexion->prepare($queryCheck);
    $statementCheck->bindParam(":idLieu", $idLieu, PDO::PARAM_STR);
    $statementCheck->execute();
    // Récupérez les données du lieu
    $lieuData = $statementCheck->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-places.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomLieu = filter_input(INPUT_POST, 'nomLieu', FILTER_SANITIZE_SPECIAL_CHARS);
    $adresseLieu = filter_input(INPUT_POST, 'adresseLieu', FILTER_SANITIZE_SPECIAL_CHARS);
    $cpLieu = filter_input(INPUT_POST, 'cpLieu', FILTER_SANITIZE_SPECIAL_CHARS);
    $villeLieu = filter_input(INPUT_POST, 'villeLieu', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si aucun champs n'est vide
    if (empty($nomLieu) || empty($adresseLieu) || empty($cpLieu) || empty($villeLieu)) {
        $_SESSION['error'] = "Un champs ne peut pas être vide.";
        header("Location: modify-place.php");
        exit();
    }

    // Vérifiez si le lieu existe déjà
    try {

        $queryCheck = "SELECT * FROM LIEU WHERE nom_lieu = :nomLieu AND id_lieu <> :idLieu";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":idLieu", $idLieu, PDO::PARAM_STR);
        $statementCheck->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
        $statementCheck->execute();

        //Verifier que le lieu n'est pas en double
        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le lieu existe déjà.";
            header("Location: manage-places.php");
            exit();
        } else {
            // Mettez à jour les données du lieu
            $queryUpdateLieu = "UPDATE LIEU SET nom_lieu = :nomLieu, adresse_lieu = :adresseLieu, cp_lieu = :cpLieu, ville_lieu = :villeLieu WHERE id_lieu = :idLieu";
            $statementUpdateLieu = $connexion->prepare($queryUpdateLieu);
            $statementUpdateLieu->bindParam(":idLieu", $idLieu, PDO::PARAM_STR);
            $statementUpdateLieu->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
            $statementUpdateLieu->bindParam(":adresseLieu", $adresseLieu, PDO::PARAM_STR);
            $statementUpdateLieu->bindParam(":cpLieu", $cpLieu, PDO::PARAM_STR);
            $statementUpdateLieu->bindParam(":villeLieu", $villeLieu, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statementUpdateLieu->execute()) {
                $_SESSION['success'] = "Le lieu a été modifié avec succès.";
                header("Location: manage-places.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de la modification du lieu.";
                header("Location: manage-places.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: manage-places.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Modifier un lieu - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="./manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-genders.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>

        <h1>Modifier un lieu</h1>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="modify-place.php?idLieu=<?php echo $idLieu ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce lieu?')">

            <label for="nomLieu">Nom du lieu :</label>
            <input type="text" name="nomLieu" id="nomLieu" value="<?php echo $lieuData['nom_lieu'] ?>" required>

            <label for="adresseLieu">Adresse du lieu :</label>
            <input type="text" name="adresseLieu" id="adresseLieu" value="<?php echo $lieuData['adresse_lieu'] ?>" required>

            <label for="cpLieu">Code postal du lieu :</label>
            <input type="text" pattern="[0-9]*" minlength="5" maxlength="5" name="cpLieu" id="cpLieu" value="<?php echo $lieuData['cp_lieu'] ?>" required>

            <label for="villeLieu">Ville du lieu: </label>
            <input type="text" name="villeLieu" id="villeLieu" value="<?php echo $lieuData['ville_lieu'] ?>" required>

            <input type="submit" value="Modifier le lieu">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-places.php">Retour à la gestion des Lieux</a>
        </p>

    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>