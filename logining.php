<?php
session_start();

$host = 'localhost';
$dbname = 'kinoteatr';
$dbuser = 'root';
$dbpass = '';


$conn = new mysqli($host, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$email = $_POST['email'];
$password = $_POST['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = "Неверный формат электронной почты";
    header("Location: login.php");
    exit();
}


$sql = "SELECT * FROM users WHERE mail = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['emailconf'] = true;
        $_SESSION['email'] = $email;
        $_SESSION['user_id'] = $user['id'];
        header("Location: profile.php");
    } else {
        $_SESSION['message'] = "Неверный логин или пароль";
        header("Location: login.php");
    }
} else {
    $_SESSION['message'] = "Неверный логин или пароль";
    header("Location: login.php");
}

$stmt->close();
$conn->close();
?>
