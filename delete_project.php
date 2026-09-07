<?php
session_start();
require_once "includes/db.php";

// Zugriffsschutz
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $projectId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    // 1. Projektnamen holen, um die ZIP-Datei zu finden
    $stmt = $pdo->prepare("SELECT project_name FROM projects WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        "id" => $projectId,
        "user_id" => $userId
    ]);
    $project = $stmt->fetch();

    // 2. Physische ZIP-Datei vom Server löschen (falls vorhanden)
    if ($project) {
        $cleanProjectName = preg_replace('/[^a-zA-Z0-9_]/', '_', $project['project_name']);
        $zipFilePath = __DIR__ . '/temp/SCORM_' . $cleanProjectName . '.zip';

        if (file_exists($zipFilePath)) {
            unlink($zipFilePath);
        }

        // 3. Datensatz aus der Datenbank löschen
        $deleteStmt = $pdo->prepare("DELETE FROM projects WHERE id = :id AND user_id = :user_id");
        $deleteStmt->execute([
            "id" => $projectId,
            "user_id" => $userId
        ]);
    }
}

// Zurück zum Dashboard mit Erfolgsmeldung
header("Location: index.php?deleted=1");
exit;