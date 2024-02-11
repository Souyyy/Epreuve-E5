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
    $idAthlete = filter_input(INPUT_POST, 'idAthlete', FILTER_VALIDATE_INT);
    $idEpreuve = filter_input(INPUT_POST, 'idEpreuve', FILTER_VALIDATE_INT);
    $nouveauResultat = filter_input(INPUT_POST, 'nouveauResultat', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si les champs obligatoires sont vides
    if (empty($idAthlete) || empty($idEpreuve) || empty($nouveauResultat)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header("Location: add-result.php");
        exit();
    }

    try {
        // Requête pour ajouter un résultat
        $query = "INSERT INTO PARTICIPER (id_athlete, id_epreuve, resultat) VALUES (:idAthlete, :idEpreuve, :nouveauResultat)";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":idAthlete", $idAthlete, PDO::PARAM_INT);
        $statement->bindParam(":idEpreuve", $idEpreuve, PDO::PARAM_INT);
        $statement->bindParam(":nouveauResultat", $nouveauResultat, PDO::PARAM_STR);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le résultat a été ajouté avec succès.";
            header("Location: manage-results.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du résultat.";
            header("Location: add-result.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-result.php");
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
        <h1>Ajouter un resultat</h1>
        <?php
        // Afficher une erreur si besoin
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>

        <form action="add-result.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce résultat?')">

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
                    echo '<option value="' . $athlete['id_athlete'] . '">' . $athlete['prenom_athlete'] . ' ' . $athlete['nom_athlete'] . '</option>';
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

                // Affichez les options de la liste déroulante pour les epreuves
                while ($epreuve = $statementEpreuves->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $epreuve['id_epreuve'] . '">' . $epreuve['nom_epreuve'] . '</option>';
                }
                ?>
            </select>

            <!-- Champ Résultat (Texte) -->
            <label for="nouveauResultat">Résultat :</label>
            <input type="text" name="nouveauResultat" id="nouveauResultat" required>

            <!-- Bouton de soumission -->
            <input type="submit" value="Ajouter le Résultat">
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