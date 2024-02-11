<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'athlete est fourni dans l'URL
if (!isset($_GET['idAthlete'])) {
    $_SESSION['error'] = "ID de l'athlète manquant.";
    header("Location: delete-athlete.php");
    exit();
} else {
    $idAthlete = filter_input(INPUT_GET, 'idAthlete', FILTER_VALIDATE_INT);
    // Vérifiez si l'ID de l'athlete est un entier valide
    if (!$idAthlete && $idAthlete !== 0) {
        $_SESSION['error'] = "ID de l'athlete invalide.";
        header("Location: manage-athletes.php");
        exit();
    } else {
        try {
            // Récupérez l'ID de l'athlete à supprimer depuis la requête GET
            $idAthlete = $_GET['idAthlete'];

            // Verifiez si l'athlete est dans la table PARTICIPER et la supprimer
            $queryCheck = "SELECT id_athlete FROM PARTICIPER WHERE id_athlete = :idAthlete";
            $statementCheck = $connexion->prepare($queryCheck);
            $statementCheck->bindParam(":idAthlete", $idAthlete, PDO::PARAM_INT);
            $statementCheck->execute();
            if ($statementCheck->rowCount() > 0) {
                $sql = "DELETE FROM PARTICIPER WHERE id_athlete = :idAthlete";
                $statement = $connexion->prepare($sql);
                $statement->bindParam(':idAthlete', $idAthlete, PDO::PARAM_INT);
                $statement->execute();
            }

            // Préparez la requête SQL pour supprimer l'athlete
            $sql = "DELETE FROM ATHLETE WHERE id_athlete = :idAthlete";
            $statement = $connexion->prepare($sql);
            $statement->bindParam(':idAthlete', $idAthlete, PDO::PARAM_INT);
            $statement->execute();

            // Redirigez vers la page précédente après la suppression
            header('Location: manage-athletes.php');

        } catch (PDOException $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }
}
