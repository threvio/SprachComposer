<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "includes/db.php";

// Standardwerte (für neues Projekt)
$editMode = false;
$editData = [
    "project_name" => '',
    "course_title" => '',
    "scorm_version" => '',
    "primary_color" => '#00386b',
    "layout_style" => 'layout1'
];
$projectId = '';

// Wenn eine ID übergeben wurde, Daten aus der DB holen (Edit-Modus)
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id AND user_id = :uid");
    $stmt->execute(['id' => $_GET['edit_id'], 'uid' => $_SESSION['user_id']]);
    $project = $stmt->fetch();

    if ($project) {
        $editMode = true;
        $editData = $project;
        $projectId = $project['id'];
    }
}

require_once "includes/header.php";
?>

<main class="container dashboard-wrapper">
    <div class="main-card">

        <div class="dashboard-header" style="border-bottom: none; padding-bottom: 0;">
            <div class="dashboard-title-group">
                <h2>Neues Projekt anlegen</h2>
            </div>

            <a href="index.php" class="btn-close" title="Abbrechen"> <i class="fas fa-times"></i></a>
        </div>

        <!-- enctype="multipart/form-data" ist zwingend erforderlich für Datei-Uploads! -->
        <form action="process_project.php" method="POST" enctype="multipart/form-data" id="project-form">
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($projectId); ?>">

            <!-- BLOCK 1: Allgemeine Einstellungen -->
            <div class="form-card-section">
                <h3>1. Allgemeine Einstellungen</h3>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="project_name">Interner Projektname</label>
                            <input type="text" id="project_name" name="project_name" value="<?php echo htmlspecialchars($editData['project_name'] ?? ''); ?>" placeholder="z.B. Hygiene 2026" required>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="course_titel">Titel des Kurses</label>
                            <input type="text" id="course_titel" name="course_titel" value="<?php echo htmlspecialchars($editData['course_title'] ?? ''); ?>" placeholder="Titel, den der Nutzer im LMS sieht" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOCK 2: Design der Sprachauswahl-Seite -->
            <div class="form-card-section">
                <h3>2. Design der Sprachauswahl-Seite</h3>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="primary_color">Primärfarbe (Hex)</label>
                            <input type="color" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($editData['primary_color']); ?>" class="color-picker">
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label>Layout-Variante</label>
                            <div class="radio-group">

                                <label class="radio-label">
                                    <input type="radio" name="layout_style" value="layout1" <?php if ($editData['layout_style'] === 'layout1') echo 'checked'; ?>> Layout 1 (Zentriert)
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="layout_style" value="layout2" <?php if ($editData['layout_style'] === 'layout2') echo 'checked'; ?>> Layout 2 (Split-Screen)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label for="client_logo">Kunden-Logo hochladen</label>
                            <input type="file" id="client_logo" name="client_logo" accept="image/png, image/jpeg" class="file-input">
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOCK 3: Sprachen und SCORM-Pakete -->
            <div class="form-card-section">
                <h3>3. Sprachen und SCORM-Pakete</h3>
                <p class="section-desc">Wählen Sie die benötigten Sprachen und laden Sie pro Sprache die entsprechende ZIP-Datei hoch.</p>

                <div class="language-grid">

                    <!-- DE -->
                    <div class="lang-upload-row">
                        <div class="lang-upload-left">
                            <label class="checkbox-label">
                                <input type="checkbox" name="languages[]" value="de"> <strong>DE</strong> Deutsch
                            </label>
                        </div>
                        <div class="lang-upload-right">
                            <!-- Skriveni pravi input -->
                            <input type="file" name="scorm_zip_de" id="zip_de" accept=".zip" class="hidden-file-input">
                            <!-- Naše lažno dugme koje okida skriveni input -->
                            <label for="zip_de" class="custom-file-btn">Datei auswählen</label>
                            <!-- Naš tekst koji ćemo menjati preko JS-a -->
                            <span class="custom-file-text" id="text_zip_de">Keine ausgewählt</span>
                        </div>
                    </div>

                    <!-- EN -->
                    <div class="lang-upload-row">
                        <div class="lang-upload-left">
                            <label class="checkbox-label">
                                <input type="checkbox" name="languages[]" value="en"> <strong>EN</strong> Englisch
                            </label>
                        </div>
                        <div class="lang-upload-right">
                            <input type="file" name="scorm_zip_en" id="zip_en" accept=".zip" class="hidden-file-input">
                            <label for="zip_en" class="custom-file-btn">Datei auswählen</label>
                            <span class="custom-file-text" id="text_zip_en">Keine ausgewählt</span>
                        </div>
                    </div>

                    <!-- ES -->
                    <div class="lang-upload-row">
                        <div class="lang-upload-left">
                            <label class="checkbox-label">
                                <input type="checkbox" name="languages[]" value="es"> <strong>ES</strong> Spanisch
                            </label>
                        </div>
                        <div class="lang-upload-right">
                            <input type="file" name="scorm_zip_es" id="zip_es" accept=".zip" class="hidden-file-input">
                            <label for="zip_es" class="custom-file-btn">Datei auswählen</label>
                            <span class="custom-file-text" id="text_zip_es">Keine ausgewählt</span>
                        </div>
                    </div>

                    <!-- FR -->
                    <div class="lang-upload-row">
                        <div class="lang-upload-left">
                            <label class="checkbox-label">
                                <input type="checkbox" name="languages[]" value="fr"> <strong>FR</strong> Französisch
                            </label>
                        </div>
                        <div class="lang-upload-right">
                            <input type="file" name="scorm_zip_fr" id="zip_fr" accept=".zip" class="hidden-file-input">
                            <label for="zip_fr" class="custom-file-btn">Datei auswählen</label>
                            <span class="custom-file-text" id="text_zip_fr">Keine ausgewählt</span>
                        </div>
                    </div>

                    <!-- IT -->
                    <div class="lang-upload-row">
                        <div class="lang-upload-left">
                            <label class="checkbox-label">
                                <input type="checkbox" name="languages[]" value="it"> <strong>IT</strong> Italienisch
                            </label>
                        </div>
                        <div class="lang-upload-right">
                            <input type="file" name="scorm_zip_it" id="zip_it" accept=".zip" class="hidden-file-input">
                            <label for="zip_it" class="custom-file-btn">Datei auswählen</label>
                            <span class="custom-file-text" id="text_zip_it">Keine ausgewählt</span>
                        </div>
                    </div>

                    <!-- NL -->
                    <div class="lang-upload-row">
                        <div class="lang-upload-left">
                            <label class="checkbox-label">
                                <input type="checkbox" name="languages[]" value="nl"> <strong>NL</strong> Niederländisch
                            </label>
                        </div>
                        <div class="lang-upload-right">
                            <input type="file" name="scorm_zip_nl" id="zip_nl" accept=".zip" class="hidden-file-input">
                            <label for="zip_nl" class="custom-file-btn">Datei auswählen</label>
                            <span class="custom-file-text" id="text_zip_nl">Keine ausgewählt</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-container" id="upload-progress" style="display: none;">
                <div class="progress-header">
                    <span class="progress-status"><i class="fas fa-spinner fa-spin"></i> Verarbeitung läuft... Bitte warten.</span>
                    <span class="progress-percent" id="progress-percent-text">0%</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" id="progress-bar-fill"></div>
                </div>
            </div>

            <div class="form-actions text-right">
                <button type="submit" name="action" value="save_only" class="btn btn-secondary">Speichern</button>

                <button type="submit" name="action" value="generate" class="btn btn-primary">Projekt generieren</button>
            </div>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>