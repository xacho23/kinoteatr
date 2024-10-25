<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;

require 'vendor/autoload.php';

session_start();
if (!isset($_SESSION['loggedin'])) {
    $email = $_POST['email'];
}else{
    $email = $_SESSION['email'];
}

$host = 'localhost';
$db = 'kinoteatr';
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
    die('Подключение не удалось: ' . $e->getMessage());
}

$sessionId = $_POST['session_id'];
$seatIds = explode(',', $_POST['seat_ids']);
$userId = $_SESSION['user_id'];


$stmt = $pdo->prepare('SELECT time, day, movie_id FROM sessions WHERE id = ?');
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if ($session) {
    $currentTime = new DateTime();
    $startTime = new DateTime($session['time']);
    $interval = $currentTime->diff($startTime);

    if (true) {

        foreach ($seatIds as $seatId) {
            $stmt = $pdo->prepare('INSERT INTO bookings (session_id, seat_id, user_id, status, email) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$sessionId, $seatId, $userId, 'Бронь', $email]);
        }

        function getBookingsForUser($email) {
            global $pdo;
            $stmt = $pdo->prepare('SELECT bookings.*, sessions.day, sessions.time, sessions.hall_id, seats.roww, seats.number, bookings.random_code, movies.title 
            FROM bookings 
            JOIN sessions ON bookings.session_id = sessions.id 
            JOIN seats ON bookings.seat_id = seats.id 
            JOIN movies ON sessions.movie_id = movies.id 
            WHERE bookings.email = ? 
              AND bookings.booking_time >= NOW() - INTERVAL 2 MINUTE');

            $stmt->execute([$email]);
            return $stmt->fetchAll();
        }

        if (!isset($_SESSION['loggedin'])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $bookings = getBookingsForUser($email);

               
                if (!is_dir('qrcodes')) {
                    mkdir('qrcodes', 0777, true);
                }

                $mail = new PHPMailer(true);

                try {
                   
                    $mail->isSMTP();
                    $mail->Host = 'ssl://smtp.mail.ru'; 
                    $mail->SMTPAuth = true;
                    $mail->Username = 'kino.khaus@mail.ru'; 
                    $mail->Password = 'vEeQHHTDG7X6SG5em9fk'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 465;

                    
                    $mail->setFrom('kino.khaus@mail.ru', 'Кинохаус');
                    $mail->addAddress($email);
                     $mail->CharSet = 'UTF-8';
                   
                    $mail->isHTML(true);
                    $mail->Subject = 'Ваши билеты на сеанс';

                    
                    $bodyContent = "<div style='font-family: Arial, sans-serif;'>";
                    
                    foreach ($bookings as $booking) {
                        $hallId = $booking['hall_id'];
                        $type = '';
                        $price = 0;

                        if ($hallId >= 1 && $hallId <= 4) {
                            $type = 'Стандарт';
                            $price = 290;
                        } elseif ($hallId == 5 || $hallId == 6) {
                            $type = 'VIP';
                            $price = 590;
                        } elseif ($hallId == 7 || $hallId == 8) {
                            $type = '4DX';
                            $price = 490;
                        }

                        
                        $dayFormatted = DateTime::createFromFormat('Y-m-d', $booking['day'])->format('d.m.Y');
                        $timeFormatted = DateTime::createFromFormat('H:i:s', $booking['time'])->format('H:i');

                        $bodyContent .= "<h1 style='color: #4CAF50;'>{$booking['title']}</h1>";
                        $bodyContent .= "<p style='font-size: 18px;'>{$dayFormatted} {$timeFormatted}</p>";
                        $bodyContent .= "<p style='color: red'>Перейдите по ссылке для оплаты билетов. Через 1 час бронь пропадет.</p>";
                        $bodyContent .= "<p><a href='https://localhost/yookassa_payment3.php?email={$booking['email']}'>Оплатить билеты</a></p>";
                        $bodyContent .= "<p>Кинотеатр «Раздан»</p>";
                        $bodyContent .= "<p>г. Ростов-на-Дону, ул. Текучева, 105</p>";
                        $bodyContent .= "<p>Зал: <strong>{$booking['hall_id']} ({$type})</strong></p>";
                        $bodyContent .= "<p>Ряд: <strong>{$booking['roww']}</strong></p>";
                        $bodyContent .= "<p>Место: <strong>{$booking['number']}</strong></p>";
                        $bodyContent .= "<p>Цена: <strong>{$price} руб.</strong></p>";

                        
                        $bodyContent .= "<hr>";
                    }

                    $bodyContent .= "</div>";
                    $mail->Body = $bodyContent;

                    if ($mail->send()) {
                        header("Location: succes_bron.php");
                        exit();
                    } else {
                        echo 'Ошибка при отправке билетов: ' . $mail->ErrorInfo;
                    }
                } catch (Exception $e) {
                    echo "Ошибка при отправке билетов: {$mail->ErrorInfo}";
                }

                
                foreach ($bookings as $booking) {
                    $qrCodePath = 'qrcodes/' . $booking['random_code'] . '.png';
                    if (file_exists($qrCodePath)) {
                        unlink($qrCodePath);
                    }
                }

                exit();
            }
        }
        else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $bookings = getBookingsForUser($email);

                
                if (!is_dir('qrcodes')) {
                    mkdir('qrcodes', 0777, true);
                }

                $mail = new PHPMailer(true);

                try {
                    
                    $mail->isSMTP();
                    $mail->Host = 'ssl://smtp.mail.ru'; 
                    $mail->SMTPAuth = true;
                    $mail->Username = 'kino.khaus@mail.ru'; 
                    $mail->Password = 'vEeQHHTDG7X6SG5em9fk'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 465;

        
                    $mail->setFrom('kino.khaus@mail.ru', 'Кинохаус');
                    $mail->addAddress($email);
                    $mail->CharSet = 'UTF-8';
  
                    $mail->isHTML(true);
                    $mail->Subject = 'Ваши билеты на сеанс';

               
                    $bodyContent = "<div style='font-family: Arial, sans-serif;'>";
                    
                    foreach ($bookings as $booking) {
                        $hallId = $booking['hall_id'];
                        $type = '';
                        $price = 0;

                        if ($hallId >= 1 && $hallId <= 4) {
                            $type = 'Стандарт';
                            $price = 290;
                        } elseif ($hallId == 5 || $hallId == 6) {
                            $type = 'VIP';
                            $price = 590;
                        } elseif ($hallId == 7 || $hallId == 8) {
                            $type = '4DX';
                            $price = 490;
                        }


                        $dayFormatted = DateTime::createFromFormat('Y-m-d', $booking['day'])->format('d.m.Y');
                        $timeFormatted = DateTime::createFromFormat('H:i:s', $booking['time'])->format('H:i');

                        $bodyContent .= "<h1 style='color: #4CAF50;'>{$booking['title']}</h1>";
                        $bodyContent .= "<p style='font-size: 18px;'>{$dayFormatted} {$timeFormatted}</p>";
                        $bodyContent .= "<p style='color: red'>Перейдите по ссылке для оплаты билетов. Через 1 час бронь пропадет.</p>";
                        $bodyContent .= "<p><a href='https://localhost/yookassa_payment3.php?email={$booking['email']}'>Оплатить билеты</a></p>";
                        $bodyContent .= "<p>Кинотеатр «Раздан»</p>";
                        $bodyContent .= "<p>г. Ростов-на-Дону, ул. Текучева, 105</p>";
                        $bodyContent .= "<p>Код бронирования: <strong>{$booking['random_code']}</strong></p>";
                        $bodyContent .= "<p>Зал: <strong>{$booking['hall_id']} ({$type})</strong></p>";
                        $bodyContent .= "<p>Ряд: <strong>{$booking['roww']}</strong></p>";
                        $bodyContent .= "<p>Место: <strong>{$booking['number']}</strong></p>";
                        $bodyContent .= "<p>Цена: <strong>{$price} руб.</strong></p>";

                        $bodyContent .= "<hr>";
                    }

                    $bodyContent .= "</div>";
                    $mail->Body = $bodyContent;

                    if ($mail->send()) {
                        header("Location: profile.php");
                        exit();
                    } else {
                        echo 'Ошибка при отправке билетов: ' . $mail->ErrorInfo;
                    }
                } catch (Exception $e) {
                    echo "Ошибка при отправке билетов: {$mail->ErrorInfo}";
                }

        
                foreach ($bookings as $booking) {
                    $qrCodePath = 'qrcodes/' . $booking['random_code'] . '.png';
                    if (file_exists($qrCodePath)) {
                        unlink($qrCodePath);
                    }
                }

                exit();
            }
        }
    } else {
        echo 'Сеанс не найден.';
    }
}
?>
