<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du genre est fourni dans l'URL
if (!isset($_GET['idGender'])) {
    $_SESSION['error'] = "ID du genre manquant.";
    header("Location: manage-genders.php");
    exit();
}

$idGender = filter_input(INPUT_GET, 'idGender', FILTER_VALIDATE_INT);

// Vérifiez si l'ID du genre est un entier valide
if (!$idGender && $idGender !== 0) {
    $_SESSION['error'] = "ID du genre invalide.";
    header("Location: manage-genders.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomGenre = ucfirst(strtolower(trim(filter_input(INPUT_POST, 'nomGenre', FILTER_SANITIZE_SPECIAL_CHARS), " ")));

    // Vérifiez si le nom du genre est vide
    if (empty($nomGenre)) {
        $_SESSION['error'] = "Le nom du genre ne peut pas être vide.";
        header("Location: add-gender.php");
        exit();
    }

    try {
        // Vérifiez si le genre existe déjà
        $queryCheck = "SELECT id_genre FROM GENRE WHERE nom_genre = :nomGenre AND id_genre <> :idGender";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomGenre", $nomGenre, PDO::PARAM_STR);
        $statementCheck->bindParam(":idGender", $idGender, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le genre existe déjà.";
            header("Location: modify-gender.php?idGender=$idGender");
            exit();
        }

        // Requête pour mettre à jour le genre
        $query = "UPDATE GENRE SET nom_genre = :nomGenre WHERE id_genre = :idGender";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomGenre", $nomGenre, PDO::PARAM_STR);
        $statement->bindParam(":idGender", $idGender, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le genre a été modifié avec succès.";
            header("Location: manage-genders.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du genre.";
            header("Location: modify-gender.php?idGender=$idGender");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-gender.php?idGender=$idGender");
        exit();
    }
}

// Récupérez les informations du genre pour affichage dans le formulaire
try {
    $queryGenre = "SELECT nom_genre FROM GENRE WHERE id_genre = :idGender";
    $statementGenre = $connexion->prepare($queryGenre);
    $statementGenre->bindParam(":idGender", $idGender, PDO::PARAM_INT);
    $statementGenre->execute();

    if ($statementGenre->rowCount() > 0) {
        $genre = $statementGenre->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Genre non trouvé.";
        header("Location: manage-genders.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-genders.php");
    exit();
}

// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);

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
    <title>Modifier un Genre - Jeux Olympiques 2024</title>
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
                <li><a href="./manage-genders.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Modifier un Genre</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-gender.php?idGender=<?php echo $idGender; ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce genre?')">
            <label for="nomGenre">Nom du Genre :</label>
            <input type="text" name="nomGenre" id="nomGenre" value="<?php echo htmlspecialchars($genre['nom_genre']); ?>" required>
            <input type="submit" value="Modifier le Genre">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-genders.php">Retour à la gestion des genres</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>