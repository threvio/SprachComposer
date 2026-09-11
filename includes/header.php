<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SprachComposer</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css">
</head>

<body>
    <!-- Header -->
    <header id="header">
        <div class="container header-inner">
            <div class="header-left">
                <h1>
                    <a href="index.php" style="text-decoration: none; color: inherit;">Sprach<span>Composer</span></a>
                </h1>
            </div>
            <!-- Prüfen, ob der Benutzer eingeloggt ist (Session existiert) -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="header-right">

                    <div class="user-info">
                        <p class="meta">
                            <i class="fas fa-user"></i> Angemeldet als:
                            <!-- Den dynamischen Benutzernamen aus der Session ausgeben -->
                            <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                        </p>

                        <!-- Das aktuelle Datum auf Deutsch formatieren (Monat Tag, Jahr) -->
                        <?php
                        $monate = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
                        $aktuellerMonat = $monate[date('n') - 1]; // date('n') vraca broj meseca od 1 do 12
                        $formatiertesDatum = $aktuellerMonat . ' ' . date('j, Y'); // Spajamo: Mesec Dan, Godina
                        ?>
                        <p><span> | <?php echo $formatiertesDatum; ?></span></p>
                    </div>

                    <!-- Logout-Button -->
                    <a href="logout.php" class="logout-btn" title="Abmelden">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>

                </div>
            <?php endif; ?>
        </div>
    </header>