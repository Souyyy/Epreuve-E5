<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'utilisateur est fourni dans l'URL
if (!isset($_GET['idUser'])) {
    $_SESSION['error'] = "ID de l'utilisateur manquant.";
    header("Location: manage-users.php");
    exit();
} else {
    $idUser = filter_input(INPUT_GET, 'idUser', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$idUser && $idUser !== 0) {
        $_SESSION['error'] = "ID de l'utilisateur est invalide.";
        header("Location: manage-users.php");
        exit();
    } else {

        try {
            // Vérifiez si l'utilisateur existe
            $queryCheckUser = "SELECT id_utilisateur FROM UTILISATEUR WHERE id_utilisateur = :idUser";
            $statementCheckUser = $connexion->prepare($queryCheckUser);
            $statementCheckUser->bindParam(":idUser", $idUser, PDO::PARAM_STR);
            $statementCheckUser->execute();

            // Récupérez les données de l'utilisateur
            $userData = $statementCheckUser->fetch(PDO::FETCH_ASSOC);

            // Vérifiez si l'utilisateur existe
            if (!$userData) {
                // L'utilisateur n'existe pas redirection vers une autre page
                $_SESSION['error'] = "L'utilisateur n'existe pas.";
                header("Location: manage-users.php");
                exit();
            }

            // Supprimez l'utilisateur
            $queryDeleteUser = "DELETE FROM UTILISATEUR WHERE id_utilisateur = :idUser";
            $statementDeleteUser = $connexion->prepare($queryDeleteUser);
            $statementDeleteUser->bindParam(":idUser", $idUser, PDO::PARAM_STR);
            $deleteSuccess = $statementDeleteUser->execute();

            // Gérez la réponse de la requete de suppression
            if ($deleteSuccess) {
                $_SESSION['success'] = "L'utilisateur a été supprimé avec succès.";
                header("Location: manage-users.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de l'utilisateur.";
                header("Location: manage-users.php");
                exit();
            }
            
        } catch (PDOException $e) {
            // Gérez les erreurs de base de données
            $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
            header("Location: manage-users.php");
            exit();
        }
    }
}
