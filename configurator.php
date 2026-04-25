<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="stylesheet" href="styles/pages/configurator/configurator.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LADA: в наличии</title>
</head>
<body>
    <?php include 'header.php'; ?>
    <section class="configurator">
        <div class="configurator__preview">
            <img src="media/img1.png" alt="Логотип" class="image--scaling">
        </div>
        <div class="configurator__colors">
            <a class="color-pick"></a>
            <a class="color-pick"></a>
            <a class="color-pick"></a>
            <a class="color-pick"></a>
            <a class="color-pick"></a>
        </div>
        <div class="configurator__options">
            <div class="selection-panel">
                <div class="text--gray">Семейство</div>
                <select id="configurator-car-family" name="Семейство">
                    <option value="">-</option>
                    <option value="2105">2105</option>
                    <option value="2107">2107</option>
                    <option value="Granta">Granta</option>
                </select>
                <div class="text--gray text--light">Описание...</div>
            </div>
            <div class="selection-panel">
                <div class="text--gray">Комплектация</div>
                <select id="configurator-car-version" name="Комплектация">
                    <option value="">-</option>
                    <option value="msk">1.5</option>
                    <option value="spb">1.6</option>
                    <option value="ekb">Comfort</option>
                </select>
                <div class="text--gray text--light">Описание...</div>
            </div>
            <div class="selection-panel">
                <div class="text--gray">Итог</div>
                <div class="text--gray">000.000 РУБ.</div>
                <button class="button--red">Оставить заявку</button>

            </div>
        </div>
    </section>
    <?php include 'footer.php'; ?>
    <script src="styles/pages/configurator/load.js"></script>
    <script src="styles/components/navigation/burger.js"></script>
    <script src="styles/components/auth/auth.js"></script>
</body>
</html>