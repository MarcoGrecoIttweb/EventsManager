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
        // Contrasto e selezione visibile nell’iframe (incolla da Word/siti con testo bianco o “windowtext”).
        var ckCustomCss = 'data:text/css;charset=utf-8,' + encodeURIComponent(
            '.cke_editable{font-size:14px;line-height:1.55;color:#212529!important;background:#fff!important;}' +
            '.cke_editable p,.cke_editable li,.cke_editable td,.cke_editable th{color:#212529;}' +
            '.cke_editable ::selection{background:rgba(13,110,253,.35);}'
        );
        CKEDITOR.replace(id, {
            language: 'it',
            height: {{ $ckHeight }},
            removePlugins: 'elementspath',
            resize_dir: 'vertical',
            versionCheck: false,
            // Incolla da Word/browser: senza questo CKEditor può scartare o svuotare il contenuto (ACF).
            // L’HTML viene comunque ripulito al salvataggio da SafeRichText::sanitize.
            allowedContent: true,
            pasteFilter: null,
            pasteFromWordRemoveFontStyles: false,
            pasteFromWordRemoveStyles: false,
            contentsCss: [CKEDITOR.getUrl('contents.css'), ckCustomCss]
        });
    });
</script>
