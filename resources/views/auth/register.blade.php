@extends('layouts.app')

@section('no_sidebar', '1')
@section('title', 'Registrati - Excursio')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Registrati</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <h6 class="mb-2 fw-bold">Completa il tuo profilo</h6>
                        <p class="mb-2">
                            Per procedere, alla registrazione compila tutti i campi obbligatori contrassegnati con (*).
                            I dati contrassegnati dal simbolo [] non saranno visibili agli altri utenti e non verranno mostrate pubblicamente.
                        </p>
                        <p class="mb-0"><strong>Importante:</strong> Usa solo foto in primo piano per evitare che la tua richiesta venga respinta.</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data" autocomplete="off">
                        @csrf
                        {{-- Assorbe autofill login (username/password salvati) così non finiscono nei campi reali --}}
                        <div class="position-absolute opacity-0 overflow-hidden" style="height:0;width:0;" aria-hidden="true" tabindex="-1">
                            <input type="text" name="reg_trap_user" autocomplete="username" tabindex="-1">
                            <input type="password" name="reg_trap_pass" autocomplete="current-password" tabindex="-1">
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required autocomplete="given-name">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="cognome" class="form-label">Cognome * [ ]</label>
                                    <input type="text" class="form-control @error('cognome') is-invalid @enderror"
                                           id="cognome" name="cognome" value="{{ old('cognome') }}" required autocomplete="family-name">
                                    @error('cognome')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="datanascita" class="form-label">Data di nascita * [ ]</label>
                                    <input type="date"
                                           class="form-control @error('datanascita') is-invalid @enderror"
                                           id="datanascita" name="datanascita"
                                           value="{{ old('datanascita') }}"
                                           required autocomplete="bday">
                                    @error('datanascita')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="sesso" class="form-label">Sesso *</label>
                                    <select class="form-select @error('sesso') is-invalid @enderror" id="sesso" name="sesso" required autocomplete="off">
                                        <option value="">Seleziona...</option>
                                        <option value="m" {{ old('sesso') === 'm' ? 'selected' : '' }}>Uomo</option>
                                        <option value="f" {{ old('sesso') === 'f' ? 'selected' : '' }}>Donna</option>
                                    </select>
                                    @error('sesso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="residenza" class="form-label">Residenza</label>
                                    <input type="text" class="form-control @error('residenza') is-invalid @enderror"
                                           id="residenza" name="residenza" value="{{ old('residenza') }}" autocomplete="address-level2">
                                    @error('residenza')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Telefono * [ ]</label>
                                    <input type="tel"
                                           class="form-control @error('telefono') is-invalid @enderror"
                                           id="telefono" name="telefono"
                                           value="{{ old('telefono') }}"
                                           placeholder="Es. 3331234567"
                                           required autocomplete="tel">
                                    @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Inserisci una foto * <span class="text-muted">(Campo obbligatorio)</span></label>
                            <input type="file"
                                   class="form-control @error('photo') is-invalid @enderror"
                                   id="photo" name="photo"
                                   accept="image/*"
                                   required>
                            @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Descrizione</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="5"
                                      placeholder="Scrivi qualcosa su di te (testo semplice, senza formattazione)..."
                                      autocomplete="off">{{ old('description') }}</textarea>
                            <small class="text-muted">Campo di testo normale: nessun editor visuale.</small>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="nickname" class="form-label">Nickname *</label>
                                    <input type="text" class="form-control @error('nickname') is-invalid @enderror"
                                           id="nickname" name="nickname" value="{{ old('nickname') }}" required autocomplete="nickname">
                                    @error('nickname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email * [ ]</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password * [ ]</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required autocomplete="new-password">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Conferma Password * [ ]</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <div class="p-2 rounded border border-warning bg-warning bg-opacity-10">
                            <div class="form-check">
                                <input class="form-check-input @error('privacy_consent') is-invalid @enderror"
                                       type="checkbox"
                                       id="privacy_consent"
                                       name="privacy_consent"
                                       value="1"
                                       {{ old('privacy_consent') ? 'checked' : '' }}
                                       required>
                                <label class="form-check-label" for="privacy_consent">
                                    Autorizzo per il trattamento dei dati personali:
                                </label>
                                @error('privacy_consent')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <style>
                                /* Evidenzia il bordo della checkbox "Autorizzo" */
                                #privacy_consent.form-check-input {
                                    border: 2px solid #f5c400;
                                }
                                #privacy_consent.form-check-input:focus {
                                    border-color: #f5c400;
                                    box-shadow: 0 0 0 0.2rem rgba(245, 196, 0, 0.35);
                                }
                                #privacy_consent.form-check-input:checked {
                                    background-color: #f5c400;
                                    border-color: #000;
                                }
                            </style>
                            <div class="mt-2 p-2 border rounded bg-light" style="max-height: 260px; overflow:auto;">
                                <div class="small">
                                    <strong>Informativa Privacy (Sintesi)</strong> Ai sensi dell'art. 13 del Regolamento UE 2016/679 (GDPR), ti informiamo che:
                                    <ol class="mb-2">
                                        <li><strong>Finalità:</strong> I tuoi dati sono raccolti esclusivamente per permetterti di registrarti e usufruire dei servizi del sito.</li>
                                        <li><strong>Obbligatorietà:</strong> Il conferimento dei dati è facoltativo, ma necessario per creare il tuo account. Senza di essi, non potremo fornirti il servizio.</li>
                                        <li><strong>Sicurezza:</strong> I dati saranno trattati con strumenti informatici sicuri e non verranno ceduti a terzi per scopi commerciali senza il tuo consenso.</li>
                                        <li><strong>I tuoi diritti:</strong> Hai il diritto di accedere ai tuoi dati, rettificarli o chiederne la cancellazione in qualsiasi momento (Diritto all'oblio). Ti basta inviare una email a: <a href="mailto:excursio@libero.it">excursio@libero.it</a>.</li>
                                        <li><strong>Età:</strong> La registrazione è consentita solo ai maggiori di 18 anni.</li>
                                    </ol>
                                </div>
                            </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrati</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}">Hai già un account? Accedi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
