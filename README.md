# SprachComposer

**SprachComposer** ist eine professionelle Web-Anwendung zur automatisierten Erstellung von mehrsprachigen, standardkonformen SCORM-E-Learning-Modulen (Version 1.2 und 2004).

---

## Erste Schritte & Setup (Installation)

Wenn du das Projekt zum ersten Mal von Git herunterlädst und auf deinem lokalen Server (z. B. XAMPP) einrichtest, folge diesen Schritten:

1. **Repository klonen oder kopieren:**
   Platziere den Projektordner `SprachComposer` in das Webserver-Verzeichnis (z. B. `C:/xampp/htdocs/SprachComposer`).

2. **Server starten:**
   Starte **Apache** und **MySQL** über dein XAMPP Control Panel.

3. **Konfiguration (WICHTIG):**
   Erstelle eine Kopie der Datei `config.example.ini`, benenne sie in `config.ini` um und passe bei Bedarf die Datenbank-Zugangsdaten (User/Passwort) an.

4. **Automatisches Setup (Der Setup-Assistent):**
   Öffne deinen Browser und rufe folgenden Link auf, um die Datenbank und den Standard-Admin automatisch anzulegen: **`http://localhost/SprachComposer/setup.php`**
   _(Wenn alles glatt läuft, erscheint eine grüne Erfolgsmeldung mit direktem Link zum Login)._

5. **Erster Login in die App:**
   Klicke auf den Link zur Anmeldeseite oder rufe direkt **`http://localhost/SprachComposer/login.php`** auf.
    - **Benutzername:** `admin`
    - **Passwort:** `admin123`

---

## Tech Stack & Features

- **Backend:** PHP 8.x (PDO, ZipArchive, MIME-Type Validation)
- **Datenbank:** MySQL / MariaDB (Mit automatischer Tabellen-Erstellung)
- **Features:** Multi-Language Packaging (DE, EN, ES, FR, IT, NL), dynamische Landing-Page-Generierung (Zentriert & Split-Screen), CRUD-Dashboard mit Sticky Headers und internem Scrollen.

---

## Lizenz

Im Rahmen der IHK-Abschlussprojektarbeit erstellt.
