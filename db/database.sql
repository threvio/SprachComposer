-- 1. Datenbank erstellen (falls nicht vorhanden) und auswählen
CREATE DATABASE IF NOT EXISTS sprachcomposer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sprachcomposer;

-- 2. Tabelle für Administratoren / Benutzer (Authentifizierung)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Schifarnik / Tabelle für unterstützte Sprachen (Alle 6 Sprachen)
CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lang_code VARCHAR(10) NOT NULL,
    lang_name VARCHAR(50) NOT NULL
);

-- 4. Haupttabelle für generierte SCORM-Projekte (mit Foreign Key zu Users)
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_name VARCHAR(100) NOT NULL,
    course_title VARCHAR(150) NOT NULL,
    scorm_version ENUM('1.2', '2004') NOT NULL DEFAULT '1.2',
    primary_color VARCHAR(10) DEFAULT '#00386b',
    layout_style ENUM('layout1', 'layout2') DEFAULT 'layout1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Alle 6 unterstützten Sprachen in den Schifarnik einfügen
INSERT INTO languages (lang_code, lang_name) VALUES 
('de', 'Deutsch'),
('en', 'Englisch'),
('es', 'Spanisch'),
('fr', 'Französisch'),
('it', 'Italienisch'),
('nl', 'Niederländisch');

-- 6. Standard-Admin anlegen (Passwort für den Test: admin123)
-- Hash entspricht dem Bcrypt-Hash für 'admin123'
INSERT INTO users (username, password_hash) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');