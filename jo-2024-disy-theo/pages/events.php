<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/styles-computer.css">
    <link rel="stylesheet" href="../css/styles-responsive.css">
    <link rel="shortcut icon" href="../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Calendrier des épreuves - Jeux Olympiques 2024</title>
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

        <h1>Calendrier des épreuves</h1>
        <?php
        require_once("../database/database.php");

        try {
            // Requête pour récupérer la date des epreuves depuis la base de données
            $query = "SELECT *, 
            DATE_FORMAT(date_epreuve, '%d/%m/%Y') AS date_epreuve, 
            DATE_FORMAT(heure_epreuve, '%Hh%i') AS heure_epreuve
            FROM EPREUVE INNER JOIN SPORT 
            ON EPREUVE.id_sport = SPORT.id_sport INNER JOIN LIEU 
            ON EPREUVE.id_lieu = LIEU.id_lieu ORDER BY nom_epreuve";
            // Préparation de la requête SQL et de l'execution
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table>";
                echo "<tr>";
                echo "<th class='color'>Catégorie</th>";
                echo "<th class='color'>Epreuve</th>";
                echo "<th class='color'>Date</th>";
                echo "<th class='color'>Heure</th>";
                echo "<th class='color'>Lieu</th>";
                echo "<th class='color'>Adresse</th>";
                echo "</tr>";
                

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_sport']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['date_epreuve']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['heure_epreuve']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_lieu']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['adresse_lieu'] . ', ' . $row['cp_lieu'] . ' ' . $row['ville_lieu']) . "</td>";
                    

                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucune épreuve trouvé.</p>";
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