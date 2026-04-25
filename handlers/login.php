<?php
session_start();

// Если пользователь уже авторизован - отправляем в личный кабинет
if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) {
    header('Location: ../profile.php');
    exit;
}

// Проверяем, что форма отправлена методом POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Получаем данные из формы (имена полей должны совпадать с name в форме)
    $phone = trim($_POST['login_phone'] ?? '');
    $password = $_POST['login_password'] ?? '';
    
    // Отладка: записываем в лог
    $logMessage = date('Y-m-d H:i:s') . " - Телефон: $phone, Пароль: $password\n";
    file_put_contents('debug.log', $logMessage, FILE_APPEND);
    
    // Простая проверка (временная)
    if ($phone === '+7 (800) 555-35-35' && $password === 'HellOOO123$') {
        $_SESSION['user_phone'] = $phone;
        $_SESSION['auth'] = true;
        header('Location: ../profile.php');
        exit;
    } else {
        // Неверные данные
        header('Location: ../index.php?error=login_failed');
        exit;
    }
    
} else {
    // Если кто-то зашёл напрямую, а не через POST
    header('Location: ../index.php');
    exit;
}
?>