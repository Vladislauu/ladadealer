<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="stylesheet" href="styles/pages/profile_page.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LADA: личный кабинет</title>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="account">
        
    </section>
    <section class="car__maintence">
        <span>Ближайшие записи на техническое обслуживание</span>
        <ul class="maintence">
            <li class="record">
                <span>01.02.2026 17:30</span>
                <span>LADA Kalina Y815BX57</span>
                <span>ул. Пушкина д.18</span>
                <span>ТО</span>
            </li>
            <li class="record">
                <span>01.02.2026 17:30</span>
                <span>LADA Kalina Y815BX57</span>
                <span>ул. Пушкина д.18</span>
                <span>ТО</span>
            </li>
            <li class="record">
                <span>01.02.2026 17:30</span>
                <span>LADA Kalina Y815BX57</span>
                <span>ул. Пушкина д.18</span>
                <span>ТО</span>
            </li>
            <li class="record">
                <span>01.02.2026 17:30</span>
                <span>LADA Kalina Y815BX57</span>
                <span>ул. Пушкина д.18</span>
                <span>ТО</span>
            </li>
        </ul>
    </section>
    <section class="cars">
        <span>Ваши автомобили</span>
        <ul class="cars__list">
            <li class="cars__card">
                <div><span>LADA Signet 1987</span><img src="media/img1.png" class="image--scaling"></div>
                <div><span>Пробег:198.000км (09.02.2026)</span>
                    <span>ТО21: 210.000км</span>
                    <span>Оценочная стоимость: -</span>
                </div>
                <div>
                    <button class="button--red">Запись на ТО</button>
                    <button class="button--red">Удалить</button>
                </div>
            </li>
        </ul>
    </section>
    <?php include 'footer.php'; ?>
    <script src="styles/components/navigation/burger.js"></script>
    <script src="styles/components/auth/auth.js"></script>
</body>
</html>