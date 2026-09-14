<style>
    .weekday-date-label {
        display: block;
        margin-top: 0.25rem;
        color: #555;
        font-size: 0.8rem;
        text-transform: capitalize;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.weekday-date-input').forEach(function (input) {
            var label = input.parentElement.querySelector('.weekday-date-label');
            if (!label) return;

            function updateWeekday() {
                if (!input.value) {
                    label.textContent = '';
                    return;
                }

                var value = input.value.replace('T', ' ');
                var date = new Date(value.replace(' ', 'T'));
                if (Number.isNaN(date.getTime())) {
                    label.textContent = '';
                    return;
                }

                label.textContent = new Intl.DateTimeFormat('it-IT', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }).format(date);
            }

            input.addEventListener('change', updateWeekday);
            input.addEventListener('input', updateWeekday);
            updateWeekday();
        });
    });
</script>
