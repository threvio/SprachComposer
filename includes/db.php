<?php
// config.ini laden
$configPath = __DIR__ . '/../config.ini';
$config = parse_ini_file($configPath);

if ($config === false) {
    error_log("config.ini konnte nicht geladen werden");
    die("Konfigurationsfehler. Bitte kontaktieren Sie den Administrator.");
}

$host = $config['DB_HOST'];
$dbname = $config['DB_NAME'];
$username = $config['DB_USER'];
$password = $config['DB_PASSWORD'];
$charset = $config['DB_CHARSET'] ?? 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Fortgeschrittene PDO-Optionen
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log("DB connection failed: " . $e->getMessage());
    die("Datenbankverbindung fehlgeschlagen. Bitte später erneut versuchen.");
}
?>