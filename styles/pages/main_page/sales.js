const salestrack = document.getElementById('sales__cards')
const salesleft = document.getElementById('sales__switch--left')
const salesright = document.getElementById('sales__switch--right')

let salesmutex = false;

function scroll(next = true)
{
    let salesFirstCard = salestrack.children[0]
    let salesLastCard = salestrack.children[salestrack.children.length-1]
    if (salesmutex) return;
    salesmutex = true;
    salestrack.style.transition = 'transform 1s ease-in-out'
    if (!next) salestrack.style.transform = `translateX(-${100}%)`;
    else salestrack.style.transform = `translateX(${100}%)`;
    salestrack.addEventListener('transitionend', function onTransitionEnd() {
        salestrack.style.transition = 'none'
        salestrack.style.transform = `translateX(0)`;
        if (!next) salestrack.append(salesFirstCard)
        else salestrack.prepend(salesLastCard)
        salestrack.offsetHeight;
        salesmutex = false;
    })
}

salesleft.addEventListener('click', () => scroll(true))
salesright.addEventListener('click', () => scroll(false))