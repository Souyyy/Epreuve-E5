<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'epreuve est fourni dans l'URL
if (!isset($_GET['idEvent'])) {
    $_SESSION['error'] = "ID de l'épreuve manquant.";
    header("Location: manage-events.php");
    exit();
}

$idEvent = filter_input(INPUT_GET, 'idEvent', FILTER_SANITIZE_SPECIAL_CHARS);

// Vérifiez si l'ID de l'epreuve est un entier valide
if (!$idEvent && $idEvent !== 0) {
    $_SESSION['error'] = "ID de l'epreuve est invalide.";
    header("Location: manage-events.php");
    exit();
}

// Essayer de recuperer les données de l'ID saisie
try {
    $queryCheck = "SELECT * FROM EPREUVE WHERE id_epreuve = :idEvent";
    $statementCheck = $connexion->prepare($queryCheck);
    $statementCheck->bindParam(":idEvent", $idEvent, PDO::PARAM_STR);
    $statementCheck->execute();
    // Récupérez les données de l'épreuve
    $eventData = $statementCheck->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-events.php");
    exit();
}


// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomEvent = filter_input(INPUT_POST, 'nomEvent', FILTER_SANITIZE_SPECIAL_CHARS);
    $dateEvent = filter_input(INPUT_POST, 'dateEvent', FILTER_SANITIZE_SPECIAL_CHARS);
    $heureEvent = filter_input(INPUT_POST, 'heureEvent', FILTER_SANITIZE_SPECIAL_CHARS);
    $lieux = filter_input(INPUT_POST, 'lieux', FILTER_SANITIZE_SPECIAL_CHARS);
    $categories = filter_input(INPUT_POST, 'categories', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si les champs requis sont vides
    if (empty($categories) || empty($nomEvent) || empty($dateEvent) || empty($heureEvent) || empty($lieux)) {
        $_SESSION['error'] = "Un champ ne peut pas être vide.";
        header("Location: modify-event.php?idEvent=$idEvent");
        exit();
    }

    // Vérifiez si l'epreuve existe déjà
    try {
        $queryCheck = "SELECT * FROM EPREUVE WHERE nom_epreuve = :nomEvent AND id_epreuve <> :idEvent";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":idEvent", $idEvent, PDO::PARAM_STR);
        $statementCheck->bindParam(":nomEvent", $nomEvent, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'epreuve existe déjà.";
            header("Location: modify-event.php?idEvent=$idEvent");
            exit();
        } else {


            // Mettez à jour les données de l'epreuve
            $queryUpdateEvent = "UPDATE EPREUVE SET nom_epreuve = :nomEvent, date_epreuve = :dateEvent, heure_epreuve = :heureEvent, id_lieu = :lieux, id_sport = :categories WHERE id_epreuve = :idEvent";
            $statementUpdateEvent = $connexion->prepare($queryUpdateEvent);
            $statementUpdateEvent->bindParam(":idEvent", $idEvent, PDO::PARAM_STR);
            $statementUpdateEvent->bindParam(":nomEvent", $nomEvent, PDO::PARAM_STR);
            $statementUpdateEvent->bindParam(":dateEvent", $dateEvent, PDO::PARAM_STR);
            $statementUpdateEvent->bindParam(":heureEvent", $heureEvent, PDO::PARAM_STR);
            $statementUpdateEvent->bindParam(":lieux", $lieux, PDO::PARAM_STR);
            $statementUpdateEvent->bindParam(":categories", $categories, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statementUpdateEvent->execute()) {
                $_SESSION['success'] = "L'epreuve a été modifié avec succès.";
                header("Location: manage-events.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de la modification de l'épreuve.";
                header("Location: manage-events.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: manage-events.php");
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
    <title>Modifier un evenement - Jeux Olympiques 2024</title>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="./manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-genders.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>

        <h1>Modifier un evenement</h1>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <!-- formulaire permettant la modification -->
        <form action="modify-event.php?idEvent=<?php echo $idEvent ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet evenement?')">
            <label for="categorieEvent">Choississez une catégorie :</label>
            <select name="categories" id="categorieEvent">
                <?php
                try {

                    // Récupérez la liste des sports depuis la base de données
                    $queryEvent = "SELECT * FROM SPORT";
                    $statementEvent = $connexion->prepare($queryEvent);
                    $statementEvent->execute();
                    // Afficher le resultat et ajouter "selected" pour la données utiliser
                    while ($event = $statementEvent->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $event['id_sport'] . '"';
                        if ($event['id_sport'] == $eventData['id_sport']) {
                            echo ' selected';
                        }
                        echo '>' . $event['nom_sport'] . '</option>';
                    }
                } catch (PDOException $e) {
                    // Afficher un message d'erreur si il y a lieu
                    echo "Error: " . $e->getMessage();
                }
                ?>
            </select>

            <label for="nomEvent">Nom de l'épreuve :</label>
            <input type="text" name="nomEvent" id="nomEvent" value="<?php echo $eventData['nom_epreuve'] ?>" required>

            <label for="dateEvent">Date de l'evenement :</label>
            <input type="date" name="dateEvent" id="dateEvent" value="<?php echo $eventData['date_epreuve'] ?>" required>

            <label for="heureEvent">Heure de l'evenement :</label>
            <input type="time" name="heureEvent" id="heureEvent" value="<?php echo $eventData['heure_epreuve'] ?>" required>

            <label for="lieuEvent">Choississez un lieu :</label>
            <select name="lieux" id="lieuEvent" value="<?php echo $eventData['id_lieu'] ?>">
                <?php
                try {

                    // Récupérez la liste des lieux depuis la base de données
                    $queryLieu = "SELECT * FROM LIEU";
                    $statementLieu = $connexion->prepare($queryLieu);
                    $statementLieu->execute();
                    // Afficher le resultat et ajouter "selected" pour la données utiliser
                    while ($lieux = $statementLieu->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $lieux['id_lieu'] . '"';
                        if ($lieux['id_lieu'] == $eventData['id_sport']) {
                            echo ' selected';
                        }
                        echo '>' . $lieux['nom_lieu'] . '</option>';
                    }
                } catch (PDOException $e) {
                    // Afficher un message d'erreur si il y a lieu
                    echo "Error: " . $e->getMessage();
                }
                ?>
            </select>

            <input type="submit" value="Modifier le lieu">

        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion du calendrier</a>
        </p>

    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>