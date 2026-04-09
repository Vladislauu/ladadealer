const track = document.getElementById('popular__track')
const prevBtn = document.getElementById('popular__switch--left')
const nextBtn = document.getElementById('popular__switch--right')
const cards = Array.from(document.querySelectorAll('.popular__card'))
let cardWidth = cards[0].offsetWidth
let animationMutex = false; //cards[Math.floor(cards.length/2)].classList.toggle('active')

function resizeactive()
{
  track.children[Math.floor(track.children.length/2)].classList.toggle('active')
}

function moveNext() {
  if (animationMutex) return;
  animationMutex = true;
  cardWidth = track.children[0].offsetWidth
  resizeactive()
  track.addEventListener('transitionend', function onResizeEnd() {
      track.removeEventListener('transitionend', onResizeEnd);

      track.style.transition = 'transform 0.4s ease-in-out'
      track.style.transform = `translateX(-${cardWidth}px)`;

      track.addEventListener('transitionend', function onTransitionEnd() {
        track.removeEventListener('transitionend', onTransitionEnd);
        track.style.transition = 'none';
        track.style.transform = 'translateX(0)';
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
  cardWidth = track.children[0].offsetWidth
  resizeactive()
  track.addEventListener('transitionend', function onResizeEnd() {
      track.removeEventListener('transitionend', onResizeEnd);

      track.style.transition = 'transform 0.4s ease-in-out'
      track.style.transform = `translateX(${cardWidth}px)`;

      track.addEventListener('transitionend', function onTransitionEnd() {
        track.removeEventListener('transitionend', onTransitionEnd);
        const lastCard = track.children[track.children.length - 1];
        track.prepend(lastCard);
        track.offsetHeight;
        track.style.transition = 'none';
        track.style.transform = 'translateX(0)';
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