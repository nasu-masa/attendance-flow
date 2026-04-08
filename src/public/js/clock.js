document.addEventListener('DOMContentLoaded', () => {
    dayjs.locale('ja');
    dayjs.extend(dayjs_plugin_advancedFormat);

    function updateClock() {
        const now = dayjs();

        const dateEl = document.getElementById('js-date');
        const timeEl = document.getElementById('js-time');

        const dateFormat = dateEl.dataset.format;
        const timeFormat = timeEl.dataset.format;

        dateEl.textContent = now.format(dateFormat);
        timeEl.textContent = now.format(timeFormat);
    }

    updateClock();
    setInterval(updateClock, 1000);
});
