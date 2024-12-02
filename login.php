<?php
require 'db_functions.php';
session_start();

$komunikat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logowanie użytkownika
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $uzytkownik = zaloguj($pdo, $username, $password);

        if ($uzytkownik) {
            // Logowanie poprawne
            $_SESSION['username'] = $uzytkownik['username'];
            $_SESSION['role'] = $uzytkownik['role'];
            header("Location: index.php");
            exit();
        } else {
            // Błąd logowania
            $komunikat = "Niepoprawny login lub hasło.";
        }
    }

    // Rejestracja nowego obserwatora
    if (isset($_POST['register_observer'])) {
        $username = trim($_POST['register_username']);
        $password = trim($_POST['register_password']);

        if (!empty($username) && !empty($password)) {
            try {
                $komunikat = dodajObserwatora($pdo, $username, $password);
            } catch (PDOException $e) {
                $komunikat = "Błąd: Nazwa użytkownika jest już zajęta.";
            }
        } else {
            $komunikat = "Nazwa użytkownika i hasło nie mogą być puste.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 400px; margin: auto; }
        .message { color: red; }
    </style>
</head>
<body>
<div class="container">
    <h1>Logowanie</h1>
    <!-- Formularz logowania -->
    <form method="POST">
        <label for="username">Login:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit" name="login">Zaloguj</button>
    </form>

    <p class="message"><?= htmlspecialchars($komunikat) ?></p>

    <!-- Link do rejestracji -->
    <p>Nie masz konta? <a href="#register_form">Zarejestruj się</a></p>

    <!-- Formularz rejestracji dla obserwatora (wysuwany po kliknięciu) -->
    <div id="register_form" style="display: none;">
        <h2>Rejestracja Obserwatora</h2>
        <form method="POST">
            <label for="register_username">Login:</label>
            <input type="text" id="register_username" name="register_username" required>
            <label for="register_password">Hasło:</label>
            <input type="password" id="register_password" name="register_password" required>
            <button type="submit" name="register_observer">Utwórz konto</button>
        </form>
    </div>

    <p><a href="index.php">Wróć do strony głównej</a></p>
</div>

<script>
    // Wyświetlenie formularza rejestracji
    document.querySelector('a[href="#register_form"]').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('register_form').style.display = 'block';
    });
</script>

</body>
</html>
