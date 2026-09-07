<?php
// Session starten, um Benutzerdaten zu überprüfen
session_start();

// Datenbankverbindung einbinden
require_once "includes/db.php";

// Zugriffsschutz: Prüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Maximale Ausführungszeit auf unendlich setzen, da der Upload und das Zippen großer Dateien dauern kann
set_time_limit(0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sleep(5);

    // 1. Grundlegende Formulardaten sicher abrufen
    $projectName = $_POST['project_name'] ?? 'Unbenanntes Projekt';
    $courseTitle = $_POST['course_titel'] ?? 'SCORM Modul';
    $scormVersion = $_POST['scorm_version'] ?? 'SCORM 1.2';
    $primaryColor = $_POST['primary_color'] ??  '#00386b';
    $layoutStyle = $_POST['layout_style'] ?? 'layout1';

    // Validierung der Sprachen
    if (!isset($_POST['languages']) || empty($_POST['languages'])) {
        die("Fehler: Bitte wählen Sie mindestens eine Sprache aus.");
    }
    $selectedLanguages = $_POST['languages'];

    /* SCHRITT 1: TEMPORÄRES ARBEITSVERZEICHNIS ERSTELLEN */

    // Projektnamen bereinigen (Sonderzeichen und Leerzeichen entfernen), um einen sicheren Ordnernamen zu generieren
    $cleanProjectName = preg_replace('/[^a-zA-Z0-9_]/', '_', $projectName);

    // Eine einzigartige ID anhängen, damit sich gleichnamige Projekte nicht überschreiben
    $tempDirName = $cleanProjectName . '_' . uniqid();
    $tempDirPath = __DIR__ . '/temp/' . $tempDirName;

    // Hauptordner für dieses Projekt erstellen
    if (!is_dir($tempDirPath)) {
        mkdir($tempDirPath, 0777, true);
    }

    /* SCHRITT 2: HOCHGELADENE DATEIEN IM WORKSPACE SPEICHERN */

    // 2.1 Kunden-Logo verarbeiten und im Temp-Ordner speichern
    $logoFilename = null;
    if (isset($_FILES['client_logo']) && $_FILES['client_logo']['error'] === UPLOAD_ERR_OK) {

        // Dateiendung ermitteln (bspw. .png oder .jpg)
        $logoExtension = strtolower(pathinfo($_FILES['client_logo']['name'], PATHINFO_EXTENSION));
        $logoFilename = 'logo.' . $logoExtension;

        // Datei vom temporären Server-Speicher in unseren Workspace verschieben
        move_uploaded_file($_FILES['client_logo']['tmp_name'], $tempDirPath . '/' . $logoFilename);
    }

    // 2.2 SCORM-ZIP-Dateien der ausgewählten Sprachen speichern
    $uploadedLanguages = [];  // Array zur Speicherung erfolgreich hochgeladener Sprach-Codes

    foreach ($selectedLanguages as $langCode) {
        $fileInputName = 'scorm_zip_' . $langCode;

        // Prüfen, ob für diese spezifische Sprache eine Datei übermittelt wurde
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {

            // Sicherheitsprüfung
            $fileExtension = strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));
            $allowedMimeTypes = ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'];

            // MIME-Type des temporären Files ermitteln
            $fileMimeType = mime_content_type($_FILES[$fileInputName]['tmp_name']);

            // Wenn es keine echte ZIP-Datei ist, Vorgang für diese Sprache abbrechen
            if ($fileExtension !== 'zip' || !in_array($fileMimeType, $allowedMimeTypes)) {
                die("Fehler: Ungültiges Dateiformat für Sprache " . strtoupper($langCode) . ". Es sind aus Sicherheitsgründen nur echte ZIP-Dateien erlaubt.");
            }

            // Datei im Workspace speichern (bspw. als "de.zip", "en.zip")
            $zipDestination = $tempDirPath . '/' . $langCode . '.zip';

            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $zipDestination)) {
                $uploadedLanguages[] = $langCode; // Als erfolgreich markieren
            }
        }
    }

    // Sicherheitsprüfung: Wurde überhaupt eine gültige ZIP-Datei hochgeladen?
    if (empty($uploadedLanguages)) {
        die("Fehler: Es wurden keine gültigen SCORM-ZIP-Dateien hochgeladen. Der Vorgang wurde abgebrochen.");
    }

    /* SCHRITT 3: ZIP-DATEIEN ENTPACKEN (UNZIP) */

    // PHP's native Zip-Klasse initialisieren
    $zip = new ZipArchive();

    foreach ($uploadedLanguages as $langCode) {
        $zipFilePath = $tempDirPath . '/' . $langCode . '.zip';

        // Zielordner für das Entpacken erstellen (bspw. temp/Projekt_123/de/)
        $extractPath = $tempDirPath . '/' . $langCode;

        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0777, true);
        }

        // ZIP öffnen und in den Unterordner entpacken
        if ($zip->open($zipFilePath) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();

            // Die ursprüngliche ZIP-Datei löschen, um den Server sauber zu halten
            unlink($zipFilePath);
        } else {

            // Falls eine Datei defekt ist, Prozess sofort abbrechen
            die("Fehler: Das SCORM-Paket für die Sprache '" . strtoupper($langCode) . "' konnte nicht entpact werden.");
        }
    }

    /* SCHRITT 4: LANDING-PAGE (INDEX.HTML) GENERIEREN */

    $indexPath = $tempDirPath . '/index.html';

    // Prüfen, welches Layout gewählt wurde
    $containerClass = ($layoutStyle === 'layout2') ? 'container split-layout' : 'container';

    $htmlContent = '<!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($courseTitle) . '</title>
        <style>
            :root {
                --primary: ' . htmlspecialchars($primaryColor) . ';
                --bg-tint: color-mix(in srgb, var(--primary) 6%, #f4f7f6);
                --shadow-tint: color-mix(in srgb, var(--primary) 20%, transparent);
            }
            
            body { 
                font-family: "Segoe UI", Arial, sans-serif; 
                background-color: var(--bg-tint);
                display: flex; 
                justify-content: center; 
                align-items: center; 
                min-height: 100vh; 
                margin: 0; 
                padding: 20px; 
                box-sizing: border-box;
            }
            
            .container { 
                background: white; 
                padding: 50px; 
                border-radius: 16px; 
                box-shadow: 0 15px 40px var(--shadow-tint); 
                max-width: 650px; 
                width: 100%; 
                text-align: center;
            }
            
            /* LAYOUT 2: SPLIT-SCREEN */
            .container.split-layout {
                display: flex; 
                flex-direction: row; 
                align-items: center;
                max-width: 850px; 
                padding: 40px; 
                text-align: left; 
                gap: 40px;
            }
            .split-layout .logo-wrapper { 
                flex: 0 0 35%; 
                display: flex; 
                justify-content: center;
            }
            .split-layout .content-wrapper { 
                flex: 0 0 calc(65% - 40px);
            }
            .split-layout .btn-group { 
                justify-content: flex-start; 
            }
            
            /* Titels */
            h1 { 
                color: var(--primary);
                margin-top: 0; 
                margin-bottom: 8px; 
                font-size: 32px;
                font-weight: 800;
                letter-spacing: -0.5px;
            }
            .subtitle { 
                color: #666666; 
                margin-bottom: 35px; 
                font-size: 16px;
                font-style: italic;
                font-weight: 500;
                letter-spacing: -0.5px;
            }
            
            .btn-group { 
                display: flex; 
                justify-content: center; 
                gap: 20px; 
                flex-wrap: wrap; 
            }
            
            .btn { 
                position: relative;
                display: flex;
                justify-content: center;
                align-items: center;
                width: 130px;      
                height: 90px;      
                text-decoration: none; 
                color: white; 
                border-radius: 12px; 
                font-weight: 800; 
                font-size: 32px;   
                text-shadow: 0 2px 5px rgba(0,0,0,0.8); 
                transition: transform 0.2s, box-shadow 0.2s; 
                overflow: hidden;
                box-shadow: 0 5px 15px var(--shadow-tint);
            }
            
            .btn:hover { 
                transform: translateY(-4px) scale(1.03); 
                box-shadow: 0 12px 25px var(--shadow-tint);
            }
            
            .logo-img { 
                max-width: 100%; 
                max-height: 120px; 
                border-radius: 8px; 
                object-fit: contain; 
            }
            
            @media (max-width: 650px) {
                .container.split-layout { 
                    flex-direction: column; 
                    text-align: center; 
                    gap: 20px; 
                }
                .split-layout .btn-group {
                    justify-content: center; 
                }
                .split-layout .logo-wrapper,
                .split-layout .content-wrapper {
                    flex: 1 1 100%; 
                }
            }
        </style>
    </head>
    <body>
    <div class="' . $containerClass . '">';

    if ($layoutStyle === 'layout2') {
        $htmlContent .= '<div class="logo-wrapper">';
        if ($logoFilename) {
            $htmlContent .= '<img src="' . htmlspecialchars($logoFilename) . '" alt="Kunden Logo" class="logo-img">';
        }
        $htmlContent .= '</div><div class="content-wrapper">';
    } else {
        if ($logoFilename) {
            $htmlContent .= '<img src="' . htmlspecialchars($logoFilename) . '" alt="Kunden Logo" class="logo-img" style="margin-bottom: 25px;">';
        }
    }

    $htmlContent .= '<h1>' . htmlspecialchars($courseTitle) . '</h1>
        <p class="subtitle">Bitte wählen Sie Ihre Sprache / Please select your language:</p>
        <div class="btn-group">';

    // Schleife, die Buttons mit Flaggen und Farbverlauf erstellt
    foreach ($uploadedLanguages as $langCode) {
        $langName = strtoupper($langCode);

        $flagCode = ($langCode === 'en') ? 'gb' : $langCode;
        $flagUrl = 'https://flagcdn.com/w160/' . $flagCode . '.png';

        $bgStyle = 'background-image: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0.8)), url(\'' . $flagUrl . '\'); background-size: cover; background-position: center;';

        $htmlContent .= '<a href="' . $langCode . '/index.html" class="btn" style="' . $bgStyle . '">' . $langName . '</a>';
    }

    $htmlContent .= '</div>';

    if ($layoutStyle === 'layout2') {
        $htmlContent .= '</div>';
    }
    $htmlContent .= '</div></body></html>';

    file_put_contents($indexPath, $htmlContent);

    /* SCHRITT 5: MASTER SCORM-MANIFEST (imsmanifest.xml) GENERIEREN */

    $manifestPath = $tempDirPath . '/imsmanifest.xml';

    // Einzigartige IDs für das SCORM-Paket generieren (Vermeidet Caching-Probleme im LMS)
    $manifestId = 'com.sprachcomposer.pkg_' . time();
    $orgId = 'org_' . time();
    $itemId = 'item_' . time();
    $resourceId = 'res_' . time();

    // Wir bauen die XML-Struktur abhängig von der gewählten SCORM-Version (Aus dem Formular)
    if ($scormVersion === '2004') {
        $schema = 'ADL SCORM';
        $schemaVersion = '2004 3rd Edition';
        $manifestTag = '<manifest identifier="' . $manifestId . '" version="1.0" xmlns="http://www.imsglobal.org/xsd/imscp_v1p1" xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_v1p3" xmlns:adlseq="http://www.adlnet.org/xsd/adlseq_v1p3" xmlns:adlnav="http://www.adlnet.org/xsd/adlnav_v1p3" xmlns:imsss="http://www.imsglobal.org/xsd/imsss">';
    } else {
        // Standard-Fallback: SCORM 1.2
        $schema = 'ADL SCORM';
        $schemaVersion = '1.2';
        $manifestTag = '<manifest identifier="' . $manifestId . '" version="1.0" xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2" xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2">';
    }

    // Das Master-XML zusammensetzen
    $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
    ' . $manifestTag . '
    <metadata>
        <schema>' . $schema . '</schema>
        <schemaversion>' . $schemaVersion . '</schemaversion>
    </metadata>
    <organizations default="' . $orgId . '">
        <organization identifier="' . $orgId . '">
        <title>' . htmlspecialchars($courseTitle) . '</title>
        <item identifier="' . $itemId . '" identifierref="' . $resourceId . '">
            <title>' . htmlspecialchars($courseTitle) . '</title>
        </item>
        </organization>
    </organizations>

    <resources>
        <!-- Der wichtigste Teil: Hier definieren wir unsere Landing-Page als Startdatei (href="index.html") -->
        <resource identifier="' . $resourceId . '" type="webcontent" adlcp:scormtype="sco" href="index.html">
        <file href="index.html"/>';

    // Das Logo in die Ressourcenliste aufnehmen, falls vorhanden
    if ($logoFilename) {
        $xmlContent .= '<file href="' . htmlspecialchars($logoFilename) . '"/>';
    }
    $xmlContent .= '
        </resource>
    </resources>
    </manifest>';

    // XML-Datei physisch im Arbeitsverzeichnis speichern
    file_put_contents($manifestPath, $xmlContent);

    /* SCHRITT 6: ALLES IN EIN MASTER-ZIP PACKEN & DOWNLOADEN */

    // Name der finalen ZIP-Datei
    $finalZipName = 'SCORM_' . $cleanProjectName . '.zip';
    $finalZipPath = __DIR__ . '/temp/' . $finalZipName;

    $masterZip = new ZipArchive();

    if ($masterZip->open($finalZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

        // Den gesamten Workspace-Ordner (tempDirPath) rekursiv durchsuchen
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempDirPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Nur echte Dateien hinzufügen, keine Ordner-Strukturen selbst
            if (!$file->isDir()) {
                // Den exakten Pfad der Datei holen
                $filePath = $file->getRealPath();

                // Den relativen Pfad für das ZIP berechnen (damit die Ordnerstruktur im ZIP stimmt)
                $relativePath = substr($filePath, strlen($tempDirPath) + 1);

                // Datei ins ZIP einfügen
                $masterZip->addFile($filePath, $relativePath);
            }
        }
        $masterZip->close();
    } else {
        die("Kritischer Fehler: Die finale ZIP-Datei konnte nicht erstellt werden.");
    }

    /* SCHRITT 7: PROJEKT IN DER DATENBANK SPEICHERN */

    // Hier speichern ODER aktualisieren wir das Projekt in deiner Datenbank
    try {
        // Wenn eine project_id übergeben wurde, handelt es sich um ein UPDATE (Bearbeiten)
        if (isset($_POST['project_id']) && !empty($_POST['project_id'])) {

            $stmt = $pdo->prepare("UPDATE projects SET project_name = :pname, course_title = :ctitle, scorm_version = :sversion, primary_color = :pcolor, layout_style = :lstyle WHERE id = :id AND user_id = :uid");
            $stmt->execute([
                'pname' => $projectName,
                'ctitle' => $courseTitle,
                'sversion' => $scormVersion,
                'pcolor' => $primaryColor,
                'lstyle' => $layoutStyle,
                'id' => $_POST['project_id'],
                'uid' => $_SESSION['user_id']
            ]);
        } else {
            // Ansonsten ist es ein NEUES Projekt (INSERT)
            $stmt = $pdo->prepare("INSERT INTO projects (user_id, project_name, course_title, scorm_version, primary_color, layout_style) VALUES (:uid, :pname, :ctitle, :sversion, :pcolor, :lstyle)");
            $stmt->execute([
                'uid' => $_SESSION['user_id'],
                'pname' => $projectName,
                'ctitle' => $courseTitle,
                'sversion' => $scormVersion,
                'pcolor' => $primaryColor,
                'lstyle' => $layoutStyle
            ]);
        }
    } catch (PDOException $e) {
        error_log("DB Fehler beim Speichern/Aktualisieren des Projekts: " . $e->getMessage());
    }

    /* SCHRITT 8: ZUM DASHBOARD WEITERLEITEN (MIT DOWNLOAD-TRIGGER) */

    if (file_exists($finalZipPath)) {
        header("Location: index.php?success=1&download=" . urlencode($finalZipName));
        exit;
    } else {
        die("Fehler: Die ZIP-Datei wurde nicht gefunden.");
    }
} else {
    header("Location: create_project.php");
    exit;
}
