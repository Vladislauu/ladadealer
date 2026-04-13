const track = document.getElementById('popular__track')
const prevBtn = document.getElementById('popular__switch--left')
const nextBtn = document.getElementById('popular__switch--right')
const cards = Array.from(document.querySelectorAll('.popular__card'))
let animationMutex = false; //cards[Math.floor(cards.length/2)].classList.toggle('active')
let basetransform = 0
let scrollmult = 1;
function resizeactive()
{
  track.children[Math.floor(track.children.length/2)].classList.toggle('active')
}

function checkWidth()
{
  if (window.innerWidth < 768) {
    if (track.children.length%2 == 0) basetransform = 50
    track.style.transform = `translateX(-${basetransform}%)`;
    scrollmult = 1.5;
  } else {
    if (track.children.length%2 == 0) basetransform = 10
    track.style.transform = `translateX(-${basetransform}%)`;
    scrollmult = 1.5;
  }
}

checkWidth();
resizeactive();

function moveNext() {
  if (animationMutex) return;
  animationMutex = true;
  resizeactive()
  track.addEventListener('transitionend', function onResizeEnd() {
      track.removeEventListener('transitionend', onResizeEnd);

      track.style.transition = 'transform 0.4s ease-in-out'
      track.style.transform = `translateX(-${basetransform+20}%)`;

      track.addEventListener('transitionend', function onTransitionEnd() {
        track.removeEventListener('transitionend', onTransitionEnd);
        track.style.transition = 'none';
        track.style.transform = `translateX(-${basetransform}%)`
        const firstCard = track.children[0];
        track.appendChild(firstCard);

        track.offsetHeight;
        resizeactive()
        track.addEventListener('transitionend', function onTransitionEnd() {
          track.removeEventListener('transitionend', onTransitionEnd);
          animationMutex = false;
        })
    })
  })
}

function movePrev() {
  if (animationMutex) return;
  animationMutex = true;
  resizeactive()
  track.addEventListener('transitionend', function onResizeEnd() {
      track.removeEventListener('transitionend', onResizeEnd);

      track.style.transition = 'transform 0.4s ease-in-out'
      track.style.transform = `translateX(${basetransform+20}%)`;

      track.addEventListener('transitionend', function onTransitionEnd() {
        track.removeEventListener('transitionend', onTransitionEnd);
        const lastCard = track.children[track.children.length - 1];
        track.prepend(lastCard);
        track.offsetHeight;
        track.style.transition = 'none';
        track.style.transform = `translateX(-${basetransform}%)`
        track.offsetHeight;
        resizeactive()
        track.addEventListener('transitionend', function onTransitionEnd() {
          track.removeEventListener('transitionend', onTransitionEnd);
          animationMutex = false;
        })
    })
  })
}

nextBtn.addEventListener('click', moveNext);
prevBtn.addEventListener('click', movePrev);
window.addEventListener('resize', checkWidth);