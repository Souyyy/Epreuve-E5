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
    $nomUser = filter_input(INPUT_POST, 'nomUser', FILTER_SANITIZE_SPECIAL_CHARS);
    $prenomUser = filter_input(INPUT_POST, 'prenomUser', FILTER_SANITIZE_SPECIAL_CHARS);
    $pseudoUser = filter_input(INPUT_POST, 'pseudoUser', FILTER_SANITIZE_SPECIAL_CHARS);
    // Hashage du mot de passe par BCRYPT
    $passwordUser = password_hash(filter_input(INPUT_POST, 'passwordUser', FILTER_SANITIZE_SPECIAL_CHARS), PASSWORD_BCRYPT);
    
    // Vérifiez si un champs est vide
    if (empty($nomUser) || empty($prenomUser) || empty($pseudoUser) || empty($passwordUser)) {
        $_SESSION['error'] = "Un champs ne peut pas être vide.";
        header("Location: add-user.php");
        exit();
    }

    try {
        // Vérifiez si le pseudonyme existe déjà
        $queryCheck = "SELECT login FROM UTILISATEUR WHERE login = :pseudoUser";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":pseudoUser", $pseudoUser, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'utilisateur existe déjà.";
            header("Location: add-user.php");
            exit();
        } else {
            // Requête pour ajouter un utilisateur
            $query = "INSERT INTO UTILISATEUR (nom_utilisateur, prenom_utilisateur, login, password) VALUES (:nomUser, :prenomUser, :pseudoUser, :passwordUser)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":nomUser", $nomUser, PDO::PARAM_STR);
            $statement->bindParam(":prenomUser", $prenomUser, PDO::PARAM_STR);   
            $statement->bindParam(":pseudoUser", $pseudoUser, PDO::PARAM_STR);   
            $statement->bindParam(":passwordUser", $passwordUser, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'utilisateur a été ajouté avec succès.";
                header("Location: manage-users.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'utilisateur.";
                header("Location: add-user.php");
                exit();
            }
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-user.php");
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
    <title>Ajouter un utilisateur - Jeux Olympiques 2024</title>
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
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter un Utilisateur</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-user.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet utilisateur?')">
            <label for="nomUser">Nom de l'utilisateur :</label>
            <input type="text" name="nomUser" id="nomUser" required>

            <label for="prenomUser">Prénom de l'utilisateur :</label>
            <input type="text" name="prenomUser" id="prenomUser" required>

            <label for="pseudoUser">Pseudonyme :</label>
            <input type="text" name="pseudoUser" id="pseudoUser" required>

            <label for="passwordUser">Mot de passe :</label>
            <input type="password" name="passwordUser" id="passwordUser" minlength="8" required>

            <input type="submit" value="Ajouter l'utilisateur">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-users.php">Retour à la gestion des utilisateurs</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>
</html>