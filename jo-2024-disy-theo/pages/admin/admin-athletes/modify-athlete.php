<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'athlete est fourni dans l'URL
if (!isset($_GET['idAthlete'])) {
    $_SESSION['error'] = "ID de l'athlete manquant.";
    header("Location: manage-athletes.php");
    exit();
}

$idAthlete = filter_input(INPUT_GET, 'idAthlete', FILTER_SANITIZE_SPECIAL_CHARS);

// Vérifiez si l'ID de l'athlete est un entier valide
if (!$idAthlete && $idAthlete !== 0) {
    $_SESSION['error'] = "ID de l'athlete invalide.";
    header("Location: manage-athletes.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_SPECIAL_CHARS);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_SPECIAL_CHARS);
    $paysAthlete = filter_input(INPUT_POST, 'paysAthlete', FILTER_SANITIZE_SPECIAL_CHARS);
    $genreAthlete = filter_input(INPUT_POST, 'genreAthlete', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si les champs requis sont vides
    if (empty($nomAthlete) || empty($prenomAthlete) || empty($paysAthlete) || empty($genreAthlete)) {
        $_SESSION['error'] = "Un champ ne peut pas être vide.";
        header("Location: modify-athlete.php");
        exit();
    }

    // Vérifiez si l'athlete existe déjà
    try {
        // Vérifiez si l'athlete existe déjà
        $queryCheck = "SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE WHERE nom_athlete = :nomAthlete AND prenom_athlete = :prenomAthlete AND id_athlete <> :idAthlete";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":idAthlete", $idAthlete, PDO::PARAM_STR);
        $statementCheck->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statementCheck->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statementCheck->execute();

        //Verifier que le pays n'est pas en double
        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'athlète existe déjà.";
            header("Location: modify-athlete.php?idAthlete=$idAthlete");
            exit();
        }

        $query = "UPDATE ATHLETE SET nom_athlete = :nomAthlete, prenom_athlete= :prenomAthlete, id_pays = :paysAthlete, id_genre= :genreAthlete WHERE id_athlete = :idAthlete";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":idAthlete", $idAthlete, PDO::PARAM_STR);
        $statement->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":paysAthlete", $paysAthlete, PDO::PARAM_STR);
        $statement->bindParam(":genreAthlete", $genreAthlete, PDO::PARAM_STR);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'athlète a été ajouté avec succès.";
            header("Location: manage-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'athlète.";
            header("Location: modify-athlete.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: manage-athletes.php");
        exit();
    }
}

// Essayer de recuperer les données de l'ID saisie
try {
    $queryAthlete = "SELECT * FROM ATHLETE WHERE id_athlete = :idAthlete";
    $statementAthlete = $connexion->prepare($queryAthlete);
    $statementAthlete->bindParam(":idAthlete", $idAthlete, PDO::PARAM_STR);
    $statementAthlete->execute();

    if ($statementAthlete->rowCount() > 0) {
        $athlete = $statementAthlete->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Athlete non trouvé.";
        header("Location: manage-athletes.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-athletes.php");
    exit();
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
    <title>Modifier un athlete - Jeux Olympiques 2024</title>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin-users/manage-users.php">Gestion Utilisateurs</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-genders.php">Gestion Genres</a></li>
                <li><a href="./manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>

        <h1>Modifier un athlete</h1>

        <?php
        // Afficher un message d'erreur si besoin
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <!-- Formulaire permettant de modifier un athlete -->
        <form action="modify-athlete.php?idAthlete=<?php echo $idAthlete ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet athlete?')">
            <label for="nomAthlete">Nom de l'athlète :</label>
            <input type="text" name="nomAthlete" id="nomAthlete" value="<?php echo htmlspecialchars($athlete['nom_athlete']) ?>" required>

            <label for="prenomAthlete">Prénom de l'athlète :</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete" value="<?php echo htmlspecialchars($athlete['prenom_athlete']) ?>" required>

            <label for="pays">Choississez un pays :</label>
            <select name="paysAthlete" id="pays">
                <?php
                try {
                    // Récupérez la liste des PAYS depuis la base de données
                    $queryPays = "SELECT * FROM PAYS";
                    $statementPays = $connexion->prepare($queryPays);
                    $statementPays->execute();

                    // Affichez les options de la liste déroulante pour les pays
                    while ($pays = $statementPays->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $pays['id_pays'] . '"';
                        if ($pays['id_pays'] == $athlete['id_pays']) {
                            echo ' selected';
                        }
                        echo '>' . htmlspecialchars($pays['nom_pays']) . '</option>';
                    }
                } catch (PDOException $e) {
                    // Erreur si il y a un problème
                    echo "Erreur: " . $e->getMessage();
                }
                ?>
            </select>

            <label for="genre">Choississez un genre :</label>
            <select name="genreAthlete" id="genre">
                <?php
                try {
                    // Récupérez la liste des GENRE depuis la base de données
                    $queryGenre = "SELECT * FROM GENRE";
                    $statementGenre = $connexion->prepare($queryGenre);
                    $statementGenre->execute();

                    // Affichez les options de la liste déroulante pour les genres
                    while ($genre = $statementGenre->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $genre['id_genre'] . '"';
                        if ($genre['id_genre'] == $athlete['id_genre']) {
                            echo ' selected';
                        }
                        echo '>' . htmlspecialchars($genre['nom_genre']) . '</option>';
                    }
                } catch (PDOException $e) {
                    // Erreur si il y a un problème
                    echo "Erreur: " . $e->getMessage();
                }
                ?>
            </select>

            <input type="submit" value="Modifier l'athlète">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des athletes</a>
        </p>

    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>