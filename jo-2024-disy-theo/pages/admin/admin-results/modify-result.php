<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}


// Vérifiez si l'identifiant unique est fourni dans l'URL
if (!isset($_GET['uniqueIdentifiantAction'])) {
    $_SESSION['error'] = "Identifiant unique manquant.";
    header("Location: manage-results.php");
    exit();
}

$uniqueIdentifiantAction = $_GET['uniqueIdentifiantAction'];
$uniqueIdentifiantAction = htmlspecialchars($uniqueIdentifiantAction);
list($id_athlete, $id_epreuve, $resultat) = explode('_', $uniqueIdentifiantAction);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nouveauResultat = filter_input(INPUT_POST, 'nouveauResultat', FILTER_SANITIZE_SPECIAL_CHARS);
    $id_epreuve = filter_input(INPUT_POST, 'idEpreuve', FILTER_VALIDATE_INT);
    $id_athlete = filter_input(INPUT_POST, 'idAthlete', FILTER_VALIDATE_INT);
    $idAthlete_old = filter_input(INPUT_POST, 'idAthlete_old', FILTER_VALIDATE_INT);
    $idEpreuve_old = filter_input(INPUT_POST, 'idEpreuve_old', FILTER_VALIDATE_INT);

    // Vérifiez si le résultat est vide
    if (empty($nouveauResultat)) {
        $_SESSION['error'] = "Le nouveau résultat ne peut pas être vide.";
        header("Location: modify-result.php?uniqueIdentifiantAction=$uniqueIdentifiantAction");
        exit();
    }


    try {
        // Requête pour mettre à jour le résultat
        $query = "UPDATE PARTICIPER SET resultat = :nouveauResultat, id_athlete = :idAthlete, id_epreuve = :idEpreuve WHERE id_athlete = :idAthlete_old AND id_epreuve = :idEpreuve_old AND resultat = :resultat";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nouveauResultat", $nouveauResultat, PDO::PARAM_STR);
        $statement->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
        $statement->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
        $statement->bindParam(":idAthlete_old", $idAthlete_old, PDO::PARAM_INT);
        $statement->bindParam(":idEpreuve_old", $idEpreuve_old, PDO::PARAM_INT);
        $statement->bindParam(":resultat", $resultat, PDO::PARAM_STR);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le résultat a été modifié avec succès.";
            header("Location: manage-results.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du résultat.";
            header("Location: modify-result.php?uniqueIdentifiantAction=$uniqueIdentifiantAction");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-result.php?uniqueIdentifiantAction=$uniqueIdentifiantAction");
        exit();
    }
}

// Récupérez les informations du résultat pour affichage dans le formulaire
try {
    $queryResult = "SELECT resultat FROM PARTICIPER WHERE id_athlete = :idAthlete AND id_epreuve = :idEpreuve AND resultat = :resultat";
    $statementResult = $connexion->prepare($queryResult);
    $statementResult->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
    $statementResult->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
    $statementResult->bindParam(":resultat", $resultat, PDO::PARAM_STR);
    $statementResult->execute();
    $statementResult->execute();

    // Verifier si il existe
    if ($statementResult->rowCount() > 0) {
        $result = $statementResult->fetch(PDO::FETCH_ASSOC);
    } else {
        // Afficher une erreur si besoin
        $_SESSION['error'] = "Résultat non trouvé.";
        header("Location: manage-results.php");
        exit();
    }
    // Afficher une erreur si besoin
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-results.php");
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
    <title>Modifier un resultat - Jeux Olympiques 2024</title>
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
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="./manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Modifier un resultat</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="modify-result.php?uniqueIdentifiantAction=<?php echo $uniqueIdentifiantAction; ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce résultat?')">
            <!-- Garder les anciennes valeurs -->
            <input type="hidden" name="idAthlete_old" value="<?php echo $id_athlete; ?>">
            <input type="hidden" name="idEpreuve_old" value="<?php echo $id_epreuve; ?>">

            <!-- Champ Athlète (Liste déroulante) -->
            <label for="idAthlete">Athlète :</label>
            <select name="idAthlete" id="idAthlete" required>
                <?php
                // Récupérez la liste des athlètes depuis la base de données
                $queryAthletes = "SELECT id_athlete, prenom_athlete, nom_athlete FROM ATHLETE";
                $statementAthletes = $connexion->prepare($queryAthletes);
                $statementAthletes->execute();

                // Affichez les options de la liste déroulante pour les athlètes
                while ($athlete = $statementAthletes->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $athlete['id_athlete'] . '"';
                    if ($athlete['id_athlete'] == $id_athlete) {
                        echo ' selected';
                    }
                    echo '>' . $athlete['prenom_athlete'] . ' ' . $athlete['nom_athlete'] . '</option>';
                }
                ?>
            </select>

            <!-- Champ Épreuve (Liste déroulante) -->
            <label for="idEpreuve">Épreuve :</label>
            <select name="idEpreuve" id="idEpreuve" required>
                <?php
                // Récupérez la liste des épreuves depuis la base de données
                $queryEpreuves = "SELECT id_epreuve, nom_epreuve FROM EPREUVE";
                $statementEpreuves = $connexion->prepare($queryEpreuves);
                $statementEpreuves->execute();

                // Affichez les options de la liste déroulante pour les épreuves
                while ($epreuve = $statementEpreuves->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $epreuve['id_epreuve'] . '"';
                    if ($epreuve['id_epreuve'] == $id_epreuve) {
                        echo ' selected';
                    }
                    echo '>' . $epreuve['nom_epreuve'] . '</option>';
                }

                ?>
            </select>

            <!-- Champ Résultat (Texte) -->
            <label for="nouveauResultat">Résultat :</label>
            <input type="text" name="nouveauResultat" id="nouveauResultat" value="<?php echo htmlspecialchars($result['resultat']); ?>" required>

            <!-- Bouton de soumission -->
            <input type="submit" value="Modifier le Résultat">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-results.php">Retour à la gestion des resultats</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>