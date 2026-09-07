<?php
session_start();

//1.Alle Sitzungsvariablen entfernen
$_SESSION = [];

//2.Session-Cookie im Browser löschen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"],
    );
}

//3.Sitzung auf dem Server zerstoren
session_destroy();

//4.Zuruck zur Anmeldung
header('Location: login.php');
exit;
