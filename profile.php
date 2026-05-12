<?php
session_start();
$logMessage = "hereProfile";
    file_put_contents('debug.log', $logMessage, FILE_APPEND);
// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
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

$userId = (int)$_SESSION['user_id'];

// --- Обработка POST (запись на ТО или удаление автомобиля) ---
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_service') {
        $lts_id = (int)$_POST['lts_id'];
        $description = trim($_POST['description']);
        $date_record = !empty($_POST['date_record']) ? date('Y-m-d H:i:s', strtotime($_POST['date_record'])) : null;

        // Проверяем, принадлежит ли автомобиль текущему пользователю
        $checkStmt = $mysqli->prepare("SELECT 1 FROM `Личный автомобиль` WHERE `ID ЛТС` = ? AND `ID пользователя` = ?");
        $checkStmt->bind_param('ii', $lts_id, $userId);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->num_rows > 0;
        $checkStmt->close();

        if ($exists && !empty($description)) {
            // ID статуса по умолчанию (1 – «Новая», убедитесь, что он существует в таблице Статус)
            $status_id = 1;
            $date_app = date('Y-m-d');
            $stmt = $mysqli->prepare("INSERT INTO `Запись на сервис` (`ID ЛТС`, `ID пользователя`, `ID статуса`, `Дата заявки`, `Описание`, `Дата записи`) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('iiisss', $lts_id, $userId, $status_id, $date_app, $description, $date_record);
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Заявка на ТО оформлена. Вам перезвонят для уточнения деталей.';
            } else {
                $_SESSION['message'] = 'Ошибка при создании заявки: ' . $mysqli->error;
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = 'Некорректные данные или автомобиль не принадлежит вам.';
        }
        header('Location: profile.php');
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete_car') {
        $lts_id = (int)$_POST['lts_id'];
        // Проверяем владельца
        $checkStmt = $mysqli->prepare("SELECT 1 FROM `Личный автомобиль` WHERE `ID ЛТС` = ? AND `ID пользователя` = ?");
        $checkStmt->bind_param('ii', $lts_id, $userId);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->num_rows > 0;
        $checkStmt->close();

        if ($exists) {
            $stmt = $mysqli->prepare("DELETE FROM `Личный автомобиль` WHERE `ID ЛТС` = ?");
            $stmt->bind_param('i', $lts_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Автомобиль удалён из вашего списка.';
            } else {
                $_SESSION['message'] = 'Ошибка при удалении.';
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = 'Автомобиль не найден или не принадлежит вам.';
        }
        header('Location: profile.php');
        exit;
    }
}

// Выводим сообщение, если оно есть, и очищаем
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// --- 1. Получаем список автомобилей пользователя ---
$sqlCars = "
    SELECT 
        l.`ID ЛТС`,
        l.`Регистрационный знак`,
        l.`Пробег`,
        l.`Пробег последнего ТО`,
        m.`Наименование модели`,
        COALESCE(
            (SELECT `Директория с изображениями` 
             FROM `Цвет модели` 
             WHERE `ID модели` = l.`ID модели` 
             LIMIT 1),
            'media/default_car.png'
        ) AS `Изображение`
    FROM `Личный автомобиль` l
    JOIN `Модель` m ON l.`ID модели` = m.`ID модели`
    WHERE l.`ID пользователя` = ?
";
$stmt = $mysqli->prepare($sqlCars);
$stmt->bind_param('i', $userId);
$stmt->execute();
$resultCars = $stmt->get_result();
$cars = [];
while ($row = $resultCars->fetch_assoc()) {
    $cars[] = $row;
}
$stmt->close();

// --- 2. Получаем ближайшие записи на сервис ---
$sqlRecords = "
    SELECT 
        z.`Дата записи`,
        z.`Описание`,
        z.`ID статуса`,
        s.`Наименование статуса`,
        l.`Регистрационный знак`,
        m.`Наименование модели`
    FROM `Запись на сервис` z
    JOIN `Личный автомобиль` l ON z.`ID ЛТС` = l.`ID ЛТС`
    JOIN `Модель` m ON l.`ID модели` = m.`ID модели`
    LEFT JOIN `Статус` s ON z.`ID статуса` = s.`ID статуса`
    WHERE l.`ID пользователя` = ? AND z.`Дата записи` >= NOW()
    ORDER BY z.`Дата записи` ASC
    LIMIT 5
";
$stmt = $mysqli->prepare($sqlRecords);
$stmt->bind_param('i', $userId);
$stmt->execute();
$resultRecords = $stmt->get_result();
$records = [];
while ($row = $resultRecords->fetch_assoc()) {
    $records[] = $row;
}
$stmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="stylesheet" href="styles/pages/profile_page.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LADA: личный кабинет</title>
    <style>
        /* Простые стили для модального окна */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            max-width: 90%;
            text-align: center;
        }
        .modal-content textarea, .modal-content input, .modal-content select {
            width: 100%;
            margin: 10px 0;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .modal-buttons {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .modal-buttons button {
            flex: 1;
        }
        .message {
            padding: 10px;
            margin: 10px 20px;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form class="account" action="handlers/logout.php" method="POST">
        <button class="button--red" type="submit">Выход</button>
    </form>

    <section class="car__maintence">
        <span>Ближайшие записи на техническое обслуживание</span>
        <ul class="maintence">
            <?php if (empty($records)): ?>
                <li class="record">Нет предстоящих записей</li>
            <?php else: ?>
                <?php foreach ($records as $rec): ?>
                    <li class="record">
                        <span><?= htmlspecialchars(date('d.m.Y H:i', strtotime($rec['Дата записи']))) ?></span>
                        <span>LADA <?= htmlspecialchars($rec['Наименование модели']) ?> <?= htmlspecialchars($rec['Регистрационный знак']) ?></span>
                        <span><?= htmlspecialchars($rec['Описание']) ?></span>
                        <span><?= htmlspecialchars($rec['Наименование статуса'] ?? 'Новый') ?></span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </section>

    <!-- Ваши автомобили -->
    <section class="cars">
        <span>Ваши автомобили</span>
        <ul class="cars__list">
            <?php if (empty($cars)): ?>
                <li class="cars__card">У вас пока нет добавленных автомобилей</li>
            <?php else: ?>
                <?php foreach ($cars as $car): ?>
                    <li class="cars__card" data-lts-id="<?= $car['ID ЛТС'] ?>">
                        <div>
                            <span>LADA <?= htmlspecialchars($car['Наименование модели']) ?> <?= htmlspecialchars($car['Регистрационный знак']) ?></span>
                            <img src="<?= htmlspecialchars($car['Изображение']) ?>" class="image--scaling" alt="Изображение автомобиля">
                        </div>
                        <div>
                            <span>Пробег: <?= number_format($car['Пробег'], 0, ',', ' ') ?> км</span>
                            <span>ТО: <?= number_format($car['Пробег последнего ТО'], 0, ',', ' ') ?> км</span>
                        </div>
                        <div>
                            <button class="button--red btn-service" data-lts-id="<?= $car['ID ЛТС'] ?>">Запись на ТО</button>
                            <button class="button--red btn-delete" data-lts-id="<?= $car['ID ЛТС'] ?>">Удалить</button>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </section>

    <!-- Модальное окно для записи на ТО -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <h3>Запись на техническое обслуживание</h3>
            <form method="POST" id="serviceForm">
                <input type="hidden" name="action" value="add_service">
                <input type="hidden" name="lts_id" id="modal_lts_id" value="">
                <label>Описание работ / проблемы:</label>
                <textarea name="description" rows="3" required placeholder="Например: замена масла, диагностика двигателя..."></textarea>
                <label>Желаемая дата записи (необязательно):</label>
                <input type="datetime-local" name="date_record" id="date_record">
                <div class="modal-buttons">
                    <button type="button" class="button--gray" id="modalCancel">Отмена</button>
                    <button type="submit" class="button--red">Отправить заявку</button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Модальное окно
        const modal = document.getElementById('serviceModal');
        const modalLtsId = document.getElementById('modal_lts_id');
        const serviceForm = document.getElementById('serviceForm');
        const cancelBtn = document.getElementById('modalCancel');

        // Обработчики кнопок "Запись на ТО"
        document.querySelectorAll('.btn-service').forEach(btn => {
            btn.addEventListener('click', () => {
                const ltsId = btn.getAttribute('data-lts-id');
                modalLtsId.value = ltsId;
                modal.style.display = 'flex';
            });
        });

        // Закрыть модальное окно
        function closeModal() {
            modal.style.display = 'none';
            serviceForm.reset();
        }
        cancelBtn.addEventListener('click', closeModal);
        window.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        // Обработчики кнопок "Удалить" с подтверждением
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (confirm('Вы уверены, что хотите удалить этот автомобиль из списка?')) {
                    const ltsId = btn.getAttribute('data-lts-id');
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="delete_car">
                        <input type="hidden" name="lts_id" value="${ltsId}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>