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

// Получаем выбранную модель из GET-параметра 'car'
$carName = isset($_GET['car']) ? trim($_GET['car']) : '';
$selectedModelId = null;
$modelInfo = null;
$complectations = [];
$colors = [];

if (!empty($carName)) {
    // Ищем модель по наименованию
    $stmt = $mysqli->prepare("SELECT `ID модели`, `Наименование модели` FROM `Модель` WHERE `Наименование модели` = ?");
    $stmt->bind_param('s', $carName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $selectedModelId = $row['ID модели'];
        $modelInfo = $row;
    }
    $stmt->close();

    if ($selectedModelId) {
        // --- Получаем комплектации для модели ---
        $stmt = $mysqli->prepare("SELECT `ID комплектации`, `Наименование комплектации`, `Базовая стоимость` FROM `Комплектация` WHERE `ID модели` = ? ORDER BY `Базовая стоимость`");
        $stmt->bind_param('i', $selectedModelId);
        $stmt->execute();
        $compResult = $stmt->get_result();
        while ($row = $compResult->fetch_assoc()) {
            $complectations[] = $row;
        }
        $stmt->close();

        // --- Получаем цвета для модели через таблицу Цвет модели + Цвет ---
        $stmt = $mysqli->prepare("
            SELECT 
                cm.`ID цвет модели`,
                c.`Наименование цвета`,
                cm.`Директория с изображениями`
            FROM `Цвет модели` cm
            JOIN `Цвет` c ON cm.`Код цвета` = c.`Код цвета`
            WHERE cm.`ID модели` = ?
        ");
        $stmt->bind_param('i', $selectedModelId);
        $stmt->execute();
        $colorResult = $stmt->get_result();
        while ($row = $colorResult->fetch_assoc()) {
            $colors[] = $row;
        }
        $stmt->close();
    }
}

// Определяем выбранную комплектацию (из GET) или берём первую
$selectedVersionId = isset($_GET['version']) ? (int)$_GET['version'] : 0;
$selectedComplectation = null;
if ($selectedVersionId && !empty($complectations)) {
    foreach ($complectations as $comp) {
        if ($comp['ID комплектации'] == $selectedVersionId) {
            $selectedComplectation = $comp;
            break;
        }
    }
}
if (!$selectedComplectation && !empty($complectations)) {
    $selectedComplectation = $complectations[0];
    $selectedVersionId = $selectedComplectation['ID комплектации'];
}

// Выбранный цвет (первый доступный)
$selectedColor = !empty($colors) ? $colors[0] : null;
$selectedColorId = $selectedColor ? $selectedColor['ID цвет модели'] : 0;
$currentImage = $selectedColor ? $selectedColor['Директория с изображениями'] : 'media/default_car.png';

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="stylesheet" href="styles/pages/configurator/configurator.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LADA: конфигуратор</title>
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="configurator">
        <div class="configurator__preview">
            <img src="<?= htmlspecialchars($currentImage) ?>" alt="Автомобиль" class="image--scaling" id="car-image">
        </div>

        <div class="configurator__colors" id="color-list">
            <?php foreach ($colors as $color): ?>
                <a class="color-pick" 
                   data-color-id="<?= $color['ID цвет модели'] ?>" 
                   data-image="<?= htmlspecialchars($color['Директория с изображениями']) ?>"
                   title="<?= htmlspecialchars($color['Наименование цвета']) ?>"
                   style="display: inline-block; width: 40px; height: 40px; background-color: <?= getColorCode($color['Наименование цвета']) ?>; border-radius: 50%; margin: 5px; cursor: pointer;">
                </a>
            <?php endforeach; ?>
        </div>

        <div class="configurator__options">
            <div class="selection-panel">
                <div class="text--gray">Семейство</div>
                <select id="configurator-car-family" name="Семейство">
                    <option value="">-</option>
                    <?php
                    // Для выбора другого семейства можно подгрузить все модели (опционально)
                    $allModels = [];
                    $tempConn = new mysqli($db_host, $db_user, $db_pass, $db_name);
                    $tempConn->set_charset('utf8');
                    $res = $tempConn->query("SELECT `Наименование модели` FROM `Модель` ORDER BY `Наименование модели`");
                    while ($row = $res->fetch_assoc()) {
                        $allModels[] = $row['Наименование модели'];
                    }
                    $tempConn->close();
                    foreach ($allModels as $modelName):
                        $selected = ($modelName == $carName) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($modelName) ?>" <?= $selected ?>><?= htmlspecialchars($modelName) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="text--gray text--light" id="model-description">
                    <?= $modelInfo ? "Модель {$modelInfo['Наименование модели']}" : 'Выберите семейство' ?>
                </div>
            </div>

            <div class="selection-panel">
                <div class="text--gray">Комплектация</div>
                <select id="configurator-car-version" name="Комплектация">
                    <option value="">-</option>
                    <?php foreach ($complectations as $comp): ?>
                        <option value="<?= $comp['ID комплектации'] ?>" 
                                data-price="<?= $comp['Базовая стоимость'] ?>"
                                <?= ($selectedVersionId == $comp['ID комплектации']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($comp['Наименование комплектации']) ?> (<?= number_format($comp['Базовая стоимость'], 0, ',', ' ') ?> ₽)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="text--gray text--light" id="version-description">
                    <?= $selectedComplectation ? htmlspecialchars($selectedComplectation['Наименование комплектации']) : 'Выберите комплектацию' ?>
                </div>
            </div>

            <div class="selection-panel">
                <div class="text--gray">Итог</div>
                <div class="text--gray" id="total-price">
                    <?= $selectedComplectation ? number_format($selectedComplectation['Базовая стоимость'], 0, ',', ' ') . ' ₽' : '0 ₽' ?>
                </div>
                <button class="button--red" id="submit-order">Оставить заявку</button>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        // Получаем элементы
        const familySelect = document.getElementById('configurator-car-family');
        const versionSelect = document.getElementById('configurator-car-version');
        const totalPriceSpan = document.getElementById('total-price');
        const carImage = document.getElementById('car-image');
        const colorItems = document.querySelectorAll('.color-pick');
        const submitBtn = document.getElementById('submit-order');

        // Функция обновления цены при смене комплектации
        function updatePrice() {
            const selectedOption = versionSelect.options[versionSelect.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            if (price) {
                totalPriceSpan.textContent = Number(price).toLocaleString('ru-RU') + ' ₽';
            } else {
                totalPriceSpan.textContent = '0 ₽';
            }
        }

        // Смена комплектации
        versionSelect.addEventListener('change', function() {
            updatePrice();
            // Можно также обновить параметр URL без перезагрузки (history API)
            const url = new URL(window.location.href);
            url.searchParams.set('version', this.value);
            window.history.pushState({}, '', url);
        });

        // Смена цвета
        colorItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const imageUrl = this.getAttribute('data-image');
                if (imageUrl) {
                    carImage.src = imageUrl;
                }
                // Активный класс (можно добавить выделение)
                colorItems.forEach(c => c.classList.remove('active'));
                this.classList.add('active');

                // Обновить URL параметр цвета (необязательно)
                const colorId = this.getAttribute('data-color-id');
                const url = new URL(window.location.href);
                if (colorId) url.searchParams.set('color', colorId);
                window.history.pushState({}, '', url);
            });
        });

        // Смена семейства - перезагрузка страницы с новым параметром car
        familySelect.addEventListener('change', function() {
            const selectedFamily = this.value;
            if (selectedFamily) {
                window.location.href = 'configurator.php?car=' + encodeURIComponent(selectedFamily);
            }
        });

        // Отправка заявки
        submitBtn.addEventListener('click', async function() {
            const selectedVersion = versionSelect.value;
            const selectedColorElem = document.querySelector('.color-pick.active');
            const colorId = selectedColorElem ? selectedColorElem.getAttribute('data-color-id') : null;

            if (!selectedVersion || !colorId) {
                alert('Пожалуйста, выберите комплектацию и цвет.');
                return;
            }

            const phone = prompt('Введите ваш контактный номер телефона (только цифры):', '');
            if (!phone || !/^\d+$/.test(phone)) {
                alert('Необходимо ввести корректный номер телефона (только цифры).');
                return;
            }

            // Отправка заявки на сервер (можно через fetch)
            const formData = new FormData();
            formData.append('version_id', selectedVersion);
            formData.append('color_id', colorId);
            formData.append('phone', phone);

            try {
                const response = await fetch('handlers/submit_configurator_order.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alert('Заявка успешно отправлена! Менеджер свяжется с вами.');
                } else {
                    alert('Ошибка: ' + (result.error || 'Неизвестная ошибка'));
                }
            } catch (err) {
                alert('Ошибка сети: ' + err.message);
            }
        });

        // Устанавливаем активный цвет по умолчанию
        if (colorItems.length > 0) {
            colorItems[0].classList.add('active');
        }
        updatePrice();
    </script>
</body>
</html>

<?php
// Вспомогательная функция для получения примерного цвета по названию (для демонстрации)
function getColorCode($colorName) {
    $map = [
        'белый' => '#FFFFFF',
        'черный' => '#000000',
        'красный' => '#FF0000',
        'синий' => '#0000FF',
        'зеленый' => '#00FF00',
        'желтый' => '#FFFF00',
        'серый' => '#808080',
        'серебристый' => '#C0C0C0',
        'коричневый' => '#8B4513',
    ];
    $lower = mb_strtolower($colorName);
    foreach ($map as $key => $code) {
        if (strpos($lower, $key) !== false) {
            return $code;
        }
    }
    return '#CCCCCC'; // цвет по умолчанию
}
?>