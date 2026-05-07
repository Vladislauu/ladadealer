<?php
session_start();

// Параметры подключения к БД
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '21074';
$db_name = 'ladadealer';

// Подключение к MySQL
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Проверка соединения
if ($mysqli->connect_error) {
    error_log("DB connection failed: " . $mysqli->connect_error);
    header('Location: ../index.php?error=db_error');
    exit;
}

$mysqli->set_charset("utf8");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_phone = trim($_POST['phone'] ?? '');
    $password = $_POST['pass'] ?? '';

    // Логируем попытку входа (без пароля)
    $logMessage = date('Y-m-d H:i:s') . " - Попытка входа для телефона: $raw_phone\n";
    file_put_contents('debug.log', $logMessage, FILE_APPEND);

    // Нормализация номера
    $phone_digits = preg_replace('/\D/', '', $raw_phone);
    if ($phone_digits === '') {
        file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Неверный формат телефона: $raw_phone\n", FILE_APPEND);
        header('Location: ../index.php?error=invalid_phone');
        exit;
    }

    $sql = "SELECT `ID пользователя`, `Номер телефона`, `Пароль`, `Фамилия`, `Имя`, `Отчество`, `Город`, `ID роли` 
            FROM `Пользователь` 
            WHERE `Номер телефона` = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        header('Location: ../index.php?error=internal');
        exit;
    }

    $stmt->bind_param("s", $phone_digits);
    $stmt->execute();
    $result = $stmt->get_result();

    // Логируем результат поиска
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Лог с данными пользователя (без пароля)
        $logResult = date('Y-m-d H:i:s') . " - Пользователь НАЙДЕН: ID={$user['ID пользователя']}, Имя={$user['Имя']} {$user['Фамилия']}, Телефон={$user['Номер телефона']}\n";
        file_put_contents('debug.log', $logResult, FILE_APPEND);
        
        $stored_password = $user['Пароль'];

        if ($password == $stored_password) {
            $_SESSION['user_id'] = $user['ID пользователя'];
            $_SESSION['user_phone'] = $user['Номер телефона'];
            $_SESSION['user_name'] = $user['Имя'] . ' ' . $user['Фамилия'];
            $_SESSION['user_role'] = $user['ID роли'];
            $_SESSION['auth'] = true;
            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - УСПЕШНЫЙ вход для {$user['Имя']} {$user['Фамилия']}\n", FILE_APPEND);
            $stmt->close();
            $mysqli->close();
            header('Location: ../profile.php');
            exit;
        } else {
            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - НЕВЕРНЫЙ пароль для пользователя ID={$user['ID пользователя']} ".$password." ".$stored_password, FILE_APPEND);
        }
    } else {
        // Пользователь не найден
        $logResult = date('Y-m-d H:i:s') . " - Пользователь с телефоном $phone_digits НЕ НАЙДЕН\n";
        file_put_contents('debug.log', $logResult, FILE_APPEND);
    }

    $stmt->close();
    $mysqli->close();
    header('Location: ../index.php?error=login_failed');
    exit;
} else {
    header('Location: ../index.php');
    exit;
}
?>