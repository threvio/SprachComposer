<?php
// Session starten, um Benutzerdaten zu überprüfen
session_start();

// Datenbankverbindung einbinden
require_once "includes/db.php";

// Prüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Hilfsfunktion
function getScormStartFile($manifestPath)
{
    if (!file_exists($manifestPath)) {
        return 'index.html';
    }
    $xml = @simplexml_load_file($manifestPath);
    if ($xml && isset($xml->resources->resource[0]['href'])) {
        return (string)$xml->resources->resource[0]['href'];
    }
    return 'index.html';
}

// Funktion zur automatischen Erkennung des E-Learning-Standards
function detectCourseStandard($extractPath)
{

    // A. xAPI Prüfung: Existiert eine tincan.xml?
    if (file_exists($extractPath . '/tincan.xml')) {
        return 'xAPI';
    }

    // B. SCORM Prüfung: Existiert ein imsmanifest.xml?
    $manifestPath = $extractPath . '/imsmanifest.xml';
    if (file_exists($manifestPath)) {
        $xml = @simplexml_load_file($manifestPath);
        if ($xml && isset($xml->metadata->schemaversion)) {
            $version = (string)$xml->metadata->schemaversion;
            if (strpos($version, '2004') !== false || strpos($version, '1.3') !== false) {
                return 'SCORM 2004';
            }
        }
        return 'SCORM 1.2';
    }
    return 'Unknown';
}

// Maximale Ausführungszeit auf unendlich setzen
set_time_limit(0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Grundlegende Formulardaten sicher abrufen
    $projectName  = $_POST['project_name'] ?? 'Unbenanntes Projekt';
    $courseTitle  = $_POST['course_titel'] ?? 'E-Learning Modul';
    $primaryColor = $_POST['primary_color'] ?? '#00386b';
    $layoutStyle  = $_POST['layout_style'] ?? 'layout1';
    $action       = $_POST['action'] ?? 'save_only';

    // Da wir das Feld aus dem HTML entfernt haben, setzen wir einen Platzhalter
    $scormVersion = 'Wird ermittelt...';

    /* SCHRITT 1: PROJEKT IMMER IN DER DATENBANK SPEICHERN */
    try {
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
        // Skript sofort abbrechen, falls das Projekt nicht in der Datenbank gespeichert werden konnte
        die("Kritischer Datenbankfehler: " . $e->getMessage());
    }

    // Projekt-ID für spätere automatische Updates speichern (prüfen, ob leer)
    $currentProjectId = !empty($_POST['project_id']) ? $_POST['project_id'] : $pdo->lastInsertId();

    /* SCHRITT 2: PRÜFEN, OB NUR GESPEICHERT ODER GENERIERT WERDEN SOLL */
    if ($action === 'save_only') {
        // Spezifisches Signal 'saved' an das Dashboard übergeben, um die korrekte Meldung anzuzeigen
        header("Location: index.php?success=saved");
        exit;
    }

    // Wenn "generate" geklickt wurde, geht der Code hier weiter
    if ($action === 'generate') {

        // Validierung der Sprachen
        if (!isset($_POST['languages']) || empty($_POST['languages'])) {
            die("Fehler: Bitte wählen Sie mindestens eine Sprache aus.");
        }
        $selectedLanguages = $_POST['languages'];

        /* SCHRITT 3: TEMPORÄRES ARBEITSVERZEICHNIS ERSTELLEN */
        $cleanProjectName = preg_replace('/[^a-zA-Z0-9_]/', '_', $projectName);
        $tempDirName = $cleanProjectName . '_' . uniqid();
        $tempDirPath = __DIR__ . '/temp/' . $tempDirName;

        if (!is_dir($tempDirPath)) {
            mkdir($tempDirPath, 0755, true);
        }

        /* SCHRITT 4: HOCHGELADENE DATEIEN IM WORKSPACE SPEICHERN */
        $logoFilename = null;
        if (isset($_FILES['client_logo']) && $_FILES['client_logo']['error'] === UPLOAD_ERR_OK) {
            $logoExtension = strtolower(pathinfo($_FILES['client_logo']['name'], PATHINFO_EXTENSION));
            $logoFilename = 'logo.' . $logoExtension;
            move_uploaded_file($_FILES['client_logo']['tmp_name'], $tempDirPath . '/' . $logoFilename);
        }

        $uploadedLanguages = [];
        foreach ($selectedLanguages as $langCode) {
            $fileInputName = 'scorm_zip_' . $langCode;
            if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
                $fileExtension = strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));
                $allowedMimeTypes = ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip'];
                $fileMimeType = mime_content_type($_FILES[$fileInputName]['tmp_name']);

                if ($fileExtension !== 'zip' || !in_array($fileMimeType, $allowedMimeTypes)) {
                    die("Fehler: Ungültiges Dateiformat für Sprache " . strtoupper($langCode) . ". Es sind nur ZIP-Dateien erlaubt.");
                }

                $zipDestination = $tempDirPath . '/' . $langCode . '.zip';
                if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $zipDestination)) {
                    $uploadedLanguages[] = $langCode;
                }
            }
        }

        if (empty($uploadedLanguages)) {
            die("Fehler: Es wurden keine gültigen SCORM-ZIP-Dateien hochgeladen.");
        }

        /* SCHRITT 5: ZIP-DATEIEN ENTPACKEN (UNZIP) */
        $zip = new ZipArchive();
        foreach ($uploadedLanguages as $langCode) {
            $zipFilePath = $tempDirPath . '/' . $langCode . '.zip';
            $extractPath = $tempDirPath . '/' . $langCode;

            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            if ($zip->open($zipFilePath) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();
                unlink($zipFilePath);
            } else {
                die("Fehler: SCORM-Paket für '" . strtoupper($langCode) . "' konnte nicht entpackt werden.");
            }
        }

        /* SCHRITT 6: LANDING-PAGE (INDEX.HTML) GENERIEREN MIT SMARTY */
        // Smarty-Klasse einbinden
        require_once __DIR__ . '/libs/Smarty.class.php';

        // Smarty instanziieren und Verzeichnisse konfigurieren
        $smarty = new \Smarty\Smarty();
        $smarty->setTemplateDir(__DIR__ . '/templates/');
        $smarty->setCompileDir(__DIR__ . '/templates_c/');
        $smarty->setCacheDir(__DIR__ . '/cache/');
        $smarty->setConfigDir(__DIR__ . '/configs/');

        // Layout-Klasse für den Container bestimmen
        $containerClass = ($layoutStyle === 'layout2') ? 'container split-layout' : 'container';

        // Array für die Sprach-Buttons vorbereiten
        $languageLinks = [];
        foreach ($uploadedLanguages as $langCode) {
            $langName = strtoupper($langCode);
            $flagCode = ($langCode === 'en') ? 'gb' : $langCode;
            $flagUrl = 'https://flagcdn.com/w160/' . $flagCode . '.png';
            $bgStyle = 'background-image: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0.8)), url(\'' . $flagUrl . '\'); background-size: cover; background-position: center;';

            $extractedManifestPath = $tempDirPath . '/' . $langCode . '/imsmanifest.xml';
            $startFile = getScormStartFile($extractedManifestPath);

            // Button-Daten in das Array pushen
            $languageLinks[] = [
                'url' => $langCode . '/' . $startFile,
                'name' => $langName,
                'bgStyle' => $bgStyle
            ];
        }

        // Variablen an die Smarty-Template-Engine übergeben
        $smarty->assign('courseTitle', $courseTitle);
        $smarty->assign('primaryColor', $primaryColor);
        $smarty->assign('layoutStyle', $layoutStyle);
        $smarty->assign('containerClass', $containerClass);
        $smarty->assign('logoFilename', $logoFilename);
        $smarty->assign('languageLinks', $languageLinks);

        // HTML-Inhalt aus dem Template generieren lassen
        $htmlContent = $smarty->fetch('landingpage.tpl');

        // Generierte HTML-Datei physisch im Arbeitsverzeichnis speichern
        $indexPath = $tempDirPath . '/index.html';
        file_put_contents($indexPath, $htmlContent);

        /* SCHRITT 7: STANDARD ERKENNEN & MASTER-XML (SCORM ODER XAPI) GENERIEREN */
        // Den Standard anhand des ersten hochgeladenen Sprachpakets erkennen
        $firstLangPath = $tempDirPath . '/' . $uploadedLanguages[0];
        $detectedStandard = detectCourseStandard($firstLangPath);

        if ($detectedStandard === 'Unknown') {
            die("Fehler: Das hochgeladene ZIP-Paket ist weder ein gültiges SCORM- noch ein xAPI-Paket.");
        }

        // Datenbank mit dem automatisch erkannten Standard aktualisieren
        $stmtUpdate = $pdo->prepare("UPDATE projects SET scorm_version = :sversion WHERE id = :id");
        $stmtUpdate->execute(['sversion' => $detectedStandard, 'id' => $currentProjectId]);

        // Alle Dateien im Workspace scannen, um sie korrekt im Manifest aufzulisten (Behebt den Fehler fehlender Dateien)
        $resourceFilesXml = '';
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempDirPath));

        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($tempDirPath) + 1);
                // Backslashes für Windows-Systeme in Slashes umwandeln
                $relativePath = str_replace('\\', '/', $relativePath);

                // Wir ignorieren temporäre ZIPs und die Master-XML-Dateien selbst
                if (pathinfo($relativePath, PATHINFO_EXTENSION) !== 'zip' && $relativePath !== 'imsmanifest.xml' && $relativePath !== 'tincan.xml') {
                    $resourceFilesXml .= '<file href="' . htmlspecialchars($relativePath) . '"/>' . "\n            ";
                }
            }
        }

        // FORK: Entweder xAPI oder SCORM generieren
        if ($detectedStandard === 'xAPI') {

            // --- GENERIERUNG: xAPI (tincan.xml) ---
            $tincanPath = $tempDirPath . '/tincan.xml';
            $activityId = 'http://sprachcomposer.com/course/' . time();

            $xmlContent = '<?xml version="1.0" encoding="utf-8" ?>
            <tincan xmlns="http://projecttincan.com/tincan.xsd">
                <activities>
                    <activity id="' . $activityId . '" type="http://adlnet.gov/expapi/activities/course">
                        <name>' . htmlspecialchars($courseTitle) . '</name>
                        <description lang="de-DE">Generiertes xAPI-Projekt</description>
                        <launch>index.html</launch>
                    </activity>
                </activities>
            </tincan>';

            file_put_contents($tincanPath, $xmlContent);
        } else {

            // --- GENERIERUNG: SCORM 1.2 oder 2004 (imsmanifest.xml) ---
            $manifestPath = $tempDirPath . '/imsmanifest.xml';
            $manifestId = 'com.sprachcomposer.pkg_' . time();
            $orgId = 'org_' . time();
            $itemId = 'item_' . time();
            $resourceId = 'res_' . time();

            if ($detectedStandard === 'SCORM 2004') {
                $schema = 'ADL SCORM';
                $schemaVersion = '2004 3rd Edition';
                $manifestTag = '<manifest identifier="' . $manifestId . '" version="1.0" xmlns="http://www.imsglobal.org/xsd/imscp_v1p1" xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_v1p3" xmlns:adlseq="http://www.adlnet.org/xsd/adlseq_v1p3" xmlns:adlnav="http://www.adlnet.org/xsd/adlnav_v1p3" xmlns:imsss="http://www.imsglobal.org/xsd/imsss">';
            } else {
                $schema = 'ADL SCORM';
                $schemaVersion = '1.2';
                $manifestTag = '<manifest identifier="' . $manifestId . '" version="1.0" xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2" xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2">';
            }

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
                <resource identifier="' . $resourceId . '" type="webcontent" adlcp:scormtype="sco" href="index.html">
                ' . $resourceFilesXml . '
                </resource>
            </resources>
            </manifest>';

            file_put_contents($manifestPath, $xmlContent);

            // XSD-Definitionsdateien automatisch in das SCORM-Paket kopieren
            $xsdSourceDir = __DIR__ . '/xsd_templates/';
            if (is_dir($xsdSourceDir)) {
                // Alle .xsd Dateien aus dem Vorlagenordner suchen
                $xsdFiles = glob($xsdSourceDir . '*.xsd');
                foreach ($xsdFiles as $xsdFile) {
                    $fileName = basename($xsdFile);
                    // Datei in das temporäre Arbeitsverzeichnis kopieren
                    copy($xsdFile, $tempDirPath . '/' . $fileName);
                }
            }
        }

        /* SCHRITT 8: ALLES IN EIN MASTER-ZIP PACKEN & DOWNLOADEN */
        $finalZipName = 'SCORM_' . $cleanProjectName . '.zip';
        $finalZipPath = __DIR__ . '/temp/' . $finalZipName;

        $masterZip = new ZipArchive();
        if ($masterZip->open($finalZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tempDirPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($tempDirPath) + 1);
                    $masterZip->addFile($filePath, $relativePath);
                }
            }
            $masterZip->close();

            // GARBAGE COLLECTION
            $dir = new RecursiveDirectoryIterator($tempDirPath, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
            }
            rmdir($tempDirPath);
        } else {
            die("Kritischer Fehler: Die finale ZIP-Datei konnte nicht erstellt werden.");
        }

        /* SCHRITT 9: ZUM DASHBOARD WEITERLEITEN (MIT DOWNLOAD-TRIGGER) */
        if (file_exists($finalZipPath)) {
            header("Location: index.php?success=1&download=" . urlencode($finalZipName));
            exit;
        } else {
            die("Fehler: Die ZIP-Datei wurde nicht gefunden.");
        }
    }
} else {
    header("Location: create_project.php");
    exit;
}
