<?php
session_start();

// Параметры подключения к БД
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '21074';
$db_name = 'ladadealer';

// Подключение к MySQL
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die('Ошибка подключения к БД: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $lastname = trim($_POST['lastname']);
    $name = trim($_POST['name']);
    $father = trim($_POST['father']);
    $password = $_POST['password'];
    $password_repeat = $_POST['password-repeat'];
    $city = trim($_POST['city']);

    file_put_contents('debug.log', $phone, FILE_APPEND);
    file_put_contents('debug.log', $lastname, FILE_APPEND);
    file_put_contents('debug.log', $father, FILE_APPEND);
    file_put_contents('debug.log', $password, FILE_APPEND);
    file_put_contents('debug.log', $password_repeat, FILE_APPEND);
    file_put_contents('debug.log', $city, FILE_APPEND);
    file_put_contents('debug.log', $name, FILE_APPEND);

    // Валидация полей
    $errors = [];
    if (empty($phone)) $errors[] = 'Телефон обязателен';
    if (empty($lastname)) $errors[] = 'Фамилия обязательна';
    if (empty($name)) $errors[] = 'Имя обязательно';
    if (empty($city)) $errors[] = 'Город обязателен';
    if ($password !== $password_repeat) $errors[] = 'Пароли не совпадают';
    if (strlen($password) < 4) $errors[] = 'Пароль должен содержать минимум 4 символа';
    if (!ctype_digit($phone)) $errors[] = 'Телефон должен состоять только из цифр';

    // Если ошибок нет – пробуем вставить в БД
    if (empty($errors)) {

        // Если отчество пустое – передаём NULL в БД
        $fatherDb = empty($father) ? null : $father;

        // Подготовленный запрос (ID пользователя генерируется автоматически при AUTO_INCREMENT)
        $sql = "INSERT INTO `Пользователь` (`Номер телефона`, `Пароль`, `Фамилия`, `Имя`, `Отчество`, `Город`, `ID роли`)
                VALUES (?, ?, ?, ?, ?, ?, 1)";

        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('isssss', $phone, $password, $lastname, $name, $fatherDb, $city);
            if ($stmt->execute()) {
                // Успешная регистрация
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_phone'] = $phone;
                $_SESSION['user_name'] = $name;
                $_SESSION['auth'] = true;
                $_SESSION['user_role'] = 1;

                $stmt->close();
                $mysqli->close();
                header('Location: ../profile.php');
                exit;
            } else {
                // Ошибка выполнения запроса – проверяем код дубликата (1062)
                if ($mysqli->errno === 1062) {
                    $errors[] = 'Номер телефона уже зарегистрирован';
                } else {
                    $errors[] = 'Ошибка базы данных: ' . $mysqli->error;
                }
            }
            $stmt->close();
        } else {
            $errors[] = 'Ошибка подготовки запроса: ' . $mysqli->error;
        }
    }

    // Если есть ошибки – возвращаем на страницу регистрации
    if (!empty($errors)) {
        $errorString = implode(',', $errors);
        header("Location: ../index.php?error=" . urlencode($errorString));
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}
?>