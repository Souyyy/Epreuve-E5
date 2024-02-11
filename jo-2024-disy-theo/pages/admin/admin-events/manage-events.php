<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
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
    <title>Gestion du calendrier - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .action-buttons button {
            background-color: #1b1b1b;
            color: #d7c378;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .action-buttons button:hover {
            background-color: #d7c378;
            color: #1b1b1b;
        }
    </style>
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
        <h1>Gestion du calendrier</h1>

        <div class="action-buttons">
            <button onclick="openAddEventForm()">Ajouter une epreuve</button>
            <!-- Autres boutons... -->
        </div>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
            unset($_SESSION['success']);
        }
        ?>

        <?php
        // Connexion base de donnée
        require_once("../../../database/database.php");

        try {
            // Requête pour récupérer la date des epreuves depuis la base de données
            $query = "SELECT *, 
            DATE_FORMAT(date_epreuve, '%d/%m/%Y') AS date_epreuve, 
            DATE_FORMAT(heure_epreuve, '%Hh%i') AS heure_epreuve
            FROM EPREUVE INNER JOIN SPORT 
            ON EPREUVE.id_sport = SPORT.id_sport INNER JOIN LIEU 
            ON EPREUVE.id_lieu = LIEU.id_lieu ORDER BY nom_epreuve";
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
                echo "<th class='color'>Action</th>";
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
                    echo "<td> <button onclick='openModifyEventForm({$row['id_epreuve']})'>Modifier</button> <button onclick='deleteEventConfirmation({$row['id_epreuve']})'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucune épreuve trouvé.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        // Afficher les erreurs en PHP
        // (fonctionne à condition d’avoir activé l’option en local)
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        ?>
        <p class="paragraph-link">
            <a class="link-home" href="../admin.php">Accueil administration</a>
        </p>

    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
    
    <script>
        function openAddEventForm() {
            // Ouvrir une fenêtre pop-up avec le formulaire de modification
            // L'URL contien un paramètre "id"
            window.location.href = 'add-event.php';
        }

        function openModifyEventForm(idEvent) {
            // Ajoutez ici le code pour afficher un formulaire stylisé pour modifier un evenement
            // alert(idEvent);
            window.location.href = 'modify-event.php?idEvent=' + idEvent;
        }

        function deleteEventConfirmation(idEvent) {
            // Ajoutez ici le code pour afficher une fenêtre de confirmation pour supprimer un evenement
            if (confirm("Êtes-vous sûr de vouloir supprimer cette epreuve ?")) {
                // Ajoutez ici le code pour la suppression de l'evenement
                // alert(idEvent);
                window.location.href = 'delete-event.php?idEvent=' + idEvent;
            }
        }
    </script>

</body>

</html>