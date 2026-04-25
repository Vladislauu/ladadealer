<?php
session_start(); // начинаем сессию, чтобы запомнить пользователя

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['login_phone']);
    $password = $_POST['login_password'];

    // ВАЖНО: в реальном проекте нужно искать пользователя в БД и проверять хеш пароля.
    // Для примера – простая проверка:
    if ($phone === '+7 (123) 456-78-90' && $password === '12345') {
        $_SESSION['user_phone'] = $phone;
        $_SESSION['auth'] = true;
        header('Location: profile.php'); // перенаправляем в личный кабинет
        exit;
    } else {
        // Неверные данные – возвращаем на главную с ошибкой
        header('Location: index.php?error=login_failed');
        exit;
    }
} else {
    // Если кто-то зашёл напрямую – отправляем на главную
    header('Location: index.php');
    exit;
}
?>