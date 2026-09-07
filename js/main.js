/*** Hauptskript der Anwendung > Beinhaltet die Event-Listener und UI-Logik ***/

document.addEventListener("DOMContentLoaded", function () {
    /* 1. PROGRESS BAR LOGIK (Beim Absenden des Formulars) */

    // Formular-Element für die Projekt-Erstellung abrufen
    let projectForm = document.getElementById("project-form");

    // Das Skript wird nur ausgeführt, wenn das Formular auf der aktuellen Seite existiert
    if (projectForm) {
        projectForm.addEventListener("submit", function () {
            // Fortschrittsanzeige (Progress Bar) einblenden
            document.getElementById("upload-progress").style.display = "block";

            // Speichern-Button ausblenden, um doppelte Formularabsendungen zu verhindern
            document.getElementById("submit-btn").style.display = "none";

            // Visuelle Simulation des Ladevorgangs (während der Server im Hintergrund arbeitet)
            let width = 0;
            let bar = document.getElementById("progress-bar-fill");
            let text = document.getElementById("progress-percent-text");

            let interval = setInterval(function () {
                if (width >= 95) {
                    // Bei 90% anhalten, bis der Server die endgültige Datei zurückgibt
                    clearInterval(interval);
                } else {
                    // Zufälligen Fortschrittswert hinzufügen (für einen natürlicheren Lade-Effekt)
                    width += Math.floor(Math.random() * 10) + 2;
                    if (width > 95) width = 95;
                    bar.style.width = width + "%";
                    text.innerText = width + "%";
                }
            }, 600);
        });
    }

    /* 2. CUSTOM FILE UPLOAD LOGIK (Dateinamen anzeigen) */
    let hiddenInputs = document.querySelectorAll(".hidden-file-input");

    hiddenInputs.forEach(function (input) {
        input.addEventListener("change", function () {
            // Findet das zugehörige Text-Element anhand der ID (bspw. "text_zip_de")
            let textSpan = document.getElementById("text_" + this.id);

            if (textSpan) {
                if (this.files && this.files.length > 0) {
                    // Wenn Datei ausgewählt: Dateinamen anzeigen und Farbe ändern
                    textSpan.textContent = this.files[0].name;
                    textSpan.style.color = "var(--blue)";
                    textSpan.style.fontWeight = "600";

                    // ZUGEFÜGT: Die zugehörige Checkbox automatisch aktivieren
                    let langCode = this.id.replace("zip_", "");
                    document.querySelector(
                        'input[value="' + langCode + '"]',
                    ).checked = true;
                } else {
                    // Wenn nichts ausgewählt: Standardtext anzeigen
                    textSpan.textContent = "Keine ausgewählt";
                    textSpan.style.color = "var(--text-light)";
                    textSpan.style.fontWeight = "normal";
                }
            }
        });
    });
});
