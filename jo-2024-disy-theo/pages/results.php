<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/styles-computer.css">
    <link rel="stylesheet" href="../css/styles-responsive.css">
    <link rel="shortcut icon" href="../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Résultats des épreuves - Jeux Olympiques 2024</title>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="sports.php">Sports</a></li>
                <li><a href="events.php">Calendrier des épreuves</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="login.php">Accès administrateur</a></li>
            </ul>
        </nav>
    </header>
    <main>

        <h1>Résultats des épreuves</h1>
        <?php
        require_once("../database/database.php");

        try {
            // Cette requête SQL récupère les informations relatives à la participation aux épreuves depuis la base de données.
            $query = "SELECT * FROM PARTICIPER 
            INNER JOIN ATHLETE ON PARTICIPER.id_athlete = ATHLETE.id_athlete 
            INNER JOIN PAYS ON ATHLETE.id_pays = PAYS.id_pays 
            INNER JOIN GENRE ON ATHLETE.id_genre = GENRE.id_genre  
            INNER JOIN EPREUVE ON PARTICIPER.id_epreuve = EPREUVE.id_epreuve 
            ORDER BY resultat";
            // Préparation de la requête SQL et de l'execution
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table>";
                echo "<tr>";
                echo "<th class='color'>Pays</th>";
                echo "<th class='color'>Genre</th>";
                echo "<th class='color'>Athlète</th>";
                echo "<th class='color'>Epreuve</th>";
                echo "<th class='color'>Résultat</th>";
                echo "</tr>";
                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_pays']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_genre']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_athlete'] . ' ' . $row['prenom_athlete']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['resultat']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucun résultat trouvé.</p>";
            }

        }catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        ?>

        <p class="paragraph-link">
            <a class="link-home" href="../index.php">Retour Accueil</a>
        </p>

    </main>
    <footer>
        <figure>
            <img src="../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>