<?php
session_start();

$host = 'localhost';
$db   = 'kinoteatr';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}



if (!isset($_POST['movie_id'], $_POST['day'], $_POST['time'], $_POST['hall_id'])) {
    $_SESSION['message'] = 'Необходимо заполнить все поля!';
    header('Location: profile.php'); 
    exit;
}

$movie_id = $_POST['movie_id'];
$day = $_POST['day'];
$time = $_POST['time'];
$hall_id = $_POST['hall_id'];

$sql = "INSERT INTO sessions (movie_id, day, time, hall_id) VALUES (?, ?, ?, ?)";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$movie_id, $day, $time, $hall_id]);


    $_SESSION['message'] = 'Сеанс успешно добавлен!';
    header('Location: profile.php'); 
} catch (PDOException $e) {
    $_SESSION['message'] = "Ошибка при добавлении сеанса: " . $e->getMessage();
    header('Location: profile.php'); // Перенаправление обратно на страницу добавления сеанса в случае ошибки
}
?>
