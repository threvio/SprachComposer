<?php
// setup.php - Einmalige Installation der Datenbank und Tabellen

// Lade DB-Daten aus der config.ini
$configFile = __DIR__ . '/config.ini';

if (!file_exists($configFile)) {
    die("<h2 style='color: red;'>Fehler: config.ini nicht gefunden!</h2><p>Bitte erstelle eine Kopie von config.example.ini, benenne sie in config.ini um und trage deine Datenbank-Daten ein.</p>");
}

$config = parse_ini_file($configFile);

// Werte aus der Config auslesen (mit Fallback-Werten)
$host = $config["DB_HOST"] ?? "127.0.0.1";
$user = $config["DB_USER"] ?? "root";
$pass = $config["DB_PASSWORD"] ?? "";
$dbname = $config["DB_NAME"] ?? "sprachcomposer";

try {
    // Verbindung zum Server ohne Datenbank aufbauen
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Datenbank erstellen
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // 2. Tabellen erstellen
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS languages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lang_code VARCHAR(10) NOT NULL,
        lang_name VARCHAR(50) NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        project_name VARCHAR(100) NOT NULL,
        course_title VARCHAR(150) NOT NULL,
        scorm_version VARCHAR(50) DEFAULT 'Wird ermittelt...',
        primary_color VARCHAR(10) DEFAULT '#00386b',
        layout_style ENUM('layout1', 'layout2') DEFAULT 'layout1',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 3. Sprachen einfügen (falls leer)
    $stmt = $pdo->query("SELECT COUNT(*) FROM languages");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO languages (lang_code, lang_name) VALUES 
        ('de', 'Deutsch'),
        ('en', 'Englisch'),
        ('es', 'Spanisch'),
        ('fr', 'Französisch'),
        ('it', 'Italienisch'),
        ('nl', 'Niederländisch')");
    }

    // 4. Admin anlegen (Passwort: admin123)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES ('admin', ?)");
        $stmt->execute([$hash]);
    }

    echo "<h2 style='color: green; font-family: sans-serif;'>Erfolg! Die Datenbank 'sprachcomposer' wurde erfolgreich eingerichtet.</h2>";
    echo "<p style='font-family: sans-serif;'>Du kannst dich jetzt hier einloggen: <a href='login.php'>Zum Login</a></p>";
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Fehler bei der Installation:</h2> " . $e->getMessage();
}
