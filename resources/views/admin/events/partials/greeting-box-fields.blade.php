@php
    $eventDbMissing = \App\Support\EventoTableSchema::missingOptionalColumns();
    $greetingEnabled = (bool) old('greeting_box_enabled', $event->greeting_box_enabled ?? false);
    $greetingDuration = (int) old('greeting_box_duration', $event->greeting_box_duration ?? 5);
    $greetingMessage = old('greeting_box_message', $event->greeting_box_message ?? \App\Support\EventGreetingSettings::defaultMessageHtml());
    $greetingMaxWidth = (int) old('greeting_box_max_width', $event->greeting_box_max_width ?? 420);
    $greetingBorder = old('greeting_box_border_color', $event->greeting_box_border_color ?? '#198754');
    $greetingBg = old('greeting_box_bg_color', $event->greeting_box_bg_color ?? '#ffffff');
@endphp

@if($eventDbMissing !== [])
    <div class="alert alert-warning">
        <i class="fas fa-database me-1"></i>
        {{ \App\Support\EventGreetingSettings::migrationRequiredMessage() }}
    </div>
@else
<div class="card border-success mb-3" id="event-greeting-box-settings">
    <div class="card-header bg-success bg-opacity-10 py-2">
        <div class="form-check form-switch m-0">
            <input type="hidden" name="greeting_box_enabled" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="greeting_box_enabled" name="greeting_box_enabled" value="1" {{ $greetingEnabled ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="greeting_box_enabled">
                <i class="fas fa-comment-dots me-1"></i> Box benvenuto su «Visualizza Dettagli Evento»
            </label>
        </div>
    </div>
    <div class="card-body" id="event-greeting-box-fields" style="{{ $greetingEnabled ? '' : 'display:none;' }}">
        <p class="text-muted small mb-3">
            Attivo solo per <strong>questo evento</strong>. Placeholder:
            <code>{nickname}</code>, <code>{nome}</code>, <code>{cognome}</code>, <code>{nome_completo}</code>.
        </p>

        <div class="row g-3">
            <div class="col-md-4">
                <label for="greeting_box_duration" class="form-label fw-semibold">Durata (secondi)</label>
                <input type="number"
                       class="form-control @error('greeting_box_duration') is-invalid @enderror"
                       id="greeting_box_duration"
                       name="greeting_box_duration"
                       min="1"
                       max="120"
                       value="{{ $greetingDuration }}">
                @error('greeting_box_duration')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="greeting_box_max_width" class="form-label fw-semibold">Larghezza max (px)</label>
                <input type="number"
                       class="form-control @error('greeting_box_max_width') is-invalid @enderror"
                       id="greeting_box_max_width"
                       name="greeting_box_max_width"
                       min="280"
                       max="900"
                       value="{{ $greetingMaxWidth }}">
                @error('greeting_box_max_width')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-2">
                <label for="greeting_box_border_color" class="form-label fw-semibold">Bordo</label>
                <input type="color"
                       class="form-control form-control-color @error('greeting_box_border_color') is-invalid @enderror"
                       id="greeting_box_border_color"
                       name="greeting_box_border_color"
                       value="{{ $greetingBorder }}">
            </div>
            <div class="col-md-2">
                <label for="greeting_box_bg_color" class="form-label fw-semibold">Sfondo</label>
                <input type="color"
                       class="form-control form-control-color @error('greeting_box_bg_color') is-invalid @enderror"
                       id="greeting_box_bg_color"
                       name="greeting_box_bg_color"
                       value="{{ $greetingBg }}">
            </div>
        </div>

        <div class="mt-3">
            <label for="greeting_box_message" class="form-label fw-semibold">Messaggio box</label>
            <textarea id="greeting_box_message"
                      name="greeting_box_message"
                      class="form-control @error('greeting_box_message') is-invalid @enderror"
                      rows="6">{{ $greetingMessage }}</textarea>
            @error('greeting_box_message')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
@endif
