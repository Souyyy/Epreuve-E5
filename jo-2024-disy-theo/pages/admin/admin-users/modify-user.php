<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

$idUser = filter_input(INPUT_GET, 'idUser', FILTER_SANITIZE_SPECIAL_CHARS);

try {
    // Vérifiez si l'utilisateur existe déjà
    $queryCheck = "SELECT * FROM UTILISATEUR WHERE id_utilisateur = :idUser";
    $statementCheck = $connexion->prepare($queryCheck);
    $statementCheck->bindParam(":idUser", $idUser, PDO::PARAM_STR);
    $statementCheck->execute();

    // Récupérez les données de l'utilisateur
    $userData = $statementCheck->fetch(PDO::FETCH_ASSOC);

    // Vérifiez si l'utilisateur existe
    if (!$userData) {
        // L'utilisateur n'existe pas, redirigez vers une autre page par exemple
        $_SESSION['error'] = "L'utilisateur n'existe pas.";
        header("Location: manage-users.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-users.php");
    exit();
}


// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomUser = filter_input(INPUT_POST, 'nomUser', FILTER_SANITIZE_SPECIAL_CHARS);
    $prenomUser = filter_input(INPUT_POST, 'prenomUser', FILTER_SANITIZE_SPECIAL_CHARS);
    $pseudoUser = filter_input(INPUT_POST, 'pseudoUser', FILTER_SANITIZE_SPECIAL_CHARS);
    $passwordUser = filter_input(INPUT_POST, 'passwordUser', FILTER_SANITIZE_SPECIAL_CHARS);

    // Mettez à jour le mot de passe uniquement s'il est fourni dans le formulaire
    if (empty($passwordUser)) {
        $passwordUser = $userData['password'];
    } else {
        $passwordUser = password_hash($passwordUser, PASSWORD_BCRYPT);
    }

    try {
        // Vérifiez si le login existe déjà en excluant l'utilisateur actuel qui est en train d'être modifié
        $queryCheck = "SELECT login FROM UTILISATEUR WHERE login = :pseudoUser AND id_utilisateur <> :idUser";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":idUser", $idUser, PDO::PARAM_STR);
        $statementCheck->bindParam(":pseudoUser", $pseudoUser, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'utilisateur existe déjà.";
            header("Location: manage-users.php");
            exit();
        } else {
            // Mettre à jour les données de l'utilisateur
            $queryUpdateUser = "UPDATE UTILISATEUR SET nom_utilisateur = :nomUser, prenom_utilisateur = :prenomUser, login = :pseudoUser, password = :passwordUser WHERE id_utilisateur = :idUser";
            $statementUpdateUser = $connexion->prepare($queryUpdateUser);
            $statementUpdateUser->bindParam(":idUser", $idUser, PDO::PARAM_STR);
            $statementUpdateUser->bindParam(":nomUser", $nomUser, PDO::PARAM_STR);
            $statementUpdateUser->bindParam(":prenomUser", $prenomUser, PDO::PARAM_STR);
            $statementUpdateUser->bindParam(":pseudoUser", $pseudoUser, PDO::PARAM_STR);
            $statementUpdateUser->bindParam(":passwordUser", $passwordUser, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statementUpdateUser->execute()) {
                $_SESSION['success'] = "L'utilisateur a été modifié avec succès.";
                header("Location: manage-users.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de la modification de l'utilisateur.";
                header("Location: manage-users.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: manage-users.php");
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
    <title>Modifier un utilisateur - Jeux Olympiques 2024</title>
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
        <h1>Modifier un Utilisateur</h1>
        <form action="modify-user.php?idUser=<?php echo $idUser ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet utilisateur?')">
            <label for="nomUser">Nom de l'utilisateur :</label>
            <input type="text" name="nomUser" id="nomUser" value="<?php echo $userData['nom_utilisateur'] ?>" required>

            <label for="prenomUser">Prénom de l'utilisateur :</label>
            <input type="text" name="prenomUser" id="prenomUser" value="<?php echo $userData['prenom_utilisateur'] ?>" required>

            <label for="pseudoUser">Pseudonyme :</label>
            <input type="text" name="pseudoUser" id="pseudoUser" value="<?php echo $userData['login'] ?>" required>

            <label for="passwordUser">Mot de passe :</label>
            <input type="password" name="passwordUser" id="passwordUser" placeholder="Ne rien ajouter pour ne pas modifier le mot de passe actuel" minlength="8">

            <input type="submit" value="Modifier l'utilisateur">
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