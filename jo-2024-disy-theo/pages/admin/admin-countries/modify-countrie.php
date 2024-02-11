<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du pays est fourni dans l'URL
if (!isset($_GET['idPays'])) {
    $_SESSION['error'] = "ID du pays manquant.";
    header("Location: manage-countries.php");
    exit();
}

$idPays = filter_input(INPUT_GET, 'idPays', FILTER_SANITIZE_SPECIAL_CHARS);

// Vérifiez si l'ID du pays est un entier valide
if (!$idPays && $idPays !== 0) {
    $_SESSION['error'] = "ID du pays invalide.";
    header("Location: manage-countries.php");
    exit();
}

// Essayer de recuperer les données de l'ID saisie
try {
    $queryCheck = "SELECT * FROM PAYS WHERE id_pays = :idPays";
    $statementCheck = $connexion->prepare($queryCheck);
    $statementCheck->bindParam(":idPays", $idPays, PDO::PARAM_STR);
    $statementCheck->execute();
    // Récupérez les données du pays
    $paysData = $statementCheck->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-countries.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomPays = filter_input(INPUT_POST, 'nomPays', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si le champs n'est pas vide
    if (empty($nomPays)) {
        $_SESSION['error'] = "Un champs ne peut pas être vide.";
        header("Location: modify-countrie.php");
        exit();
    }

    // Vérifiez si le pays existe déjà
    try {

        $queryCheck = "SELECT nom_pays FROM PAYS WHERE nom_pays = :nomPays AND id_pays <> :idPays";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":idPays", $idPays, PDO::PARAM_STR);
        $statementCheck->bindParam(":nomPays", $nomPays, PDO::PARAM_STR);
        $statementCheck->execute();

        //Verifier que le pays n'est pas en double
        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Le pays existe déjà.";
            header("Location: manage-countries.php");
            exit();
        } else {
            // Mettez à jour les données du pays
            $queryUpdatePays = "UPDATE PAYS SET nom_pays = :nomPays WHERE id_pays = :idPays";
            $statementUpdatePays = $connexion->prepare($queryUpdatePays);
            $statementUpdatePays->bindParam(":idPays", $idPays, PDO::PARAM_STR);
            $statementUpdatePays->bindParam(":nomPays", $nomPays, PDO::PARAM_STR);
        
            // Exécutez la requête
            if ($statementUpdatePays->execute()) {
                $_SESSION['success'] = "Le pays a été modifié avec succès.";
                header("Location: manage-countries.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de la modification du pays.";
                header("Location: manage-countries.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: manage-countries.php");
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
    <title>Modifier un pays - Jeux Olympiques 2024</title>

</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
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

        <h1>Modifier un pays</h1>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <!-- Formulaire pour envoyer les modifications-->
        <form action="modify-countrie.php?idPays=<?php echo $idPays ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce pays?')">

            <label for="nomPays">Nom du pays :</label>
            <input type="text" name="nomPays" id="nomPays" value="<?php echo $paysData['nom_pays'] ?>" required>

            <input type="submit" value="Modifier le pays">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-countries.php">Retour à la gestion des Pays</a>
        </p>

    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>
</html>