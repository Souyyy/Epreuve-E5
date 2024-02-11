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
} else {
    $uniqueIdentifiantAction = $_GET['uniqueIdentifiantAction'];
    list($id_athlete, $id_epreuve, $resultat) = explode('_', $uniqueIdentifiantAction);

    try {
        // Préparez la requête SQL pour supprimer le résultat
        $sql = "DELETE FROM PARTICIPER WHERE id_athlete = :id_athlete AND id_epreuve = :id_epreuve AND resultat = :resultat";

        // Exécutez la requête SQL avec les paramètres
        $statement = $connexion->prepare($sql);
        $statement->bindParam(':id_athlete', $id_athlete, PDO::PARAM_INT);
        $statement->bindParam(':id_epreuve', $id_epreuve, PDO::PARAM_INT);
        $statement->bindParam(':resultat', $resultat, PDO::PARAM_STR);
        $statement->execute();
        // Redirigez vers la page précédente après la suppression
        header('Location: manage-results.php');
    } catch (PDOException $e) {
        echo 'Erreur : ' . $e->getMessage();
    }
}
?>