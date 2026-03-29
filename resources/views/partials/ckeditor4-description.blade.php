{{-- CKEditor 4 da CDN: parametro opzionale $field (id textarea, default: description) --}}
@php
    $ckHeight = (int) ($height ?? 400);
    $fieldId = $field ?? 'description';
@endphp
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var id = @json($fieldId);
        var el = document.getElementById(id);
        if (!el || typeof CKEDITOR === 'undefined') {
            return;
        }
        CKEDITOR.replace(id, {
            language: 'it',
            height: {{ $ckHeight }},
            removePlugins: 'elementspath',
            resize_dir: 'vertical',
            versionCheck: false
        });
    });
</script>
