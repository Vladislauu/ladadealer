<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['reg_phone']);
    $lastname = trim($_POST['reg_lastname']);
    $name = trim($_POST['reg_name']);
    $father = trim($_POST['reg_father']);
    $password = $_POST['reg_password'];
    $password_repeat = $_POST['reg_password_repeat'];
    $city = $_POST['reg_city'];

    // Валидация на сервере
    $errors = [];
    if (empty($phone)) $errors[] = 'Телефон обязателен';
    if (empty($name)) $errors[] = 'Имя обязательно';
    if ($password !== $password_repeat) $errors[] = 'Пароли не совпадают';
    if (strlen($password) < 4) $errors[] = 'Пароль должен быть минимум 4 символа';

    if (empty($errors)) {
        // В реальном проекте здесь вставка в БД с хешированием пароля.
        // Пароль хешируем функцией password_hash()
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Пример "регистрации" – просто сохраняем в сессию (это не надёжно)
        $_SESSION['user_phone'] = $phone;
        $_SESSION['user_name'] = $name;
        $_SESSION['auth'] = true;

        // Перенаправляем в личный кабинет
        header('Location: profile.php');
        exit;
    } else {
        // Если есть ошибки – возвращаем на страницу с сообщением
        $errorString = implode(',', $errors);
        header("Location: index.php?error=$errorString");
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>