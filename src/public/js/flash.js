document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.c-flash');
    if (flash) {
        setTimeout(() => {
            flash.classList.add('is-hidden');

            setTimeout(() => {
                flash.remove();
            }, 600);
        }, 3000);
    }
});
