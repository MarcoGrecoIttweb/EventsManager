@php
    $btnClass = $btnClass ?? 'btn btn-primary w-100';
    $greetingConfig = auth()->check()
        ? \App\Support\EventGreetingSettings::frontendConfigForEvent($event, auth()->user())
        : null;
@endphp
<a href="{{ route('events.show', $event) }}"
   class="{{ $btnClass }}{{ $greetingConfig ? ' js-event-details-greeting' : '' }}"
   @if($greetingConfig) data-greeting-config='@json($greetingConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)' @endif>
    <i class="fas fa-eye"></i>
    Visualizza Dettagli Evento
</a>
