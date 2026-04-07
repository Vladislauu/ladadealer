const track = document.getElementById('popular__track')
const prevBtn = document.getElementById('popular__switch--left')
const nextBtn = document.getElementById('popular__switch--right')
const cards = Array.from(document.querySelectorAll('.popular__card'))
let cardWidth = cards[0].offsetWidth
let animationMutex = false;

function moveNext() {
  cardWidth = cards[0].offsetWidth
  if (animationMutex) return;
  animationMutex = true;

  track.style.transition = 'transform 0.4s ease-in-out';
  track.style.transform = `translateX(-${cardWidth}px)`;

  track.addEventListener('transitionend', function onTransitionEnd() {
    track.removeEventListener('transitionend', onTransitionEnd);
    
    const firstCard = track.children[0];
    track.appendChild(firstCard);
    
    track.style.transition = 'none';
    track.style.transform = 'translateX(0)';
    
    track.offsetHeight;
    
    track.style.transition = '';
    animationMutex = false;
  });
}

function movePrev() {
  cardWidth = cards[0].offsetWidth
  if (animationMutex) return;
  animationMutex = true;

  track.style.transition = 'none';
  const lastCard = track.children[track.children.length - 1];
  track.prepend(lastCard);
  track.style.transform = `translateX(-${cardWidth}px)`;
  track.offsetHeight;
  
  track.style.transition = 'transform 0.4s ease-in-out';
  track.style.transform = 'translateX(0)';
  
  track.addEventListener('transitionend', function onEnd() {
    track.removeEventListener('transitionend', onEnd);
    animationMutex = false;
  });
}

nextBtn.addEventListener('click', moveNext, cardWidth);
prevBtn.addEventListener('click', movePrev, cardWidth);