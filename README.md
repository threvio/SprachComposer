# SprachComposer

**SprachComposer** ist eine professionelle Web-Anwendung zur automatisierten Erstellung von mehrsprachigen, standardkonformen E-Learning-Modulen. Die integrierte Engine erkennt automatisch das Format der hochgeladenen Kurse und unterstützt die Generierung von Master-Paketen für **SCORM 1.2, SCORM 2004 sowie xAPI (Tin Can)**.

---

## Erste Schritte & Setup (Installation)

Wenn du das Projekt zum ersten Mal von Git herunterlädst und auf deinem lokalen Server (z. B. XAMPP) einrichtest, folge diesen Schritten:

1. **Repository klonen oder kopieren:**
   Platziere den Projektordner `SprachComposer` in das Webserver-Verzeichnis (z. B. `C:/xampp/htdocs/SprachComposer`).

2. **XSD-Templates sicherstellen:**
   Stelle sicher, dass sich im Verzeichnis `xsd_templates/` die benötigten technischen XML-Definitionsdateien (z.B. `adlcp_rootv1p2.xsd`) befinden. Diese werden beim Generieren von SCORM-Paketen automatisch vom System injiziert, um die LMS-Validität sicherzustellen.

3. **Server starten:**
   Starte **Apache** und **MySQL** über dein XAMPP Control Panel.

4. **Konfiguration (WICHTIG):**
   Erstelle eine Kopie der Datei `config.example.ini`, benenne sie in `config.ini` um und passe bei Bedarf die Datenbank-Zugangsdaten (User/Passwort) an.

5. **Automatisches Setup (Der Setup-Assistent):**
   Öffne deinen Browser und rufe die setup.php in deinem lokalen Projektordner auf.
   Beispiel: http://localhost/SprachComposer/setup.php
   _(Wenn alles glatt läuft, wird die Datenbank samt Tabellen automatisch eingerichtet und es erscheint eine grüne Erfolgsmeldung mit direktem Link zum Login)._

6. **Erster Login in die App:**
   Klicke auf den Link zur Anmeldeseite oder rufe direkt **`http://localhost/SprachComposer/login.php`** auf.
    - **Benutzername:** `admin`
    - **Passwort:** `admin123`

---

## Tech Stack & Features

- **Backend:** PHP 8.x (PDO, ZipArchive, RecursiveDirectoryIterator)
- **Template Engine:** Smarty 5
- **Datenbank:** MySQL / MariaDB (Mit automatischer Schema-Migration)
- **Kern-Features:**
    - **Smart Standard Detection:** Automatische Erkennung von SCORM 1.2, SCORM 2004 und xAPI über Datei-Analyse (`imsmanifest.xml` / `tincan.xml`).
    - **Master Manifest Generator:** Dynamische Auflistung aller Ressourcen-Dateien inkl. rekursivem Verzeichnis-Scan.
    - **Multi-Language Packaging:** Bündelung von Sprachversionen (DE, EN, ES, FR, IT, NL) in einem zentralen Landing-Page-Wrapper.
    - **Dynamisches Layout:** Erstellung von HTML-Einstiegsseiten (Zentriert & Split-Screen) gesteuert über Smarty-Templates.

---

## Lizenz

Im Rahmen der IHK-Abschlussprojektarbeit erstellt.
