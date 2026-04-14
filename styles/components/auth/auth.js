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