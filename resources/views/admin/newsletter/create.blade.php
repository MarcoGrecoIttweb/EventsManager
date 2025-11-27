@extends('layouts.app')

@section('title', 'Newsletter - Admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-envelope"></i> Invia Newsletter
                        </h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                                @if(session('failed_emails'))
                                    <hr>
                                    <small>
                                        <strong>Invio fallito per:</strong><br>
                                        {{ implode(', ', session('failed_emails')) }}
                                    </small>
                                @endif
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.newsletter.send') }}" method="POST" id="newsletterForm">
                            @csrf

                            <div class="mb-3">
                                <label for="target" class="form-label">Destinatari</label>
                                <select class="form-select" id="target" name="target" required onchange="toggleUserSelection()">
                                    <option value="all">Tutti gli utenti</option>
                                    <option value="approved">Solo utenti approvati</option>
                                    <option value="participants">Solo utenti che partecipano ad eventi</option>
                                    <option value="pending">Solo utenti in attesa di approvazione</option>
                                    <option value="selected">Seleziona utenti specifici</option>
                                </select>
                            </div>

                            <!-- Selezione utenti specifici -->
                            <div class="mb-3" id="userSelection" style="display: none;">
                                <label class="form-label">Seleziona Utenti</label>

                                <div class="mb-2">
                                    <input type="text" id="userSearch" class="form-control"
                                           placeholder="Cerca utenti per nome, email o nickname...">
                                </div>

                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" id="selectAllUsers" class="form-check-input">
                                        <label class="form-check-label fw-bold" for="selectAllUsers">
                                            Seleziona tutti
                                        </label>
                                    </div>
                                    <hr>

                                    <div id="usersList">
                                        @foreach($users as $user)
                                            <div class="form-check mb-2 user-item">
                                                <input type="checkbox" name="selected_users[]"
                                                       value="{{ $user->id }}"
                                                       class="form-check-input user-checkbox"
                                                       id="user_{{ $user->id }}">
                                                <label class="form-check-label" for="user_{{ $user->id }}">
                                                    {{ $user->name }} ({{ $user->nickname }}) - {{ $user->email }}
                                                    <span class="badge bg-{{ $user->status === 'approved' ? 'success' : ($user->status === 'pending' ? 'warning' : 'danger') }} ms-2">
                                                    {{ $user->status }}
                                                </span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Seleziona gli utenti a cui vuoi inviare la newsletter.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label">Oggetto</label>
                                <input type="text" class="form-control" id="subject" name="subject"
                                       placeholder="Oggetto della newsletter" required>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Messaggio</label>
                                <textarea class="form-control" id="message" name="message"
                                          rows="10" placeholder="Scrivi il contenuto della newsletter..."
                                          required></textarea>
                                <small class="form-text text-muted">
                                    Puoi usare HTML base per formattare il testo.
                                </small>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Attenzione:</strong> Questa newsletter verrà inviata a tutti gli utenti selezionati.
                                Assicurati del contenuto prima di inviare.
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg"
                                    onclick="return confirm('Sei sicuro di voler inviare la newsletter?')">
                                <i class="fas fa-paper-plane"></i> Invia Newsletter
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Statistiche -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Statistiche Destinatari</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Tutti gli utenti:</strong>
                            <span class="badge bg-primary float-end">{{ number_format($usersCount) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Utenti approvati:</strong>
                            <span class="badge bg-success float-end">{{ number_format($usersCount) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Partecipanti ad eventi:</strong>
                            <span class="badge bg-warning float-end">{{ number_format($participantsCount) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>In attesa di approvazione:</strong>
                            <span class="badge bg-secondary float-end">{{ number_format($users->where('status', 'pending')->count()) }}</span>
                        </div>
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> I numeri si aggiornano in tempo reale.
                        </small>
                    </div>
                </div>

                <!-- Anteprima selezione -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Anteprima Selezione</h5>
                    </div>
                    <div class="card-body">
                        <div id="selectionPreview">
                            <p class="text-muted">Seleziona un'opzione per vedere l'anteprima</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleUserSelection() {
            const target = document.getElementById('target').value;
            const userSelection = document.getElementById('userSelection');
            userSelection.style.display = target === 'selected' ? 'block' : 'none';

            updateSelectionPreview();
        }

        function updateSelectionPreview() {
            const target = document.getElementById('target').value;
            const preview = document.getElementById('selectionPreview');
            let message = '';

            switch(target) {
                case 'all':
                    message = '<span class="text-success"><i class="fas fa-users"></i> Tutti gli utenti</span>';
                    break;
                case 'approved':
                    message = '<span class="text-success"><i class="fas fa-check-circle"></i> Solo utenti approvati</span>';
                    break;
                case 'participants':
                    message = '<span class="text-warning"><i class="fas fa-calendar-check"></i> Solo partecipanti ad eventi</span>';
                    break;
                case 'pending':
                    message = '<span class="text-warning"><i class="fas fa-clock"></i> Solo utenti in attesa</span>';
                    break;
                case 'selected':
                    const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
                    message = `<span class="text-info"><i class="fas fa-user-check"></i> ${selectedCount} utenti selezionati</span>`;
                    break;
            }

            preview.innerHTML = message;
        }

        // Selezione multipla
        document.getElementById('selectAllUsers').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectionPreview();
        });

        // Ricerca utenti
        document.getElementById('userSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const userItems = document.querySelectorAll('.user-item');

            userItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });

        // Aggiorna preview quando si selezionano utenti
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectionPreview);
        });

        // Inizializza
        document.addEventListener('DOMContentLoaded', function() {
            toggleUserSelection();
            updateSelectionPreview();
        });
    </script>

    <style>
        .user-item {
            transition: all 0.3s ease;
        }
        .user-item:hover {
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        #usersList {
            max-height: 250px;
            overflow-y: auto;
        }
    </style>
@endsection
