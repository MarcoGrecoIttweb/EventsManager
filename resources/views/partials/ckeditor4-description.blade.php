{{-- CKEditor 4 da CDN: parametro opzionale $field (id textarea, default: description) --}}
{{-- Opzionali: $editable_line_height (numero, default 1.55), $editable_p_margin (es. "0.2em") per righe più compatte --}}
@php
    $ckHeight = (int) ($height ?? 400);
    $fieldId = $field ?? 'description';
    $ckLh = isset($editable_line_height) && is_numeric($editable_line_height)
        ? min(2.0, max(1.0, (float) $editable_line_height))
        : 1.55;
    $ckPMargin = null;
    if (isset($editable_p_margin) && is_string($editable_p_margin) && preg_match('/^[\d.]+(em|rem|px)$/', $editable_p_margin)) {
        $ckPMargin = $editable_p_margin;
    }
    $ckCssParts = [
        '.cke_editable{font-size:14px;line-height:' . $ckLh . ';color:#212529!important;background:#fff!important;}',
        '.cke_editable p,.cke_editable li,.cke_editable td,.cke_editable th{color:#212529;}',
        '.cke_editable ::selection{background:rgba(13,110,253,.35);}',
    ];
    if ($ckPMargin !== null) {
        $ckCssParts[] = '.cke_editable p{margin-top:0;margin-bottom:' . $ckPMargin . ';}';
    }
    $ckCustomCssUrl = 'data:text/css;charset=utf-8,' . rawurlencode(implode('', $ckCssParts));
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
        var ckCustomCss = @json($ckCustomCssUrl);
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
            contentsCss: [CKEDITOR.getUrl('contents.css'), ckCustomCss],
            // Upload immagini (tab "Carica" nel dialog "Immagine")
            filebrowserImageUploadUrl: @json(route('ckeditor.upload', ['_token' => csrf_token()])),
            // Upload generico (altri dialog)
            filebrowserUploadUrl: @json(route('ckeditor.upload', ['_token' => csrf_token()])),
            filebrowserUploadMethod: 'form'
        });
    });
</script>
