@include('partials.ckeditor4-description', ['field' => 'greeting_box_message', 'height' => 180])

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('greeting_box_enabled');
        var fields = document.getElementById('event-greeting-box-fields');

        if (toggle && fields) {
            toggle.addEventListener('change', function () {
                fields.style.display = toggle.checked ? '' : 'none';
            });
        }

        var form = document.getElementById(@json($formId ?? 'admin-event-edit-form'));
        if (form) {
            form.addEventListener('submit', function () {
                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.greeting_box_message) {
                    CKEDITOR.instances.greeting_box_message.updateElement();
                }
            });
        }
    });
</script>
