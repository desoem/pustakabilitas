document.addEventListener('DOMContentLoaded', function () {
    console.log('Pustakabilitas Admin Panel Loaded');

    // Contoh interaksi form
    const formInputs = document.querySelectorAll('.pustakabilitas-form-group input');
    formInputs.forEach(input => {
        input.addEventListener('focus', function () {
            this.style.borderColor = '#0073aa';
        });
        input.addEventListener('blur', function () {
            this.style.borderColor = '#ddd';
        });
    });
});
