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
    $versionId = (int)$_POST['version_id'];
    $colorId = (int)$_POST['color_id'];
<<<<<<< HEAD
    $phone = "";
    if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) $phone = $_SESSION['user_phone'];
    else $phone = preg_replace('/\D/', '', $_POST['phone']); // только цифры
    //exit;
=======
    $phone = preg_replace('/\D/', '', $_POST['phone']);
>>>>>>> 7a939858b02f10ee5b0fd2aa0eab66814dd203fc

    if (!$versionId || !$colorId || !$phone) {
        echo json_encode(['success' => false, 'error' => 'Не все данные заполнены']);
        exit;
    }

    $statusId = 1;

    $stmt = $mysqli->prepare("INSERT INTO `Заявка конфигуратор` (`ID комплектации`, `ID цвет модели`, `ID статуса`, `Контактный номер телефона`) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iiis', $versionId, $colorId, $statusId, $phone);
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