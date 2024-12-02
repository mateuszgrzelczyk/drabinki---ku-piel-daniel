<?php
require 'db_functions.php';
session_start();

$komunikat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            // Dodajemy obserwatora do bazy danych
            $komunikat = dodajObserwatora($pdo, $username, $password);
        } catch (PDOException $e) {
            $komunikat = "Błąd: Nazwa użytkownika jest już zajęta.";
        }
    } else {
        $komunikat = "Nazwa użytkownika i hasło nie mogą być puste.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja Obserwatora</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 400px; margin: auto; }
        .message { color: red; }
    </style>
</head>
<body>
<div class="container">
    <h1>Rejestracja Obserwatora</h1>
    <!-- Formularz rejestracji obserwatora -->
    <form method="POST">
        <label for="username">Login:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Utwórz konto</button>
    </form>
    <p class="message"><?= htmlspecialchars($komunikat) ?></p>
    <p><a href="login.php">Wróć do logowania</a></p>
</div>
</body>
</html>
