<?php
session_start(); 

$host = 'localhost';
$dbname = 'kinoteatr';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['mail'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = "Ошибка: Неверный формат электронной почты.";
    $_SESSION['message_type'] = 'error'; 
    header("Location: signup.php");
    exit();
}

$sql = "SELECT id FROM users WHERE mail = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {

    $_SESSION['message'] = "Ошибка: Пользователь с таким email уже существует.";
    $_SESSION['message_type'] = 'error'; 
    header("Location: signup.php");
    exit();
} else {

    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_ARGON2I); 
    $role = 'client';

    $sql = "INSERT INTO users (mail, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $hashed_password, $role);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Регистрация успешна.";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Ошибка регистрации: " . $stmt->error;
        $_SESSION['message_type'] = 'error';
    }

    header("Location: signup.php");
    exit();
}

$stmt->close();
$conn->close();
?>
