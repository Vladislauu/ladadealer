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
                <div class="navigation__buttons" id="navMenu">
                    <a href="models.php" class="button--sharp">Модельный ряд</a>
                    <a href="configurator.php" class="button--sharp">Конфигуратор</a>
                    <a href="actual.php" class="button--sharp">Авто в наличии</a>
                    <button id="l-k" class="button--sharp">Личный кабинет</button>
                </div>
            </nav>
            <div id="auth-menu" class="auth">
                <form id="auth-login" class="auth__auth" action="handlers/login.php" method="POST">
                    <input id="login-phone" class="input--text" type="tel" placeholder="+7 (___) ___-__-__">
                    <input id="login-password" class="input--text" type="password" placeholder="Пароль">
                    <button id="login-submit" class="button--red" type="submit">Вход</button>
                    <button id="register" class="button--red" type="button">Регистрация</button>
                    <button id="login-decline" class="button--red" type="button">Отмена</button>
                </form>
                <form id="auth-register" class="auth__register" action="handlers/register.php" method="POST">
                    <input id="register-phone" class="input--text" type="tel" placeholder="+7 (___) ___-__-__">
                    <input id="register-lastname" class="input--text" placeholder="Фамилия">
                    <input id="register-name" class="input--text" placeholder="Имя">
                    <input id="register-father" class="input--text" placeholder="Отчество">
                    <input id="register-password" class="input--text" type="password" placeholder="Пароль">
                    <input id="register-password-repeat" class="input--text" type="password" placeholder="Повторите пароль">
                    <select name="city">
                        <option value="">Город</option>
                        <option value="msk">Москва</option>
                        <option value="spb">Санкт-Петербург</option>
                        <option value="ekb">Екатеринбург</option>
                    </select>
                    <button id="register-submit" class="button--red" type="submit">Зарегистрироваться</button>
                    <button id="register-decline" class="button--red" type="button">Назад</button>
                </form>
            </div>
        </div>
    </header>