const lk = document.getElementById("l-k")
const lkf = document.getElementById("lk_footer")
const auth_menu = document.getElementById("auth-menu")

const auth_login = document.getElementById("auth-login")
const auth_register = document.getElementById("auth-register")

const register = document.getElementById("register")
const register_decline = document.getElementById("register-decline")
const login_decline = document.getElementById("login-decline")

lk.addEventListener('click', () => auth_menu.classList.toggle("active"))
lkf.addEventListener('click', () => auth_menu.classList.toggle("active"))

register.addEventListener('click', () => {
    auth_login.classList.toggle("inactive")
    auth_register.classList.toggle("active")
}
)

register_decline.addEventListener('click', () => {
    auth_login.classList.toggle("inactive")
    auth_register.classList.toggle("active")
})

login_decline.addEventListener('click', () => auth_menu.classList.toggle("active"))

/*
                    <input id="login-phone" class="input--text" type="tel" placeholder="+7 (___) ___-__-__">
                    <input id="login-password" class="input--text" type="password" placeholder="Пароль">
                    <button id="login-submit" class="button--red">Вход</button>
                    <button id="register" class="button--red">Регистрация</button>
                    <button id="login-decline" class="button--red">Отмена</button>
                </div>
                <div id="auth-register" class="auth__register">
                    <input id="register-phone" class="input--text" type="tel" placeholder="+7 (___) ___-__-__">
                    <input id="register-lastname" class="input--text" type="password" placeholder="Фамилия">
                    <input id="register-name" class="input--text" type="password" placeholder="Имя">
                    <input id="register-father" class="input--text" type="password" placeholder="Отчество">
                    <input id="register-password" class="input--text" type="password" placeholder="Пароль">
                    <input id="register-password-repeat" class="input--text" type="password" placeholder="Повторите пароль">
*/

const regexerrors = {
    phone : 'Ошибка: номер должен содержать ровно 11 цифр и начинаться с 8 (пример: 89123456789).',
    lname : 'Фамилия должна состоять только из русских букв',
    name : 'Имя должно состоять только из русских букв',
    father : 'Отчество должно состоять только из русских букв',
    pass : 'Пароль должен состоять не менее чем из 8 символов, включать в себя заглавные и строчные буквы, специальные символы',
    pass_r : 'Пароль должен состоять не менее чем из 8 символов, включать в себя заглавные и строчные буквы, специальные символы',
    email : /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
}

const regexes = {
    phone : /^8\d{10}$/,
    lname : /^[А-ЯЁ][а-яё]*(?:-[А-ЯЁ][а-яё]*)?$/,
    name : /^[А-ЯЁ][а-яё]*(?:-[А-ЯЁ][а-яё]*)?$/,
    father : /^[А-ЯЁ][а-яё]*(?:-[А-ЯЁ][а-яё]*)?$/,
    pass : /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/,
    pass_r : /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/,
    email : /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
}

const register_submit = document.getElementById('register-submit')
const reg_phone = document.getElementById('register-phone')
const reg_lastname = document.getElementById('register-lastname')
const reg_name = document.getElementById('register-name')
const reg_father = document.getElementById('register-father')
const reg_pass = document.getElementById('register-password')
const reg_pass_rep = document.getElementById('register-password-repeat')



register_submit.addEventListener('click', function(event) 
{
    let input = {
        phone : reg_phone.value.trim(),
        lname : reg_lastname.value.trim(),
        name : reg_name.value.trim(),
        father : reg_father.value.trim(),
        pass : reg_pass.value.trim(),
        pass_r : reg_pass_rep.value.trim(),
    }

    for (ntest in input) 
    {
        if (input[ntest] === '')
        {
            alert("Все поля должны быть заполнены!")
            event.preventDefault();
            return;
        }
    }
    if (!input.pass === input.pass_r)
    {
        alert("Пароли не совпадают!")
        event.preventDefault();
        return;
    }
    for (ntest in input) {
        if (!regexes[ntest].test(input[ntest]))
        {
            alert(regexerrors[ntest])
            event.preventDefault();
            return;
        }
    }
    alert("Всё заполнено верно!")
})


const login_submit = document.getElementById('login-submit')
const log_phone = document.getElementById('login-phone')
const log_pass = document.getElementById('login-password')

login_submit.addEventListener('click', function(event) 
{
    let lphone = log_phone.value.trim();
    let lpass = log_pass.value.trim();

    if (lphone === '' || lpass === '')
    {
        alert("Все поля должны быть заполнены!")
        event.preventDefault();
        return;
    }

    if (!regexes.phone.test(lphone))
    {
        alert(regexerrors.phone)
        event.preventDefault();
        return;
    }

    if (!regexes.pass.test(lpass))
    {
        alert(regexerrors.pass)
        event.preventDefault();
        return;
    }
    alert("Всё заполнено верно!")
})