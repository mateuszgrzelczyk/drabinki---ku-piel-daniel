<?php
session_start();

if (isset($_POST['reset'])) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['numParticipants']) && $_POST['numParticipants'] > 0) {
        $_SESSION['numParticipants'] = intval($_POST['numParticipants']);
        $_SESSION['round'] = 1;
        $_SESSION['rounds'] = []; 
    }

    if (isset($_POST['participants'])) {
        $_SESSION['participants'] = $_POST['participants'];
    }

    if (isset($_POST['winners'])) {
        if (!isset($_SESSION['rounds'][$_SESSION['round']])) {
            $_SESSION['rounds'][$_SESSION['round']] = [];
        }
        $_SESSION['rounds'][$_SESSION['round']] = $_SESSION['participants'];
        $_SESSION['participants'] = $_POST['winners'];
        $_SESSION['round']++;
    }

    if (isset($_SESSION['participants']) && count($_SESSION['participants']) === 1) {
        $_SESSION['finalWinner'] = $_SESSION['participants'][0];
        $_SESSION['rounds'][$_SESSION['round']] = $_SESSION['participants'];
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Drabinek Turniejowych</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Generator Drabinek Turniejowych</h1>

        <?php if (!isset($_SESSION['numParticipants']) || $_SESSION['numParticipants'] === 0): ?>
            <form method="POST" action="">
                <label for="numParticipants">Podaj liczbę uczestników:</label>
                <input type="number" id="numParticipants" name="numParticipants" min="2" required>
                <button type="submit">Dalej</button>
            </form>
        <?php elseif (!isset($_SESSION['participants'])): ?>
            <h2>Wprowadź imiona i nazwiska uczestników:</h2>
            <form method="POST" action="">
                <div class="participants-list">
                    <?php for ($i = 1; $i <= $_SESSION['numParticipants']; $i++): ?>
                        <div>
                            <label for="participant_<?= $i ?>">Uczestnik <?= $i ?>:</label>
                            <input type="text" name="participants[]" id="participant_<?= $i ?>" required>
                        </div>
                    <?php endfor; ?>
                </div>
                <button type="submit">Generuj Drabinkę</button>
            </form>
        <?php elseif (isset($_SESSION['participants']) && !isset($_SESSION['finalWinner'])): ?>
            <h2>Runda <?= $_SESSION['round'] ?></h2>
            <div class="bracket">
                <?php
                shuffle($_SESSION['participants']);
                $matches = array_chunk($_SESSION['participants'], 2);
                ?>
                <form method="POST" action="">
                    <?php foreach ($matches as $index => $match): ?>
                        <div class="match">
                            <?php if (count($match) === 2): ?>
                                <div class="participant"><?= htmlspecialchars($match[0]) ?></div>
                                <div class="participant"><?= htmlspecialchars($match[1]) ?></div>
                                <label for="winner_<?= $index ?>">Wybierz zwycięzcę:</label>
                                <select name="winners[]" id="winner_<?= $index ?>" required>
                                    <option value="<?= htmlspecialchars($match[0]) ?>"><?= htmlspecialchars($match[0]) ?></option>
                                    <option value="<?= htmlspecialchars($match[1]) ?>"><?= htmlspecialchars($match[1]) ?></option>
                                </select>
                            <?php else: ?>
                                <div class="participant"><?= htmlspecialchars($match[0]) ?></div>
                                <input type="hidden" name="winners[]" value="<?= htmlspecialchars($match[0]) ?>" />
                                <p>Brak pary, przechodzi dalej</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit">Przejdź do następnej rundy</button>
                </form>
            </div>
        <?php elseif (isset($_SESSION['finalWinner'])): ?>

            <h2>Podsumowanie Turnieju</h2>
            <div class="summary">
                <?php foreach ($_SESSION['rounds'] as $roundNumber => $roundParticipants): ?>
                    <h3>Runda <?= $roundNumber ?></h3>
                    <div class="round">
                        <?php foreach ($roundParticipants as $participant): ?>
                            <div class="participant"><?= htmlspecialchars($participant) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="final">
                <h1>Zwycięzca: <span style="color: red"><?= htmlspecialchars($_SESSION['finalWinner']) ?></span></h1>
            </div>
            <form method="POST" action="">
                <button type="submit" name="reset" value="true">Zacznij od nowa</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

