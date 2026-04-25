// order.js – логика модального окна заказа автомобиля

const orderModal = document.getElementById('order-modal');
const orderDescription = document.getElementById('order-description');
const orderPhone = document.getElementById('order-phone');
const orderSubmit = document.getElementById('order-submit');
const orderCancel = document.getElementById('order-cancel');

// Регулярное выражение для телефона (ровно 11 цифр, начинается с 8)
const phoneRegex = /\d{11}$/;
const phoneError = 'Ошибка: номер должен содержать ровно 11 цифр и начинаться с +7 (пример: +79123456789).';

// Функция извлечения информации из карточки (БЭМ-подход)
function getCarInfoFromCard(card) {
    // Берём первые два текстовых элемента внутри карточки
    const textElements = card.querySelectorAll('.text--gray');
    
    // Первый элемент: марка и модель (например, "LADA Signet")
    const brandModel = textElements[0]?.innerText.trim() || 'Неизвестная модель';
    
    // Второй элемент: комплектация (например, "1700SL")
    const complectation = textElements[1]?.innerText.trim() || 'стандартная';
    
    // Цвет в текущей вёрстке не предусмотрен, ставим заглушку
    const color = 'не указан';
    
    return { brandModel, complectation, color };
}

// Открыть модальное окно и заполнить описание
function openOrderModal(carInfo) {
    orderDescription.innerText = `Вы собираетесь оставить заявку на автомобиль ${carInfo.brandModel} в комплектации ${carInfo.complectation} цвета ${carInfo.color}.
Вам перезвонит консультант для назначения встречи в ближайшем дилерском центре и завершения оформления заказа.`;
    orderPhone.value = '';
    orderModal.classList.add('active');
}

// Закрыть модальное окно
function closeOrderModal() {
    orderModal.classList.remove('active');
}

// Навешиваем обработчики на все кнопки "Заказать" в карточках
document.querySelectorAll('.offer-card .button--red').forEach(button => {
    button.addEventListener('click', (event) => {
        const card = event.target.closest('.offer-card');
        if (!card) return;
        
        const carInfo = getCarInfoFromCard(card);
        openOrderModal(carInfo);
    });
});

// Отправка заявки
orderSubmit.addEventListener('click', (event) => {
    const phone = orderPhone.value.trim();
    
    if (!phone) {
        alert('Введите номер телефона');
        event.preventDefault();
        return;
    }
    
    if (!phoneRegex.test(phone)) {
        alert(phoneError);
        event.preventDefault();
        return;
    }
    
    alert('Заявка отправлена! Ожидайте звонка консультанта.');
    closeOrderModal();
});

// Отмена
orderCancel.addEventListener('click', closeOrderModal);

// Закрытие по клику на затемнённый фон (полупрозрачную область)
orderModal.addEventListener('click', (event) => {
    if (event.target === orderModal) {
        closeOrderModal();
    }
});