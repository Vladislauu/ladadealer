<?php
session_start();

// Подключение к БД
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '21074';
$db_name = 'ladadealer';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die('Ошибка подключения к БД: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8');

// Получаем все модели и для каждой – первое изображение из таблицы Цвет модели
$sql = "
    SELECT 
        m.`ID модели`,
        m.`Наименование модели`,
        COALESCE(
            (SELECT `Директория с изображениями` 
             FROM `Цвет модели` 
             WHERE `ID модели` = m.`ID модели` 
             LIMIT 1),
            'media/default_car.png'
        ) AS `image_path`
    FROM `Модель` m
    ORDER BY m.`ID модели`
";
$result = $mysqli->query($sql);
$models = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $models[] = $row;
    }
    $result->free();
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="stylesheet" href="styles/pages/main_page/main_page.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LADA</title>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <section class="sales">
        <button class="button--left" id="sales__switch--left">&#10094;</button>
        <div class="sales__cards" id="sales__cards">
            <div id="lada2105" class="sales__card">
                <img src="media/render.png" class="image--fullfill">
                <div class="text--title">LADA 2105 от 120 000 РУБ.</div>
            </div>
            <div id="lada2105" class="sales__card">
                <img src="media/render2.png" class="image--fullfill">
                <div class="text--title">LADA 2105 от 120 000 РУБ.</div>
            </div>
            <div id="ladaRiva" class="sales__card">
                <img src="media/render3.png" class="image--fullfill">
                <div class="text--title">НОВАЯ GRANTA 1.6 MT от 215.000 РУБ.</div>
            </div>
        </div>
        <button class="button--next" id="sales__switch--right">&#10095;</button>
    </section>
    
    <section class="popular">
        <button class="button--left" id="popular__switch--left">&#10094;</button>
        <div class="popular__track" id="popular__track">
            <?php if (empty($models)): ?>
                <div class="popular__card">
                    <img src="media/default_car.png" alt="Нет моделей" class="image--scaling">
                </div>
            <?php else: ?>
                <?php foreach ($models as $model): 
                    $modelName = htmlspecialchars($model['Наименование модели']);
                    $imagePath = htmlspecialchars($model['image_path']);
                ?>
                    <div class="popular__card">
                        <img src="<?= $imagePath ?>" alt="Семейство <?= $modelName ?>" class="image--scaling">
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="popular__actions">
            <a href="configurator.php" class="button--red">Конфигуратор</a>
            <a href="actual.php" class="button--red">Авто в наличии</a>
        </div>
        <button class="button--next" id="popular__switch--right">&#10095;</button>
    </section>
    
    <section class="service">
        <div class="service__info">
            <div class="text--gray">
                Записывайте свой автомобиль на техническое обслуживание с удобством - в личном кабинете
            </div>
            <a href="profile.php" class="button--red">Войти</a>
        </div>
    </section>
    
    <section class="consultation">
        <div class="text--gray">
            Получите дополнительную информацию по телефону!
        </div>
        <form class="consultation__form" action="handlers/consult.php" method="POST">
            <input id="consultation-phone-form" class="input--text" type="tel" placeholder="+7 (___) ___-__-__">
            <button id="consultation-send-form" class="button--red" type="submit">Отправить заявку</button>
        </form>
    </section>
    
    <?php include 'footer.php'; ?>
    <script src="styles/pages/main_page/popular.js"></script>
    <script src="styles/pages/main_page/sales.js"></script>
    <script src="styles/pages/main_page/PhoneValidation.js"></script>
</body>
</html>