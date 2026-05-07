<?php
session_start();

// Удаляем все переменные сессии
$_SESSION = [];

// Если используется cookie сессии, удаляем и её
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Уничтожаем сессию на сервере
session_destroy();

// Перенаправляем на страницу входа или на главную
header('Location: ../index.php');
exit;
?>