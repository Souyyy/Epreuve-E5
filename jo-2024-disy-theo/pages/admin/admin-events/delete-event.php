<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

$idEvent = filter_input(INPUT_GET, 'idEvent', FILTER_SANITIZE_SPECIAL_CHARS);

try {
    // Vérifiez si l'epreuve existe
    $queryCheckEvent = "SELECT id_epreuve FROM EPREUVE WHERE id_epreuve = :idEvent";
    $statementCheckEvent = $connexion->prepare($queryCheckEvent);
    $statementCheckEvent->bindParam(":idEvent", $idEvent, PDO::PARAM_STR);
    $statementCheckEvent->execute();

    // Récupérez les données de l'epreuve
    $eventData = $statementCheckEvent->fetch(PDO::FETCH_ASSOC);

    // Vérifiez si l'epreuve existe
    if (!$eventData) {
        // L'epreuve n'existe pas redirection vers une autre page
        $_SESSION['error'] = "L'epreuve n'existe pas.";
        header("Location: manage-events.php");
        exit();
    }

    // Supprimez l'epreuve
    $queryDeleteEvent = "DELETE FROM EPREUVE WHERE id_epreuve = :idEvent";
    $statementDeleteEvent = $connexion->prepare($queryDeleteEvent);
    $statementDeleteEvent->bindParam(":idEvent", $idEvent, PDO::PARAM_STR);
    $deleteSuccess = $statementDeleteEvent->execute();

    // Gérez la réponse de la requete de suppression
    if ($deleteSuccess) {
        $_SESSION['success'] = "L'epreuve a été supprimé avec succès.";
        header("Location: manage-events.php");
        exit();
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression de l'epreuve.";
        header("Location: manage-events.php");
        exit();
    }

} catch (PDOException $e) {
    // Gérez les erreurs de base de données
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-events.php");
    exit();
}
?>