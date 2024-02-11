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
    <title>Gestion des Pays - Jeux Olympiques 2024</title>
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
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="./manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-genders.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Gestion des pays</h1>

        <div class="action-buttons">
            <button onclick="openAddCountrieForm()">Ajouter un pays</button>
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
            // Requête pour récupérer la liste des pays depuis la base de données
            $query = "SELECT * FROM PAYS";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table>";
                echo "<tr>";
                echo "<th class='color'>ID</th>";
                echo "<th class='color'>Pays</th>";
                echo "<th class='color'>Action</th>";
                echo "</tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id_pays']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_pays']) . "</td>";
                    echo "<td> <button onclick='openModifyCountrieForm({$row['id_pays']})'>Modifier</button> <button onclick='deleteCountrieConfirmation({$row['id_pays']})'>Supprimer</button></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucun pays trouvé.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }

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
        function openAddCountrieForm() {
            // Ouvrir une fenêtre pop-up avec le formulaire de modification
            // L'URL contien un paramètre "id"
            window.location.href = 'add-countrie.php';
        }

        function openModifyCountrieForm(idPays) {
            // Ajoutez ici le code pour afficher un formulaire stylisé pour modifier un pays
            // alert(idPays);
            window.location.href = 'modify-countrie.php?idPays=' + idPays;
        }

        function deleteCountrieConfirmation(idPays) {
            // Ajoutez ici le code pour afficher une fenêtre de confirmation pour supprimer un pays
            if (confirm("Êtes-vous sûr de vouloir supprimer ce lieu ?")) {
                // Ajoutez ici le code pour la suppression du sport
                // alert(idPays);
                window.location.href = 'delete-countrie.php?idPays=' + idPays;
            }
        }
    </script>

</body>

</html>