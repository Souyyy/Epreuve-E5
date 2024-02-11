<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID du lieu est fourni dans l'URL
if (!isset($_GET['idLieu'])) {
    $_SESSION['error'] = "ID du lieu manquant.";
    header("Location: manage-places.php");
    exit();
} else {

    $idLieu = filter_input(INPUT_GET, 'idLieu', FILTER_SANITIZE_SPECIAL_CHARS);

    // Vérifiez si l'ID du lieu est un entier valide
    if (!$idLieu && $idLieu !== 0) {
        $_SESSION['error'] = "ID du lieu invalide.";
        header("Location: manage-places.php");
        exit();
    } else {

        try {
            // Récupérez l'ID du lieu à supprimer depuis la requête GET
            $idLieu = $_GET['idLieu'];
            // Préparez la requête SQL pour supprimer le lieu
            $sql = "DELETE FROM LIEU WHERE id_lieu = :idLieu";
            // Exécutez la requête SQL avec le paramètre
            $statement = $connexion->prepare($sql);
            $statement->bindParam(':idLieu', $idLieu, PDO::PARAM_INT);
            $statement->execute();
            // Redirigez vers la page précédente après la suppression
            header('Location: manage-places.php');
        } catch (PDOException $e) {
            echo 'Erreur : ' . $e->getMessage();
        }

    }
}

// Afficher les erreurs en PHP (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
