@php
    $links = $links ?? collect();
@endphp

<div class="list-group">
    @foreach($links as $i => $u)
        @php
            $inputId = 'photo_link_' . md5($u) . '_' . $i;
        @endphp
        <div class="list-group-item">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <a href="{{ $u }}" target="_blank" rel="noopener" class="text-decoration-none fw-semibold">
                        {{ basename(parse_url($u, PHP_URL_PATH) ?: $u) }}
                    </a>
                    <div class="text-muted small text-truncate">{{ $u }}</div>
                </div>
                <div class="col-12 col-md-4">
                    <input id="{{ $inputId }}" type="text" class="form-control form-control-sm" value="{{ $u }}" readonly>
                </div>
                <div class="col-12 col-md-2 d-grid">
                    <button type="button" class="btn btn-sm btn-dark" data-copy-url="{{ $inputId }}">Copia</button>
                </div>
                <div class="col-12">
                    <img src="{{ $u }}" alt="immagine" style="max-height: 170px; max-width: 100%; object-fit: contain;" class="border rounded p-1 bg-white">
                </div>
            </div>
        </div>
    @endforeach
</div>

