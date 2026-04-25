<?php
session_start(); // начинаем сессию, чтобы запомнить пользователя

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $password = $_POST['pass'];
    $logMessage = date('Y-m-d H:i:s') . " - Телефон: $phone, Пароль: $password\n";
    file_put_contents('debug.log', $logMessage, FILE_APPEND);
    // ВАЖНО: в реальном проекте нужно искать пользователя в БД и проверять хеш пароля.
    // Для примера – простая проверка:
    if ($phone === '88005553535' && $password === 'HellOOO123$') {
        $_SESSION['user_phone'] = $phone;
        $_SESSION['auth'] = true;
        header('Location: ../profile.php'); // перенаправляем в личный кабинет
        exit;
    } else {
        // Неверные данные – возвращаем на главную с ошибкой
        header('Location: ../index.php?error=login_failed');
        exit;
    }
} else {
    // Если кто-то зашёл напрямую – отправляем на главную
    header('Location: ../index.php');
    exit;
}
?>