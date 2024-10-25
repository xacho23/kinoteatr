<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'];

    $result = deleteBookingById($booking_id);

    if ($result) {
        header('Location: profile.php');
        exit();
    } else {
        echo "Ошибка при отмене бронирования.";
    }
}

function deleteBookingById($booking_id) {
    $query = "DELETE FROM bookings WHERE id=?";
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $result;
}

function getDatabaseConnection() {

    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'kinoteatr';

    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>
