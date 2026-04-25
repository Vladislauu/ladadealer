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
                <div class="popular__card">
                    <img src="media/img6.png" alt="Семейство Granta" class="image--scaling">
                </div>
                <div class="popular__card">
                    <img src="media/img1.png" alt="Семейство 2107" class="image--scaling">
                </div>
                <div class="popular__card">
                    <img src="media/img3.png" alt="Семейство 2105" class="image--scaling">
                </div>
                <div class="popular__card">
                    <img src="media/img5.png" alt="Семейство Granta" class="image--scaling">
                </div>
                <div class="popular__card">
                    <img src="media/img2.png" alt="Семейство 2107" class="image--scaling">
                </div>
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