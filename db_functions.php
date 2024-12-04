<?php
require 'connection.php';

// Funkcja dodawania uczestnika
function dodajUczestnika($pdo, $imie) {
    $stmt = $pdo->prepare("INSERT INTO uczestnicy (imie) VALUES (:imie)");
    $stmt->execute(['imie' => $imie]);
}

// Funkcja pobierająca wszystkich uczestników
function pobierzUczestnikow($pdo) {
    $stmt = $pdo->query("SELECT * FROM uczestnicy");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Funkcja generująca drabinkę
function generujDrabinke($pdo, $uczestnicy) {
    $liczbaUczestnikow = count($uczestnicy);

    // Jeśli liczba uczestników jest mniejsza niż 2, zwróć komunikat
    if ($liczbaUczestnikow < 2) {
        return "Liczba uczestników musi być co najmniej 2.";
    }

    // Obliczamy najbliższą potęgę 2 większą lub równą liczbie uczestników
    $najblizszaPotega2 = pow(2, ceil(log($liczbaUczestnikow, 2)));

    // Jeśli liczba uczestników nie jest potęgą 2, dodajemy 'bye' dla tych, którzy nie mają przeciwnika
    $uczestnicyZBye = array_merge($uczestnicy, array_fill(0, $najblizszaPotega2 - $liczbaUczestnikow, null));

    // Generowanie meczów pierwszej rundy
    $runda = 1;
    for ($i = 0; $i < $najblizszaPotega2; $i += 2) {
        if ($uczestnicyZBye[$i] !== null && $uczestnicyZBye[$i + 1] !== null) {
            // Zapisujemy mecz, jeśli obaj uczestnicy są dostępni
            zapiszMecz($pdo, $runda, $uczestnicyZBye[$i]['id'], $uczestnicyZBye[$i + 1]['id'], null);
        } elseif ($uczestnicyZBye[$i] !== null && $uczestnicyZBye[$i + 1] === null) {
            // Uczestnik z bye przechodzi automatycznie do następnej rundy
            zapiszMecz($pdo, $runda, $uczestnicyZBye[$i]['id'], null, $uczestnikZBye[$i]['id']);
        } elseif ($uczestnicyZBye[$i] === null && $uczestnicyZBye[$i + 1] !== null) {
            // Uczestnik z bye przechodzi automatycznie do następnej rundy
            zapiszMecz($pdo, $runda, null, $uczestnicyZBye[$i + 1]['id'], $uczestnicyZBye[$i + 1]['id']);
        }
    }

    // Generowanie drugiej rundy dla zwycięzców pierwszej
    generujKolejnaRunde($pdo, 1); // Przekazujemy 1 jako numer rundy pierwszej

    return "Drabinka została wygenerowana dla $najblizszaPotega2 uczestników.";
}

// Funkcja generująca kolejną rundę
function generujKolejnaRunde($pdo, $runda) {
    $mecze = pobierzMecze($pdo, $runda);
    $zwyciezcy = [];

    foreach ($mecze as $mecz) {
        if ($mecz['zwyciezca_id'] !== null) {
            $zwyciezcy[] = $mecz['zwyciezca_id'];
        }
    }

    if (count($zwyciezcy) < 2) {
        return "Brak wystarczającej liczby zwycięzców, aby utworzyć nową rundę.";
    }

    $nowaRunda = $runda + 1;

    // Generujemy mecze dla zwycięzców poprzedniej rundy
    for ($i = 0; $i < count($zwyciezcy); $i += 2) {
        zapiszMecz($pdo, $nowaRunda, $zwyciezcy[$i], $zwyciezcy[$i + 1], null);
    }

    return "Nowa runda ($nowaRunda) została wygenerowana.";
}

// Funkcja zapiszMecz i inne pozostają niezmienione


// Funkcja zapisująca mecz
function zapiszMecz($pdo, $runda, $uczestnik1_id, $uczestnik2_id, $zwyciezca_id) {
    // Jeśli uczestnik2_id jest null (był bye), zapisujemy tylko uczestnika1
    if ($uczestnik2_id === null) {
        // Zapisujemy mecz, tylko dla uczestnika1, który przechodzi do następnej rundy
        $sql = "INSERT INTO mecze (runda, uczestnik1_id, uczestnik2_id, zwyciezca_id) 
                VALUES (:runda, :uczestnik1_id, NULL, :zwyciezca_id)";
    } else {
        // Zapisujemy standardowy mecz, gdy obaj uczestnicy są obecni
        $sql = "INSERT INTO mecze (runda, uczestnik1_id, uczestnik2_id, zwyciezca_id) 
                VALUES (:runda, :uczestnik1_id, :uczestnik2_id, :zwyciezca_id)";
    }

    // Przygotowanie zapytania SQL
    $stmt = $pdo->prepare($sql);

    // Wiązanie parametrów
    $stmt->bindParam(':runda', $runda);
    $stmt->bindParam(':uczestnik1_id', $uczestnik1_id);

    // Związanie uczestnika 2, jeśli nie ma "bye"
    if ($uczestnik2_id !== null) {
        $stmt->bindParam(':uczestnik2_id', $uczestnik2_id);
    }

    $stmt->bindParam(':zwyciezca_id', $zwyciezca_id);

    // Wykonanie zapytania
    $stmt->execute();
}

// Funkcja zapisująca zwycięzcę w meczu
function zapiszZwyciezce($pdo, $runda, $ucz1, $ucz2, $zwyciezca) {
    $stmt = $pdo->prepare("UPDATE mecze SET zwyciezca_id = :zwyciezca WHERE runda = :runda AND uczestnik1_id = :ucz1 AND uczestnik2_id = :ucz2");
    $stmt->execute([
        'runda' => $runda,
        'ucz1' => $ucz1,
        'ucz2' => $ucz2,
        'zwyciezca' => $zwyciezca
    ]);
}

// Funkcja pobierająca mecze z danej rundy
function pobierzMecze($pdo, $runda) {
    $stmt = $pdo->prepare("SELECT mecze.*, u1.imie AS uczestnik1, u2.imie AS uczestnik2, uw.imie AS zwyciezca
                         FROM mecze
                         LEFT JOIN uczestnicy u1 ON mecze.uczestnik1_id = u1.id
                         LEFT JOIN uczestnicy u2 ON mecze.uczestnik2_id = u2.id
                         LEFT JOIN uczestnicy uw ON mecze.zwyciezca_id = uw.id
                         WHERE mecze.runda = :runda
                         ORDER BY mecze.id");
    $stmt->execute(['runda' => $runda]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Funkcja sprawdzająca, czy turniej się zakończył
function sprawdzCzyKoniecTurnieju($pdo) {
    $stmt = $pdo->query("SELECT COUNT(DISTINCT zwyciezca_id) AS liczba_zwyciezcow FROM mecze WHERE zwyciezca_id IS NOT NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['liczba_zwyciezcow'] === 1) {
        $stmt = $pdo->query("SELECT u.imie FROM uczestnicy u 
                             JOIN mecze m ON u.id = m.zwyciezca_id 
                             WHERE m.zwyciezca_id IS NOT NULL LIMIT 1");
        $zwyciezca = $stmt->fetch(PDO::FETCH_ASSOC);
        return $zwyciezca['imie'];
    }

    return false;
}

// Funkcja sprawdzająca, czy runda finałowa została zakończona
function czyRundaFinalowaZakonczona($pdo) {
    // Sprawdzamy ostatnią rundę
    $stmt = $pdo->query("SELECT runda, COUNT(*) AS liczba_meczy, SUM(zwyciezca_id IS NOT NULL) AS zakonczone_mecze
                         FROM mecze
                         GROUP BY runda
                         ORDER BY runda DESC
                         LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jeśli jest dokładnie jeden mecz w ostatniej rundzie i został zakończony
    if ($result && $result['liczba_meczy'] == 1 && $result['zakonczone_mecze'] == 1) {
        return true;
    }

    return false;
}

// Funkcja pobierająca całą drabinkę
function pobierzDrabinke($pdo) {
    $stmt = $pdo->query("SELECT mecze.*, u1.imie AS uczestnik1, u2.imie AS uczestnik2, uw.imie AS zwyciezca
                         FROM mecze
                         LEFT JOIN uczestnicy u1 ON mecze.uczestnik1_id = u1.id
                         LEFT JOIN uczestnicy u2 ON mecze.uczestnik2_id = u2.id
                         LEFT JOIN uczestnicy uw ON mecze.zwyciezca_id = uw.id
                         ORDER BY mecze.runda, mecze.id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function resetujTurniej($pdo) {
    // Najpierw usuwamy dane z tabeli mecze
    $pdo->exec("DELETE FROM mecze");

    // Teraz możemy bezpiecznie usunąć dane z tabeli uczestnicy
    $pdo->exec("DELETE FROM uczestnicy");

    return "Turniej został zresetowany. Możesz rozpocząć nowy turniej.";
}

// Funkcja logowania
function zaloguj($pdo, $username, $password) {
    $sql = "SELECT * FROM users WHERE username = :username AND password = MD5(:password)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username, 'password' => $password]);
    return $stmt->fetch(PDO::FETCH_ASSOC); // Pobiera dane użytkownika, jeśli są poprawne
}

// Funkcja sprawdzająca rolę użytkownika
function sprawdzRole() {
    return $_SESSION['role'] ?? null;
}

// Funkcja sprawdzająca, czy użytkownik jest zalogowany
function czyZalogowany() {
    return isset($_SESSION['username']);
}

// Funkcja wylogowania
function wyloguj() {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Funkcja dodająca obserwatora
function dodajObserwatora($pdo, $username, $password) {
    // Sprawdzamy, czy użytkownik o podanym loginie już istnieje
    $sql = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Użytkownik o tym loginie już istnieje.");
    }

    // Dodawanie nowego użytkownika (obserwatora)
    $sql = "INSERT INTO users (username, password, role) VALUES (:username, MD5(:password), 'observer')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'username' => $username,
        'password' => $password
    ]);

    return "Obserwator $username został dodany.";
}

?>


    return "Obserwator $username został dodany.";
}

?>
