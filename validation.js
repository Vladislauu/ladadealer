const consultation_Phone_Form = document.getElementById('consultation-phone-form')
const consultation_Send_Forma = document.getElementById('consultation-send-form')

const phoneValidation = /^8\d{10}$/;

consultation_Send_Forma.addEventListener('click', function(event) {
    let phoneValue = consultation_Phone_Form.value.trim()
    if (phoneValue === '') {
        alert('Ошибка: поле номера телефона не должно быть пустым.');
        event.preventDefault();
        return false;
    }
    if (!phoneValidation.test(phoneValue)) {
        alert('Ошибка: номер должен содержать ровно 11 цифр и начинаться с 8 (пример: 89123456789).');
        event.preventDefault();
        return false;
    }
    alert('Номер телефона введён верно!');
})