<?php
// Session starten, um den Login-Status zu speichern
session_start();

// Datenbankverbindung einbinden
require_once "includes/db.php";

// Falls der User schon eingeloggt ist, direkt weiterleiten
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

// Prüfen, ob das Formular per POST gesendet wurde
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $error = "Bitte füllen Sie alle Felder aus.";
    } else {
        // Benutzer in der Datenbank suchen
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // Passwort verifizieren
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login erfolgreich: Session-Variablen setzen
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;

            // Weiterleitung zum Dashboard
            header('Location: index.php');
            exit;
        } else {
            $error = 'Benutzername oder Passwort falsch.';
        }
    }
}
// Jetzt das Design (HTML Header) laden
require_once 'includes/header.php';
?>

<div class="login-wrapper">
    <div class="form-wrap">
        <p>· Beginnen Sie hier ·</p>

        <!-- Fehlermeldung anzeigen, falls vorhanden -->
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Login-Formular -->
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" placeholder="z. B. max.mustermann" required>
            </div>

            <div class="form-group">
                <label for="password">Passwort</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" required style="width: 100%; padding-right: 40px; box-sizing: border-box;">

                    <!-- Ausgangszustand: Durchgestrichenes Auge (Passwort ist verborgen) -->
                    <i class="fas fa-eye-slash toggle-password-icon" id="togglePassword" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #cbd5e1;"></i>
                </div>
            </div>

            <script>
                // Event-Listener für das Toggle-Icon
                document.getElementById('togglePassword').addEventListener('click', function() {
                    const passwordInput = document.getElementById('password');

                    // Prüfung des aktuellen Feldtyps und entsprechender Wechsel
                    if (passwordInput.type === 'password') {
                        // Passwort als Klartext anzeigen
                        passwordInput.type = 'text';
                        // Icon auf "geöffnetes Auge" ändern
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    } else {
                        // Passwort wieder maskieren (Punkte)
                        passwordInput.type = 'password';
                        // Icon auf "durchgestrichenes Auge" zurücksetzen
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    }
                });
            </script>

            <button type="submit" class="btn">Anmelden</button>

            <p class="text-bottom">Sie haben Ihr Passwort vergessen? Bitte wenden Sie sich an den <a href="#">Administrator</a>.</p>
        </form>
    </div>
</div>

<?php
// Footer einbinden
require_once 'includes/footer.php';
?>