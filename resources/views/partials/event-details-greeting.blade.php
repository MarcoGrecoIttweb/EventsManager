@auth
<div id="eventDetailsGreeting" class="event-details-greeting" hidden aria-live="polite" aria-atomic="true">
    <div class="event-details-greeting__backdrop"></div>
    <div class="event-details-greeting__box" role="dialog" aria-modal="true" aria-labelledby="eventDetailsGreetingTitle">
        <div class="event-details-greeting__text" id="eventDetailsGreetingTitle"></div>
        <button type="button" class="event-details-greeting__continue btn btn-success btn-sm mt-3">
            <i class="fas fa-arrow-right me-1"></i> Apri evento
        </button>
    </div>
</div>

<style>
    .event-details-greeting {
        position: fixed;
        inset: 0;
        z-index: 10050;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .event-details-greeting[hidden] {
        display: none !important;
    }

    .event-details-greeting__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
    }

    .event-details-greeting__box {
        position: relative;
        z-index: 1;
        width: 100%;
        padding: 1.25rem 1.5rem;
        border-radius: 0.75rem;
        border-width: 2px;
        border-style: solid;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
        text-align: center;
        font-size: 1.05rem;
        line-height: 1.45;
        animation: eventDetailsGreetingIn 0.25s ease-out;
    }

    .event-details-greeting__text p:last-child {
        margin-bottom: 0;
    }

    .event-details-greeting__continue {
        min-width: 9rem;
        font-weight: 600;
        padding: 0.12rem 0.75rem !important;
        font-size: 0.82rem;
        line-height: 1;
        min-height: 0;
    }

    .event-details-greeting__continue .fa-arrow-right {
        font-size: 0.75rem;
    }

    @keyframes eventDetailsGreetingIn {
        from {
            opacity: 0;
            transform: translateY(8px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>

<script>
    (function () {
        var overlay = document.getElementById('eventDetailsGreeting');
        if (!overlay) {
            return;
        }

        var boxEl = overlay.querySelector('.event-details-greeting__box');
        var textEl = overlay.querySelector('.event-details-greeting__text');
        var continueBtn = overlay.querySelector('.event-details-greeting__continue');
        var navigateTimer = null;
        var pendingTargetUrl = null;

        function clearGreetingTimers() {
            if (navigateTimer) {
                clearTimeout(navigateTimer);
                navigateTimer = null;
            }
        }

        function hideGreeting() {
            clearGreetingTimers();
            overlay.hidden = true;
        }

        function goToEventNow() {
            var targetUrl = pendingTargetUrl;
            hideGreeting();
            pendingTargetUrl = null;
            if (targetUrl) {
                window.location.href = targetUrl;
            }
        }

        function parseConfig(link) {
            var raw = link.getAttribute('data-greeting-config');
            if (!raw) {
                return null;
            }
            try {
                return JSON.parse(raw);
            } catch (e) {
                return null;
            }
        }

        function applyConfig(config) {
            boxEl.style.maxWidth = (config.box && config.box.maxWidth ? config.box.maxWidth : 420) + 'px';
            boxEl.style.borderColor = (config.box && config.box.borderColor) ? config.box.borderColor : '#198754';
            boxEl.style.backgroundColor = (config.box && config.box.backgroundColor) ? config.box.backgroundColor : '#ffffff';
            textEl.innerHTML = config.messageHtml || '';
        }

        function showGreetingThenGo(targetUrl, config) {
            clearGreetingTimers();
            pendingTargetUrl = targetUrl;
            applyConfig(config);

            overlay.hidden = false;

            var duration = parseInt(config.durationMs, 10) || 5000;
            navigateTimer = setTimeout(goToEventNow, duration);
        }

        if (continueBtn) {
            continueBtn.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                goToEventNow();
            });
        }

        document.addEventListener('click', function (event) {
            var link = event.target.closest('.js-event-details-greeting');
            if (!link || !link.getAttribute('href')) {
                return;
            }

            var config = parseConfig(link);
            if (!config) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            showGreetingThenGo(link.getAttribute('href'), config);
        });
    })();
</script>
@endauth
