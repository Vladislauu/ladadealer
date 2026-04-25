<?php
session_start();

// Проверяем, что форма отправлена методом POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы (имена полей должны совпадать с name в форме)
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['pass'] ?? '';
    
    // Отладка: записываем в лог
    $logMessage = date('Y-m-d H:i:s') . " - Телефон: $phone, Пароль: $password\n";
    file_put_contents('debug.log', $logMessage, FILE_APPEND);
    
    // Простая проверка (временная)
    if ($phone === '88005553535' && $password === 'HellOOO123$') {
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