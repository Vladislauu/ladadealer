document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.sales__cards');
    const leftBtn = document.querySelector('.button--left');
    const rightBtn = document.querySelector('.button--next');
    const cards = document.querySelectorAll('.sales__card');
    
    function getCardWidth() {
        if (cards.length === 0) return 0;
        const card = cards[0];
        const styles = window.getComputedStyle(card);
        return card.offsetWidth + (parseFloat(styles.marginLeft) || 0) + (parseFloat(styles.marginRight) || 0);
    }
    
    leftBtn.addEventListener('click', function() {
        const cardWidth = getCardWidth();
        container.scrollBy({
            left: -cardWidth,
            behavior: 'smooth'
        });
    });
    
    rightBtn.addEventListener('click', function() {
        const cardWidth = getCardWidth();
        container.scrollBy({
            left: cardWidth,
            behavior: 'smooth'
        });
    });
});