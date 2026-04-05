const burger = document.getElementById("burgerBtn")
const menu = document.getElementById("navMenu")

function toggleMenu(arg) 
{
    burger.classList.toggle('active')
    menu.classList.toggle('active')
}

burger.addEventListener('click', toggleMenu)