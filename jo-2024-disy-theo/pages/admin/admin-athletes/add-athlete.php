<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
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
        header("Location: add-athlete.php");
        exit();
    }

    try {
        // Vérifiez si l'athlete existe déjà
        $queryCheck = "SELECT nom_athlete, prenom_athlete FROM ATHLETE WHERE nom_athlete = :nomAthlete AND prenom_athlete = :prenomAthlete";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statementCheck->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statementCheck->execute();

        // Verifier que l'element existe
        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'athlète existe déjà.";
            header("Location: add-athlete.php");
            exit();
        } else {
            // Si il n'existe pas executer ce code qui va l'enregistrer dans la base de donnée
            $query = "INSERT INTO ATHLETE (nom_athlete, prenom_athlete, id_pays, id_genre) VALUES (:nomAthlete, :prenomAthlete, :paysAthlete, :genreAthlete)";
            $statement = $connexion->prepare($query);
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
                header("Location: add-athlete.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-athlete.php");
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
    <title>Ajouter un athlete - Jeux Olympiques 2024</title>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
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
        <h1>Ajouter un athlete</h1>
        <?php
        // Afficher une erreur si besoin
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-athlete.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet athlete?')">

            <label for="nomAthlete">Nom de l'athlète :</label>
            <input type="text" name="nomAthlete" id="nomAthlete" required>

            <label for="prenomAthlete">Prénom de l'athlète :</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete" required>

            <label for="pays">Choississez un pays :</label>
            <select name="paysAthlete" id="pays">
                <?php
                try {
                    // Prepare et execute une requete pour trouver tout les pays de la table PAYS dans la BDD
                    $query = "SELECT * FROM PAYS";
                    $stmt = $connexion->prepare($query);
                    $stmt->execute();

                    // Boucler le resultat afin de le faire afficher dans une liste deroulante
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . $row['id_pays'] . "'>" . $row['nom_pays'] . "</option>";
                    }
                } catch (PDOException $e) {
                    // Affiche un message d'erreur si il y a eu un problème
                    echo "Erreur: " . $e->getMessage();
                }
                ?>
            </select>

            <label for="genre">Choississez un genre :</label>
            <select name="genreAthlete" id="genre">
                <?php
                try {
                    // Prepare et execute une requete pour trouver tout les genres de la table GENRE dans la BDD
                    $query = "SELECT * FROM GENRE";
                    $stmt = $connexion->prepare($query);
                    $stmt->execute();

                    // Boucler le resultat afin de le faire afficher dans une liste deroulante
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . $row['id_genre'] . "'>" . $row['nom_genre'] . "</option>";
                    }
                } catch (PDOException $e) {
                    // Affiche un message d'erreur si il y a eu un problème
                    echo "Erreur: " . $e->getMessage();
                }
                ?>
            </select>

            <input type="submit" value="Ajouter l'athlète">
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