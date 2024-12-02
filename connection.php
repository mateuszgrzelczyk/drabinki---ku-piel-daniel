<?php
$host = 'localhost';
$dbname = 'Turniej';
$username = 'root'; // Ustaw użytkownika
$password = ''; // Ustaw hasło

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą danych: " . $e->getMessage());
}
?>
