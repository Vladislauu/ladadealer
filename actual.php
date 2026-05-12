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

// ---------- Получаем все модели для чекбоксов (динамически) ----------
$modelsList = [];
$resultModels = $mysqli->query("SELECT `Наименование модели` FROM `Модель` ORDER BY `Наименование модели`");
if ($resultModels) {
    while ($row = $resultModels->fetch_assoc()) {
        $modelsList[] = $row['Наименование модели'];
    }
    $resultModels->free();
}

// ---------- Обработка фильтров из GET ----------
$selectedModels = isset($_GET['models']) ? (array)$_GET['models'] : [];
$priceFrom = isset($_GET['price_from']) && is_numeric($_GET['price_from']) ? (int)$_GET['price_from'] : null;
$priceTo = isset($_GET['price_to']) && is_numeric($_GET['price_to']) ? (int)$_GET['price_to'] : null;

// Формируем WHERE условия
$whereConditions = [];
$params = [];
$types = "";

// Фильтр по модели (названия моделей)
if (!empty($selectedModels)) {
    $placeholders = implode(',', array_fill(0, count($selectedModels), '?'));
    $whereConditions[] = "m.`Наименование модели` IN ($placeholders)";
    foreach ($selectedModels as $modelName) {
        $params[] = $modelName;
        $types .= "s";
    }
}

// Фильтр по цене "от"
if ($priceFrom !== null) {
    $whereConditions[] = "av.`Актуальная стоимость` >= ?";
    $params[] = $priceFrom;
    $types .= "i";
}

// Фильтр по цене "до"
if ($priceTo !== null) {
    $whereConditions[] = "av.`Актуальная стоимость` <= ?";
    $params[] = $priceTo;
    $types .= "i";
}

// Базовый SQL-запрос
$sql = "
    SELECT 
        av.`ID автомобиля`,
        av.`Актуальная стоимость`,
        m.`Наименование модели`,
        k.`Наименование комплектации`,
        cm.`Директория с изображениями`
    FROM `Автомобиль в наличии` av
    JOIN `Комплектация` k ON av.`ID комплектации` = k.`ID комплектации`
    JOIN `Модель` m ON k.`ID модели` = m.`ID модели`
    JOIN `Цвет модели` cm ON av.`ID цвет модели` = cm.`ID цвет модели`
";

if (!empty($whereConditions)) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

$sql .= " ORDER BY av.`Актуальная стоимость`";

// Выполняем запрос с параметрами
$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$cars = [];
while ($row = $result->fetch_assoc()) {
    $cars[] = $row;
}
$stmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="stylesheet" href="styles/pages/actual_page.css">
    <link rel="stylesheet" href="styles/components/order/order.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LADA: в наличии</title>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="offers">
        <!-- Форма фильтров с GET-параметрами -->
        <form class="offers__filters" method="GET" action="">
            <div class="filter__city">
                <div class="text--gray">Город</div>
                <select id="city" name="city" disabled>
                    <option value="">-</option>
                    <option value="msk">Москва</option>
                    <option value="spb">Санкт-Петербург</option>
                    <option value="ekb">Екатеринбург</option>
                </select>
                <div style="font-size: 12px; color: gray;">(фильтр временно недоступен)</div>
            </div>
            <div class="filter__cost">
                <div class="text--gray">Цена</div>
                <input class="input--text" id="cost--from" name="price_from" type="tel" placeholder="От:" value="<?= htmlspecialchars($priceFrom ?? '') ?>">
                <input class="input--text" id="cost--to" name="price_to" type="tel" placeholder="До:" value="<?= htmlspecialchars($priceTo ?? '') ?>">
            </div>
            <div class="filter__model">
                <div class="text--gray">Модель</div>
                <?php foreach ($modelsList as $model): ?>
                <div class="checker">
                    <div class="text--gray text--light">
                        <?= htmlspecialchars($model) ?>
                    </div>
                    <input class="input--checkbox" type="checkbox" name="models[]" value="<?= htmlspecialchars($model) ?>" <?= in_array($model, $selectedModels) ? 'checked' : '' ?>>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="filter__submit">
                <button type="submit" class="button--red">Применить</button>
                <a href="actual.php" class="button--gray">Сбросить</a>
            </div>
        </form>

        <div class="offers__cards">
            <?php if (empty($cars)): ?>
                <div class="offer-card" style="text-align: center; width: 100%;">
                    <div class="text--gray">Автомобилей в наличии не найдено.</div>
                    <div class="text--gray text--light">Попробуйте изменить параметры фильтра.</div>
                </div>
            <?php else: ?>
                <?php foreach ($cars as $car): 
                    $modelName = htmlspecialchars($car['Наименование модели']);
                    $complectation = htmlspecialchars($car['Наименование комплектации']);
                    $price = number_format($car['Актуальная стоимость'], 0, ',', ' ');
                    $image = !empty($car['Директория с изображениями']) ? htmlspecialchars($car['Директория с изображениями']) : 'media/default_car.png';
                ?>
                <div class="offer-card">
                    <div class="text--gray">
                        LADA <?= $modelName ?>
                    </div>
                    <div class="text--gray text--light">
                        <?= $complectation ?>
                    </div>
                    <div class="offer-card__img">
                        <img src="<?= $image ?>" alt="<?= $modelName ?> <?= $complectation ?>" class="image--scaling">
                    </div>
                    <div class="text--gray text--light">
                        <?= $price ?> ₽
                    </div>
                    <div class="text--gray text--light">
                        <!-- В БД нет привязки к городу, выводим заглушку -->
                        В наличии
                    </div>
                    <div class="offer-card__details">
                        <button class="button--red order-button" data-car-id="<?= $car['ID автомобиля'] ?>">Заказать</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
    <?php include 'footer.php'; ?>
    
    <!-- Скрипт для обработки заказа (модальное окно) -->
    <script>
        // Обработчик кнопок "Заказать" – открывает модальное окно с запросом телефона
        const orderButtons = document.querySelectorAll('.order-button');
        orderButtons.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const carId = btn.getAttribute('data-car-id');
                let getphone = await fetch('handlers/checkAuth.php', {method : 'POST'})
                let phone = await getphone.json();
                if (!phone.phone) 
                {
                    phone = prompt('Введите ваш контактный номер телефона (только цифры):', '');
                    if (!phone || !/^\d+$/.test(phone)) 
                    {
                        alert('Необходимо ввести корректный номер телефона (только цифры).');
                        return;
                    }
                }
                // Отправка заявки на сервер
                const formData = new FormData();
                formData.append('car_id', carId);
                formData.append('phone', phone);
                try {
                    const response = await fetch('handlers/submit_car_order.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success) {
                        alert('Заявка оформлена! Менеджер свяжется с вами.');
                    } else {
                        alert('Ошибка: ' + (result.error || 'Неизвестная ошибка'));
                    }
                } catch (err) {
                    alert('Ошибка сети: ' + err.message);
                }
            });
        });
    </script>
</body>
</html>