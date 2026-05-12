<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '21074';
$db_name = 'ladadealer';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die('Ошибка подключения к БД: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8');

$sql = "
    SELECT 
        m.`ID модели`,
        m.`Наименование модели`,
        m.`Описание`,  -- если поля нет, удалите эту строку и замените на заглушку
        COALESCE(
            (SELECT MIN(`Базовая стоимость`) FROM `Комплектация` WHERE `ID модели` = m.`ID модели`),
            0
        ) AS `min_price`,
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
    <link rel="stylesheet" href="styles/pages/models_page.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LADA: модельный ряд</title>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="models">
        <?php if (empty($models)): ?>
            <div class="models__card">Нет доступных моделей</div>
        <?php else: ?>
            <?php foreach ($models as $model): 
                $modelName = htmlspecialchars($model['Наименование модели']);
                $description = !empty($model['Описание']) ? htmlspecialchars($model['Описание']) : 'Описание отсутствует';
                $minPrice = (int)$model['min_price'];
                $monthlyPayment = round($minPrice / 100);
                $image = htmlspecialchars($model['image_path']);
            ?>
                <div class="models__card">
                    <div class="text--gray">
                        <?= $modelName ?>
                    </div>
                    <div class="text--gray">
                    </div>
                    <img src="<?= $image ?>" alt="Изображение <?= $modelName ?>" class="image--scaling">
<<<<<<< HEAD
=======
                    <div class="text--gray">
                        <?= $description ?>
                    </div>
>>>>>>> 7a939858b02f10ee5b0fd2aa0eab66814dd203fc
                    <div class="text--gray">
                        от <?= number_format($minPrice, 0, ',', ' ') ?> ₽<br>
                    </div>
                    <div class="models__options">
                        <a href="configurator.php?car=<?= urlencode($modelName) ?>" class="button--red">Конфигуратор</a>
                        <a href="actual.php" class="button--red">Авто в наличии</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <?php include 'footer.php'; ?>
</body>
</html>