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
} else {
    $idGender = filter_input(INPUT_GET, 'idGender', FILTER_VALIDATE_INT);
    // Vérifiez si l'ID du genre est un entier valide
    if (!$idGender && $idGender !== 0) {
        $_SESSION['error'] = "ID du genre invalide.";
        header("Location: manage-genders.php");
        exit();
    } else {
        try {
            // Récupérez l'ID du genre à supprimer depuis la requête GET
            $idGender = $_GET['idGender'];
            // Préparez la requête SQL pour supprimer le genre
            $sql = "DELETE FROM GENRE WHERE id_genre = :idGender";
            // Exécutez la requête SQL avec le paramètre
            $statement = $connexion->prepare($sql);
            $statement->bindParam(':idGender', $idGender, PDO::PARAM_INT);
            $statement->execute();
            // Redirigez vers la page précédente après la suppression
            header('Location: manage-genders.php');
        } catch (PDOException $e) {
            $_SESSION['error'] = "Des athletes possèdent ce genre, il est donc pas possible de le supprimer.";
            header("Location: manage-genders.php");
            exit();
            //echo 'Erreur : ' . $e->getMessage();
        }
    }
}
// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>