<?php
header('Content-Type: application/json');
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '21074';
$db_name = 'ladadealer';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Ошибка БД']);
    exit;
}
$mysqli->set_charset('utf8');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carId = (int)$_POST['car_id'];
    $phone = "";
    if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) $phone = $_SESSION['user_phone'];
    else $phone = preg_replace('/\D/', '', $_POST['phone']); // только цифры
    //exit;

    if (!$carId || !$phone) {
        echo json_encode(['success' => false, 'error' => 'Не все данные заполнены']);
        exit;
    }

    $checkStmt = $mysqli->prepare("SELECT 1 FROM `Автомобиль в наличии` WHERE `ID автомобиля` = ?");
    $checkStmt->bind_param('i', $carId);
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->num_rows > 0;
    $checkStmt->close();

    if (!$exists) {
        echo json_encode(['success' => false, 'error' => 'Автомобиль не найден']);
        exit;
    }

    $stmt = $mysqli->prepare("INSERT INTO `Заявка на автомобиль` (`ID автомобиля`, `Контактный номер телефона`, `Дата истечения брони`) VALUES (?, ?, NULL)");
    $stmt->bind_param('is', $carId, $phone);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $mysqli->error]);
    }
    $stmt->close();
    $mysqli->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Неверный метод']);
}
?>