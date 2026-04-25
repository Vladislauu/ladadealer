<header>
    <div class="header__content">
        <nav>
            <a href="index.php">
                <img src="media/logo.png" alt="Логотип" class="image--scaling">
            </a>
            <button class="burger" id="burgerBtn" aria-label="Открыть меню">
                <span class="burger__line"></span>
                <span class="burger__line"></span>
                <span class="burger__line"></span>
            </button>
            <form class="navigation__buttons" id="navMenu" action="handlers/checkLogin.php" method="POST">
                <a href="models.php" class="button--sharp" type="button">Модельный ряд</a>
                <a href="configurator.php" class="button--sharp" type="button">Конфигуратор</a>
                <a href="actual.php" class="button--sharp" type="button">Авто в наличии</a>
                <button id="l-k" class="button--sharp" type="submit">Личный кабинет</button>
            </form>
        </nav>
    </div>
    <script src="styles/components/navigation/burger.js"></script>
</header>