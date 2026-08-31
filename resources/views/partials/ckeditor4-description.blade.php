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
        // Default nero senza !important: altrimenti i colori scelti con l'editor (span style="color:…") non si vedono in modifica.
        '.cke_editable{font-size:14px;line-height:' . $ckLh . ';color:#212529;background:#fff!important;}',
        // Testo incollato bianco/invisibile (Word): forzalo leggibile senza bloccare i colori scelti a mano.
        '.cke_editable [style*="color: white"],.cke_editable [style*="color:#fff"],.cke_editable [style*="color: #fff"],.cke_editable [style*="color:#ffffff"],.cke_editable [style*="color: #ffffff"]{color:#212529!important;}',
        // Nota admin "Gli eventi proposti..." inserita come blockquote: mostrala marrone anche in editor, senza linea a sinistra.
        '.cke_editable blockquote:not([style*="color"]){color:#8B4513;border-left:0!important;padding-left:0!important;margin-left:0!important;}',
        '.cke_editable blockquote:not([style*="color"]) p,.cke_editable blockquote:not([style*="color"]) li{color:#8B4513;}',
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
        function initCkEditor() {
            if (CKEDITOR.instances[id]) {
                return;
            }
            CKEDITOR.replace(id, {
                language: 'it',
                height: {{ $ckHeight }},
                removePlugins: 'elementspath',
                extraPlugins: 'justify',
                resize_dir: 'vertical',
                versionCheck: false,
                toolbar: [
                    { name: 'document', items: ['Source'] },
                    { name: 'clipboard', items: ['Undo', 'Redo'] },
                    { name: 'editing', items: ['Find', 'Replace', 'SelectAll'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'] },
                    '/',
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'Iframe'] },
                    { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                    { name: 'colors', items: ['TextColor', 'BGColor'] },
                    { name: 'tools', items: ['Maximize', 'ShowBlocks'] }
                ],
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
        }
        // Se la textarea è dentro un pannello a scomparsa (Bootstrap collapse) ancora chiuso,
        // CKEditor calcolerebbe le dimensioni su un contenitore nascosto (width/height 0) e la
        // maniglia di ridimensionamento resterebbe rotta anche dopo l'apertura: si rimanda
        // l'inizializzazione a quando il pannello viene effettivamente mostrato.
        var hiddenCollapseAncestor = el.closest('.collapse:not(.show)');
        if (hiddenCollapseAncestor) {
            hiddenCollapseAncestor.addEventListener('shown.bs.collapse', function onShown() {
                hiddenCollapseAncestor.removeEventListener('shown.bs.collapse', onShown);
                initCkEditor();
            });
        } else {
            initCkEditor();
        }
    });
</script>
