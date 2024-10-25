<?php
session_start();


$host = 'localhost'; 
$db = 'kinoteatr'; 
$user = 'root'; 
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Подключение не удалось: ' . $e->getMessage());
}


$sessionId = $_POST['session_id'];
$seatIds = explode(',', $_POST['seat_ids']);
$email = $_SESSION['email'];



$token = bin2hex(random_bytes(32));
$stmt = $pdo->prepare('SELECT hall_id FROM sessions WHERE id = ? LIMIT 1');
$stmt->execute([$sessionId]);
$hallId = $stmt->fetchColumn();

foreach ($seatIds as $seatId) {
    $stmt = $pdo->prepare('UPDATE bookings SET token = ? WHERE seat_id = ?');
    $stmt->execute([$token, $seatId]);
}
if ($hallId === false) {
    die('Не удалось получить hall_id.');
}

$stmt = $pdo->prepare('SELECT price FROM halls WHERE hall_id = ? LIMIT 1');
$stmt->execute([$hallId]);
$seatPrice = $stmt->fetchColumn();

if ($seatPrice === false) {
    die('Не удалось получить цену билета.');
}
$bonus1 = isset($_POST['useBonuses']) ? (int)$_POST['useBonuses'] : 0;

if ($bonus1 === 0) {
    $bonus = 0;
    $_SESSION['$use_bonuses'] = false;
}
else{
$bonus = $_SESSION['userBonus'];
$_SESSION['$use_bonuses'] = true;

}

$totalPrice = ($seatPrice * count($seatIds));
$totalPrice -= $bonus;
$_SESSION['price'] = $totalPrice;
$price = $_SESSION['price'];


$paymentData = [
    'amount' => [
        'value' => $price,
        'currency' => 'RUB',
    ],
    'confirmation' => [
        'type' => 'redirect',
        'return_url' => 'http://localhost/payment_success2.php?session_id=' . $sessionId . '&seat_ids=' . implode(',', $seatIds) . '&token=' . $token,
    ],
    'capture' => true,
    'description' => 'Покупка билетов в кинотеатр',
];


$shopId = '389385';
$secretKey = 'test_ULXoHMfmSWIgrHO5zlb9djkI7tdTT9rNL3uGIy21pGE';

$ch = curl_init('https://api.yookassa.ru/v3/payments');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Idempotence-Key: ' . uniqid(),
    'Authorization: Basic ' . base64_encode("$shopId:$secretKey"),
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($paymentData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);
if (isset($responseData['confirmation']['confirmation_url'])) {
    header('Location: ' . $responseData['confirmation']['confirmation_url']);
    exit();
} else {
    echo 'Ошибка при создании платежа. Пожалуйста, попробуйте позже.';
}
?>
