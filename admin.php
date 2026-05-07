<?php
session_start();

// Проверка авторизации и роли (только роль > 1)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] <= 1) {
    header('Location: index.php');
    exit;
}

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
$mysqli->autocommit(true);

$success_message = '';
$error_message = '';

// --- ОБРАБОТКА POST-ЗАПРОСОВ ---

// 1. Добавление нового автомобиля в наличии
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_car') {
    $complectation_id = (int)$_POST['complectation_id'];
    $color_model_id = (int)$_POST['color_model_id'];
    $actual_price = (int)$_POST['actual_price'];

    // Проверяем базовую стоимость комплектации
    $stmt = $mysqli->prepare("SELECT `Базовая стоимость` FROM `Комплектация` WHERE `ID комплектации` = ?");
    $stmt->bind_param('i', $complectation_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $base_price = $res->fetch_assoc()['Базовая стоимость'] ?? 0;
    $stmt->close();

    if ($actual_price < $base_price) {
        $error_message = "Актуальная стоимость не может быть ниже базовой ({$base_price} ₽).";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO `Автомобиль в наличии` (`Актуальная стоимость`, `ID комплектации`, `ID цвет модели`) VALUES (?, ?, ?)");
        $stmt->bind_param('iii', $actual_price, $complectation_id, $color_model_id);
        if ($stmt->execute()) {
            $success_message = "Автомобиль успешно добавлен в наличие.";
        } else {
            $error_message = "Ошибка добавления: " . $mysqli->error;
        }
        $stmt->close();
    }
}

// 2. Обработка заявок (конфигуратор и автомобиль)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'accept_config_request') {
    $request_id = (int)$_POST['request_id'];
    $reg_number = trim($_POST['reg_number']);
    $mileage = (int)$_POST['mileage'];
    $to_mileage = (int)$_POST['to_mileage']; // пробег последнего ТО

    // Получаем данные заявки
    $stmt = $mysqli->prepare("
        SELECT z.`ID комплектации`, z.`ID цвет модели`, z.`Контактный номер телефона`, k.`ID модели`
        FROM `Заявка конфигуратор` z
        JOIN `Комплектация` k ON z.`ID комплектации` = k.`ID комплектации`
        WHERE z.`ID заявки` = ?
    ");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$req) {
        $error_message = "Заявка не найдена.";
    } else {
        // Ищем пользователя по номеру телефона
        $phone = $req['Контактный номер телефона'];
        $stmt = $mysqli->prepare("SELECT `ID пользователя` FROM `Пользователь` WHERE `Номер телефона` = ?");
        $stmt->bind_param('s', $phone);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            // Добавляем личный автомобиль
            $stmt = $mysqli->prepare("
                INSERT INTO `Личный автомобиль` 
                (`ID пользователя`, `ID модели`, `Регистрационный знак`, `Пробег`, `Пробег последнего ТО`)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('iisii', $user['ID пользователя'], $req['ID модели'], $reg_number, $mileage, $to_mileage);
            if ($stmt->execute()) {
                // Удаляем или меняем статус заявки (удалим для простоты)
                $stmt_del = $mysqli->prepare("DELETE FROM `Заявка конфигуратор` WHERE `ID заявки` = ?");
                $stmt_del->bind_param('i', $request_id);
                $stmt_del->execute();
                $stmt_del->close();
                $success_message = "Заявка принята, автомобиль добавлен пользователю.";
            } else {
                $error_message = "Ошибка добавления автомобиля: " . $mysqli->error;
            }
            $stmt->close();
        } else {
            $error_message = "Пользователь с номером {$phone} не найден. Сначала зарегистрируйте его.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'accept_car_request') {
    $request_id = (int)$_POST['request_id'];
    $reg_number = trim($_POST['reg_number']);
    $mileage = (int)$_POST['mileage'];
    $to_mileage = (int)$_POST['to_mileage'];

    // Получаем данные заявки
    $stmt = $mysqli->prepare("
        SELECT z.`ID автомобиля`, z.`Контактный номер телефона`, a.`ID комплектации`, a.`ID цвет модели`, k.`ID модели`
        FROM `Заявка на автомобиль` z
        JOIN `Автомобиль в наличии` a ON z.`ID автомобиля` = a.`ID автомобиля`
        JOIN `Комплектация` k ON a.`ID комплектации` = k.`ID комплектации`
        WHERE z.`ID заявки` = ?
    ");
    $stmt->bind_param('i', $request_id);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$req) {
        $error_message = "Заявка не найдена.";
    } else {
        $phone = $req['Контактный номер телефона'];
        $offer = $req['ID автомобиля'];
        $stmt = $mysqli->prepare("SELECT `ID пользователя` FROM `Пользователь` WHERE `Номер телефона` = ?");
        $stmt->bind_param('s', $phone);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            $stmt = $mysqli->prepare("
                INSERT INTO `Личный автомобиль` 
                (`ID пользователя`, `ID модели`, `Регистрационный знак`, `Пробег`, `Пробег последнего ТО`)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('iisii', $user['ID пользователя'], $req['ID модели'], $reg_number, $mileage, $to_mileage);
            if ($stmt->execute()) {
                // Удаляем заявку и, возможно, автомобиль из наличия? По умолчанию не удаляем – оставляем статус.
                $stmt_del = $mysqli->prepare("DELETE FROM `Заявка на автомобиль` WHERE `ID заявки` = ?");
                $stmt_del->bind_param('i', $request_id);
                $stmt_del->execute();
                $stmt_del->close();
                
                $stmt_car_del = $mysqli->prepare("DELETE FROM `автомобиль в наличии` WHERE `ID автомобиля` = ?" );
                $stmt_car_del->bind_param('i', $offer);
                $stmt_car_del->execute();
                $stmt_car_del->close();
                $success_message = "Заявка принята, автомобиль добавлен пользователю.";
            } else {
                $error_message = "Ошибка добавления автомобиля: " . $mysqli->error;
            }
            $stmt->close();
        } else {
            $error_message = "Пользователь с номером {$phone} не найден.";
        }
    }
}

// 3. Редактирование записи на сервис
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_service') {
    $record_id = (int)$_POST['record_id'];
    $date_record = $_POST['date_record'] ? date('Y-m-d H:i:s', strtotime($_POST['date_record'])) : null;
    $status_id = (int)$_POST['status_id'];

    $stmt = $mysqli->prepare("UPDATE `Запись на сервис` SET `Дата записи` = ?, `ID статуса` = ? WHERE `ID записи` = ?");
    $stmt->bind_param('sii', $date_record, $status_id, $record_id);
    if ($stmt->execute()) {
        $success_message = "Запись обновлена.";
    } else {
        $error_message = "Ошибка: " . $mysqli->error;
    }
    $stmt->close();
}

// ---- Выборка данных для отображения ----

// 1. Модели для селекта в форме добавления авто
$models = [];
$res = $mysqli->query("SELECT `ID модели`, `Наименование модели` FROM `Модель` ORDER BY `Наименование модели`");
while ($row = $res->fetch_assoc()) $models[] = $row;
$res->free();

// 2. Заявки конфигуратора (статус пока не используется, просто все активные)
$config_requests = [];
$res = $mysqli->query("
    SELECT z.*, k.`Наименование комплектации`, c.`Наименование цвета`, m.`Наименование модели`
    FROM `Заявка конфигуратор` z
    JOIN `Комплектация` k ON z.`ID комплектации` = k.`ID комплектации`
    JOIN `Цвет модели` cm ON z.`ID цвет модели` = cm.`ID цвет модели`
    JOIN `Цвет` c ON cm.`Код цвета` = c.`Код цвета`
    JOIN `Модель` m ON k.`ID модели` = m.`ID модели`
    ORDER BY z.`ID заявки` DESC
");
$config_requests = $res->fetch_all(MYSQLI_ASSOC);
$res->free();

// 3. Заявки на автомобиль
$car_requests = [];
$res = $mysqli->query("
    SELECT z.*, a.`Актуальная стоимость`, k.`Наименование комплектации`, m.`Наименование модели`
    FROM `Заявка на автомобиль` z
    JOIN `Автомобиль в наличии` a ON z.`ID автомобиля` = a.`ID автомобиля`
    JOIN `Комплектация` k ON a.`ID комплектации` = k.`ID комплектации`
    JOIN `Модель` m ON k.`ID модели` = m.`ID модели`
    ORDER BY z.`ID заявки` DESC
");
$car_requests = $res->fetch_all(MYSQLI_ASSOC);
$res->free();

// 4. Записи на сервис (для редактирования)
$service_records = [];
$res = $mysqli->query("
    SELECT z.*, u.`Фамилия`, u.`Имя`, u.`Номер телефона`, l.`Регистрационный знак`, s.`Наименование статуса`
    FROM `Запись на сервис` z
    JOIN `Личный автомобиль` l ON z.`ID ЛТС` = l.`ID ЛТС`
    JOIN `Пользователь` u ON z.`ID пользователя` = u.`ID пользователя`
    LEFT JOIN `Статус` s ON z.`ID статуса` = s.`ID статуса`
    ORDER BY z.`Дата заявки` DESC
");
$service_records = $res->fetch_all(MYSQLI_ASSOC);
$res->free();

// 5. Все статусы для выпадающего списка
$statuses = [];
$res = $mysqli->query("SELECT `ID статуса`, `Наименование статуса` FROM `Статус` ORDER BY `ID статуса`");
while ($row = $res->fetch_assoc()) $statuses[] = $row;
$res->free();

// 6. Комплектации и цвета для AJAX (отдельные скрипты, но для начала загрузим все в JS)
$complectations_by_model = [];
$colors_by_model = [];
$all_complectations = $mysqli->query("SELECT `ID комплектации`, `ID модели`, `Наименование комплектации`, `Базовая стоимость` FROM `Комплектация`");
while ($row = $all_complectations->fetch_assoc()) {
    $complectations_by_model[$row['ID модели']][] = $row;
}
$all_colors = $mysqli->query("SELECT cm.`ID цвет модели`, cm.`ID модели`, c.`Наименование цвета` FROM `Цвет модели` cm JOIN `Цвет` c ON cm.`Код цвета` = c.`Код цвета`");
while ($row = $all_colors->fetch_assoc()) {
    $colors_by_model[$row['ID модели']][] = $row;
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="stylesheet" href="styles/pages/admin.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LADA: Админ-панель</title>
    <style>
        /* Дополнительные стили для админ-панели */
        .admin-section {
            background: #f9f9f9;
            margin: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .admin-section h2 {
            margin-top: 0;
            border-bottom: 2px solid #cc0000;
            display: inline-block;
            padding-bottom: 5px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .admin-table th, .admin-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .admin-table th {
            background-color: #f2f2f2;
        }
        .form-inline {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .form-group {
            margin-bottom: 10px;
        }
        label {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
            color: #333;
        }
        select, input[type="text"], input[type="number"], input[type="datetime-local"] {
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .button--gray {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .message {
            padding: 10px;
            margin: 10px 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .small-input {
            width: 120px;
        }
    </style>
    <link rel="stylesheet" href="styles/pages/main_page/main_page.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <?php if ($success_message): ?>
        <div class="message success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="message error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="admin-section">
        <h2>Добавить автомобиль в наличии</h2>
        <form method="POST" id="addCarForm">
            <input type="hidden" name="action" value="add_car">
            <div class="form-inline">
                <div class="form-group">
                    <label>Модель</label>
                    <select name="model_id" id="model_select" required>
                        <option value="">-- Выберите модель --</option>
                        <?php foreach ($models as $model): ?>
                            <option value="<?= $model['ID модели'] ?>"><?= htmlspecialchars($model['Наименование модели']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Комплектация</label>
                    <select name="complectation_id" id="complectation_select" required disabled>
                        <option value="">Сначала выберите модель</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Цвет</label>
                    <select name="color_model_id" id="color_select" required disabled>
                        <option value="">Сначала выберите модель</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Актуальная стоимость (₽)</label>
                    <input type="number" name="actual_price" required min="0" step="1000">
                </div>
                <div class="form-group">
                    <button type="submit" class="button--red">Добавить</button>
                </div>
            </div>
        </form>
    </div>

    <div class="admin-section">
        <h2>Заявки из конфигуратора</h2>
        <table class="admin-table">
            <thead>
                <tr><th>ID</th><th>Модель</th><th>Комплектация</th><th>Цвет</th><th>Телефон</th><th>Действие</th></tr>
            </thead>
            <tbody>
            <?php foreach ($config_requests as $req): ?>
                <tr>
                    <td><?= $req['ID заявки'] ?></td>
                    <td><?= htmlspecialchars($req['Наименование модели']) ?></td>
                    <td><?= htmlspecialchars($req['Наименование комплектации']) ?></td>
                    <td><?= htmlspecialchars($req['Наименование цвета']) ?></td>
                    <td><?= htmlspecialchars($req['Контактный номер телефона']) ?></td>
                    <td>
                        <form method="POST" style="display:inline-block" onsubmit="return confirm('Добавить автомобиль пользователю? Укажите госномер и пробег.')">
                            <input type="hidden" name="action" value="accept_config_request">
                            <input type="hidden" name="request_id" value="<?= $req['ID заявки'] ?>">
                            <input type="text" name="reg_number" placeholder="Госномер" required size="10">
                            <input type="number" name="mileage" placeholder="Пробег" value="0" required size="5">
                            <input type="number" name="to_mileage" placeholder="ТО км" value="0" required size="5">
                            <button type="submit" class="button--red" style="padding: 4px 8px;">Принять</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-section">
        <h2>Заявки на автомобиль из наличия</h2>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Модель</th><th>Комплектация</th><th>Стоимость</th><th>Телефон</th><th>Действие</th></tr></thead>
            <tbody>
            <?php foreach ($car_requests as $req): ?>
                <tr>
                    <td><?= $req['ID заявки'] ?></td>
                    <td><?= htmlspecialchars($req['Наименование модели']) ?></td>
                    <td><?= htmlspecialchars($req['Наименование комплектации']) ?></td>
                    <td><?= number_format($req['Актуальная стоимость'], 0, ',', ' ') ?> ₽</td>
                    <td><?= htmlspecialchars($req['Контактный номер телефона']) ?></td>
                    <td>
                        <form method="POST" style="display:inline-block" onsubmit="return confirm('Добавить автомобиль пользователю? Укажите госномер и пробег.')">
                            <input type="hidden" name="action" value="accept_car_request">
                            <input type="hidden" name="request_id" value="<?= $req['ID заявки'] ?>">
                            <input type="text" name="reg_number" placeholder="Госномер" required size="10">
                            <input type="number" name="mileage" placeholder="Пробег" value="0" required size="5">
                            <input type="number" name="to_mileage" placeholder="ТО км" value="0" required size="5">
                            <button type="submit" class="button--red" style="padding: 4px 8px;">Принять</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-section">
        <h2>Редактирование записей на сервис</h2>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Пользователь</th><th>Автомобиль</th><th>Описание</th><th>Дата заявки</th><th>Текущий статус</th><th>Установить дату записи</th><th>Новый статус</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($service_records as $rec): ?>
                <tr>
                    <td><?= $rec['ID записи'] ?></td>
                    <td><?= htmlspecialchars($rec['Фамилия'] . ' ' . $rec['Имя']) ?><br><?= $rec['Номер телефона'] ?></td>
                    <td><?= htmlspecialchars($rec['Регистрационный знак']) ?></td>
                    <td><?= htmlspecialchars(mb_substr($rec['Описание'], 0, 50)) ?>…</td>
                    <td><?= date('d.m.Y', strtotime($rec['Дата заявки'])) ?></td>
                    <td><?= htmlspecialchars($rec['Наименование статуса'] ?? 'Нет') ?></td>
                    <form method="POST">
                        <input type="hidden" name="action" value="edit_service">
                        <input type="hidden" name="record_id" value="<?= $rec['ID записи'] ?>">
                        <td><input type="datetime-local" name="date_record" value="<?= $rec['Дата записи'] ? date('Y-m-d\TH:i', strtotime($rec['Дата записи'])) : '' ?>"></td>
                        <td>
                            <select name="status_id">
                                <option value="0">— Без статуса —</option>
                                <?php foreach ($statuses as $st): ?>
                                    <option value="<?= $st['ID статуса'] ?>" <?= ($rec['ID статуса'] == $st['ID статуса']) ? 'selected' : '' ?>><?= htmlspecialchars($st['Наименование статуса']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><button type="submit" class="button--gray">Сохранить</button></td>
                    </form>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Динамическая подгрузка комплектаций и цветов при выборе модели
        const complectations = <?= json_encode($complectations_by_model) ?>;
        const colors = <?= json_encode($colors_by_model) ?>;

        const modelSelect = document.getElementById('model_select');
        const complectationSelect = document.getElementById('complectation_select');
        const colorSelect = document.getElementById('color_select');

        function updateComplectationsAndColors() {
            const modelId = modelSelect.value;
            if (!modelId) {
                complectationSelect.disabled = true;
                complectationSelect.innerHTML = '<option value="">Сначала выберите модель</option>';
                colorSelect.disabled = true;
                colorSelect.innerHTML = '<option value="">Сначала выберите модель</option>';
                return;
            }
            // Обновляем комплектации
            let compOptions = '<option value="">-- Выберите комплектацию --</option>';
            if (complectations[modelId]) {
                complectations[modelId].forEach(comp => {
                    compOptions += `<option value="${comp['ID комплектации']}" data-base-price="${comp['Базовая стоимость']}">${comp['Наименование комплектации']} (от ${comp['Базовая стоимость']} ₽)</option>`;
                });
                complectationSelect.disabled = false;
            } else {
                complectationSelect.disabled = true;
                compOptions = '<option value="">Нет комплектаций</option>';
            }
            complectationSelect.innerHTML = compOptions;

            // Обновляем цвета
            let colorOptions = '<option value="">-- Выберите цвет --</option>';
            if (colors[modelId]) {
                colors[modelId].forEach(clr => {
                    colorOptions += `<option value="${clr['ID цвет модели']}">${clr['Наименование цвета']}</option>`;
                });
                colorSelect.disabled = false;
            } else {
                colorSelect.disabled = true;
                colorOptions = '<option value="">Нет цветов</option>';
            }
            colorSelect.innerHTML = colorOptions;
        }

        modelSelect.addEventListener('change', updateComplectationsAndColors);
        // Дополнительно можно при добавлении автомобиля проверять цену (не ниже базовой)
        document.getElementById('addCarForm').addEventListener('submit', function(e) {
            const selectedComp = complectationSelect.options[complectationSelect.selectedIndex];
            const basePrice = selectedComp.getAttribute('data-base-price');
            const actualPrice = document.querySelector('input[name="actual_price"]').value;
            if (basePrice && parseInt(actualPrice) < parseInt(basePrice)) {
                alert(`Актуальная стоимость не может быть ниже базовой (${basePrice} ₽).`);
                e.preventDefault();
            }
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>