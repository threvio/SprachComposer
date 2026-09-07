<?php
//Session start
session_start();

// Zugriffsschutz: Prüfen, ob der User eingeloggt ist
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Datenbankverbindung einbinden
require_once "includes/db.php";

// Projekte des eingeloggten Benutzers aus der Datenbank abrufen
$stmt = $pdo->prepare("SELECT id, project_name, scorm_version, created_at FROM projects WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(["user_id" => $_SESSION["user_id"]]);
$projekte = $stmt->fetchAll();

// Prüfen, ob ein Download ansteht (aus der URL auslesen)
$downloadFile = null;
if (isset($_GET['download']) && !empty($_GET['download'])) {
    $downloadFile = htmlspecialchars($_GET['download']);
}

// Header einbinden
require_once "includes/header.php";
?>

<!-- Hauptinhalt der Dashboard-Seite -->
<main class="container dashboard-wrapper">
    <div class="main-card">

        <?php
        // Bestimmen, welche Nachricht angezeigt werden soll
        $statusMsg = "";
        if ($downloadFile) {
            $statusMsg = "<strong>Erfolg!</strong> Ihr Projekt wurde erstellt und der Download startet automatisch.";
        } elseif (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
            $statusMsg = "<strong>Gelöscht!</strong> Das Projekt wurde erfolgreich entfernt.";
        }
        ?>

        <!-- Gemeinsamer Container für alle Meldungen -->
        <?php if (!empty($statusMsg)): ?>
            <div class="erfolg-msg" id="status-message">
                <?php echo $statusMsg; ?>
            </div>

            <!-- JavaScript, um die Meldung nach 5 Sekunden weich auszublenden -->
            <script>
                setTimeout(function() {
                    var msgBox = document.getElementById('status-message');
                    if (msgBox) {
                        // Weicher Übergang (Fade-out)
                        msgBox.style.transition = "opacity 0.6s ease";
                        msgBox.style.opacity = "0";

                        // Element nach dem Ausblenden komplett aus dem Layout entfernen
                        setTimeout(function() {
                            msgBox.style.display = "none";
                        }, 600);
                    }
                }, 5000); // 5000 Millisekunden = 5 Sekunden
            </script>
        <?php endif; ?>

        <!-- Kopfbereich des Dashboards -->
        <div class="dashboard-header">
            <div class="dashboard-title-group">
                <h2>Projektübersicht</h2>
                <p>Verwalten Sie hier Ihre mehrsprachigen SCORM-Projekte.</p>
            </div>

            <div class="dashboard-actions">
                <!-- Link zur Erstellung eines neuen Projekts -->
                <a href="create_project.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Neues Projekt erstellen
                </a>
            </div>
        </div>

        <!-- Tabelle mit Projekten in einer Card -->
        <div class="table-card">
            <table class="project-table">
                <thead>
                    <tr>
                        <th>Projektname</th>
                        <th>SCORM-Version</th>
                        <th>Erstellt am</th>
                        <th class="text-right">Aktionen</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($projekte) > 0): ?>
                        <!-- Schleife über alle Projekte -->
                        <?php foreach ($projekte as $projekt): ?>
                            <tr>
                                <td class="fw-600"><?php echo htmlspecialchars($projekt['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($projekt['scorm_version']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($projekt['created_at'])); ?></td>
                                <td class="text-right table-actions">
                                    <a href="create_project.php?edit_id=<?php echo $projekt['id']; ?>" class="action-edit"><i class="fas fa-pen"></i> Bearbeiten</a>
                                    <a href="delete_project.php?id=<?php echo $projekt['id']; ?>" class="action-delete" onclick="return confirm('Möchten Sie dieses Projekt wirklich löschen?');"><i class="fas fa-trash"></i> Löschen
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Leerer Zustand -->
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <p>Noch keine Projekte vorhanden. Klicken Sie auf "Neues Projekt erstellen", um zu beginnen.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
// Wenn ein Download ansteht, den JavaScript-Trigger ausführen
if ($downloadFile):
?>
    <!-- Automatischer Download-Trigger nach dem Laden der Seite -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Die ZIP-Datei aus dem temp-Ordner im Hintergrund herunterladen
            window.location.href = 'temp/<?php echo $downloadFile; ?>';

            // Optional: Die URL bereinigen, damit der Download bei 'Seite aktualisieren' nicht nochmal startet
            window.history.replaceState({}, document.title, "index.php");
        });
    </script>

<?php
endif;

// Footer einbinden
require_once 'includes/footer.php';
?>