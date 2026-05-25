@php
    $charLimit = (int) ($charLimit ?? 100);
    $preview = $charLimit === 150
        ? $event->short_preview
        : $event->getHomepagePreview($charLimit);
    $full = $event->full_public_preview;
    $expandable = $event->isPublicPreviewTruncated($charLimit);
@endphp

@once
    <style>
        .event-preview--expandable {
            cursor: pointer;
            touch-action: manipulation;
            -webkit-tap-highlight-color: rgba(13, 110, 253, 0.15);
            border-radius: 0.35rem;
            padding: 0.15rem 0.2rem;
        }
        .event-preview--expandable:active {
            background: rgba(13, 110, 253, 0.08);
        }
        @media (hover: hover) {
            .event-preview--expandable:hover {
                background: rgba(13, 110, 253, 0.06);
            }
        }
        .event-preview--expanded,
        .event-preview--expanded.event-preview {
            display: block !important;
            -webkit-line-clamp: unset !important;
            max-height: none !important;
            overflow: visible !important;
        }
        .event-preview__toggle-hint {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.35rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: #0d6efd;
            line-height: 1.2;
        }
        .event-preview__toggle-hint i {
            font-size: 0.7rem;
        }
    </style>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function setPreviewState(el, expanded) {
                    var preview = el.querySelector('.event-preview__text');
                    var full = el.querySelector('.event-preview__full');
                    var hint = el.querySelector('.event-preview__toggle-hint');
                    if (!preview || !full) {
                        return;
                    }

                    if (expanded) {
                        preview.classList.add('d-none');
                        full.classList.remove('d-none');
                        el.classList.add('event-preview--expanded');
                        el.setAttribute('data-preview-expanded', '1');
                        el.setAttribute('aria-expanded', 'true');
                        el.setAttribute('aria-label', 'Testo completo. Tocca per ridurre.');
                        if (hint) {
                            hint.innerHTML = '<i class="fas fa-chevron-up" aria-hidden="true"></i> Tocca per ridurre';
                        }
                    } else {
                        preview.classList.remove('d-none');
                        full.classList.add('d-none');
                        el.classList.remove('event-preview--expanded');
                        el.setAttribute('data-preview-expanded', '0');
                        el.setAttribute('aria-expanded', 'false');
                        el.setAttribute('aria-label', 'Anteprima testo. Tocca per leggere tutto.');
                        if (hint) {
                            hint.innerHTML = '<i class="fas fa-chevron-down" aria-hidden="true"></i> Tocca per leggere tutto';
                        }
                    }
                }

                function togglePreview(el) {
                    var expanded = el.getAttribute('data-preview-expanded') === '1';
                    setPreviewState(el, !expanded);
                }

                document.querySelectorAll('.event-preview--expandable').forEach(function (el) {
                    el.addEventListener('click', function (e) {
                        e.preventDefault();
                        togglePreview(el);
                    });
                    el.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            togglePreview(el);
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce

<div @class([
    'card-text small event-preview event-public-desc-preview',
    'event-preview--expandable' => $expandable,
])
     @if($expandable)
         role="button"
         tabindex="0"
         aria-expanded="false"
         aria-label="Anteprima testo. Tocca per leggere tutto."
         data-preview-expanded="0"
     @endif>
    <span class="event-preview__text">{{ $preview }}</span>
    @if($expandable)
        <span class="event-preview__full d-none">{{ $full }}</span>
        <span class="event-preview__toggle-hint" aria-hidden="true">
            <i class="fas fa-chevron-down"></i> Tocca per leggere tutto
        </span>
    @endif
</div>
