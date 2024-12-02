<?php
require 'db_functions.php';
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!czyZalogowany()) {
    header("Location: login.php");
    exit();
}

// Sprawdzamy rolę użytkownika
$rola = sprawdzRole();

$komunikat = '';
$pokazPrzyciskZakonczTurniej = false;

// Obsługa żądań POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($rola === 'admin') { // Tylko administrator ma pełne uprawnienia
        if (isset($_POST['dodajUczestnika'])) {
            $imie = trim($_POST['imie']);
            if (!empty($imie)) {
                dodajUczestnika($pdo, $imie);
            } else {
                $komunikat = "Imię uczestnika nie może być puste.";
            }
        }

        if (isset($_POST['generujDrabinke'])) {
            $uczestnicy = pobierzUczestnikow($pdo);
            $komunikat = generujDrabinke($pdo, $uczestnicy);
        }

        if (isset($_POST['zapiszMecz'])) {
            $runda = $_POST['runda'];
            $ucz1 = $_POST['uczestnik1'];
            $ucz2 = $_POST['uczestnik2'];
            $zwyciezca = $_POST['zwyciezca'];
            zapiszZwyciezce($pdo, $runda, $ucz1, $ucz2, $zwyciezca);
        }

        if (isset($_POST['zakonczTurniej'])) {
            $zwyciezca = sprawdzCzyKoniecTurnieju($pdo);
            if ($zwyciezca) {
                $komunikat = "Turniej zakończony! Zwycięzca: $zwyciezca";
            } else {
                $komunikat = "Turniej jeszcze nie zakończony.";
            }
        }

        if (isset($_POST['nowyTurniej'])) {
            resetujTurniej($pdo);
            header("Location: index.php");
            exit();
        }
    } else {
        $komunikat = "Brak uprawnień do wykonania tej akcji.";
    }
}

// Pobieranie danych do wyświetlenia
$uczestnicy = pobierzUczestnikow($pdo);
$drabinka = pobierzDrabinke($pdo);
$pokazPrzyciskZakonczTurniej = czyRundaFinalowaZakonczona($pdo);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Turniej</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: auto; }
        .message { color: red; }
    </style>
</head>
<body>
<div class="container">
    <h1>Turniej</h1>
    <p>Witaj, <?= htmlspecialchars($_SESSION['username']) ?>! (<a href="logout.php">Wyloguj</a>)</p>
    <p class="message"><?= htmlspecialchars($komunikat) ?></p>

    <?php if ($rola === 'admin'): ?>
        <!-- Formularz dodawania uczestnika (tylko dla administratora) -->
        <form method="POST">
            <label for="imie">Imię uczestnika:</label>
            <input type="text" id="imie" name="imie" required>
            <button type="submit" name="dodajUczestnika">Dodaj uczestnika</button>
        </form>
    <?php endif; ?>

    <!-- Lista uczestników (widoczna dla wszystkich) -->
    <h2>Lista uczestników:</h2>
    <ul>
        <?php foreach ($uczestnicy as $uczestnik): ?>
            <li><?= htmlspecialchars($uczestnik['imie']) ?></li>
        <?php endforeach; ?>
    </ul>

    <?php if ($rola === 'admin'): ?>
        <!-- Generowanie drabinki (tylko dla administratora) -->
        <form method="POST">
            <button type="submit" name="generujDrabinke">Generuj drabinkę</button>
        </form>
    <?php endif; ?>

    <!-- Drabinka turniejowa (widoczna dla wszystkich) -->
    <h2>Drabinka turniejowa:</h2>
    <div class="drabinka">
        <?php if (!empty($drabinka)): ?>
            <?php foreach ($drabinka as $mecz): ?>
                <?php if ($mecz['zwyciezca'] === null && $rola === 'admin'): ?>
                    <!-- Formularz wyboru zwycięzcy meczu -->
                    <form method="POST" style="margin-bottom: 10px;">
                        <input type="hidden" name="runda" value="<?= $mecz['runda'] ?>">
                        <input type="hidden" name="uczestnik1" value="<?= $mecz['uczestnik1_id'] ?>">
                        <input type="hidden" name="uczestnik2" value="<?= $mecz['uczestnik2_id'] ?>">

                        <label for="zwyciezca">Zwycięzca:</label>
                        <select name="zwyciezca" required>
                            <option value="<?= $mecz['uczestnik1_id'] ?>"><?= htmlspecialchars($mecz['uczestnik1']) ?></option>
                            <option value="<?= $mecz['uczestnik2_id'] ?>"><?= htmlspecialchars($mecz['uczestnik2']) ?></option>
                        </select>
                        <button type="submit" name="zapiszMecz">Zapisz zwycięzcę</button>
                    </form>
                <?php else: ?>
                    <!-- Wyświetlanie wyników meczu -->
                    <p>
                        Runda <?= $mecz['runda'] ?>: <?= htmlspecialchars($mecz['uczestnik1']) ?> vs <?= htmlspecialchars($mecz['uczestnik2']) ?> | 
                        Zwycięzca: <?= htmlspecialchars($mecz['zwyciezca']) ?>
                    </p>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Brak danych do wyświetlenia drabinki.</p>
        <?php endif; ?>
    </div>

    <?php if ($rola === 'admin'): ?>
        <!-- Przycisk zakończenia turnieju (tylko dla administratora) -->
        <?php if ($pokazPrzyciskZakonczTurniej): ?>
            <form method="POST" style="margin-top: 20px;">
                <button type="submit" name="zakonczTurniej">Zakończ turniej</button>
            </form>
        <?php endif; ?>

        <!-- Przycisk nowego turnieju (tylko dla administratora) -->
        <form method="POST" style="margin-top: 20px;">
            <button type="submit" name="nowyTurniej">Nowy turniej</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
